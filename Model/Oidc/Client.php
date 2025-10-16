
<?php
namespace martinkuhl\AutheliaOidc\Model\Oidc;

use GuzzleHttp\Client as HttpClient;
use Magento\Framework\Exception\LocalizedException;
use martinkuhl\AutheliaOidc\Helper\Data;

class Client
{
    private Data $helper;
    private Discovery $discovery;
    private HttpClient $http;

    public function __construct(Data $helper, Discovery $discovery, HttpClient $http)
    {
        $this->helper = $helper;
        $this->discovery = $discovery;
        $this->http = $http;
    }

    public function getAuthorizeUrl(string $baseUrl, string $state, string $nonce, string $codeVerifier): string
    {
        $config = $this->discovery->getConfiguration($this->helper->getIssuer());
        $authorizeEndpoint = $config['authorization_endpoint'];
        $codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');

        $params = [
            'response_type' => 'code',
            'client_id' => $this->helper->getClientId(),
            'redirect_uri' => $this->helper->getRedirectUri($baseUrl),
            'scope' => $this->helper->getScope(),
            'state' => $state,
            'nonce' => $nonce,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256'
        ];
        return $authorizeEndpoint . '?' . http_build_query($params);
    }

    public function exchangeCodeForTokens(string $code, string $baseUrl, ?string $codeVerifier): array
    {
        $config = $this->discovery->getConfiguration($this->helper->getIssuer());
        $tokenEndpoint = $config['token_endpoint'];

        $form = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->helper->getRedirectUri($baseUrl),
            'client_id' => $this->helper->getClientId(),
            'client_secret' => $this->helper->getClientSecret(),
        ];
        if ($codeVerifier) {
            $form['code_verifier'] = $codeVerifier;
        }

        try {
            $resp = $this->http->post($tokenEndpoint, ['form_params' => $form]);
            if ($resp->getStatusCode() !== 200) {
                throw new LocalizedException(__('Token Endpoint Fehler: %1', $resp->getStatusCode()));
            }
            $data = json_decode((string)$resp->getBody(), true);
            if (!is_array($data) || !isset($data['id_token'])) {
                throw new LocalizedException(__('Token-Antwort ohne id_token'));
            }
            return $data;
        } catch (\Throwable $e) {
            throw new LocalizedException(__('Token-Austausch fehlgeschlagen: %1', $e->getMessage()));
        }
    }
}
