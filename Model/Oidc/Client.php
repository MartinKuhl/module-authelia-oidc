<?php

namespace Martinkuhl\AutheliaOidc\Model\Oidc;

use GuzzleHttp\Client as HttpClient;
use Magento\Framework\Exception\LocalizedException;
use Martinkuhl\AutheliaOidc\Helper\Data;
use Martinkuhl\AutheliaOidc\Helper\LoggerHelper;

class Client
{
    private Data $helper;
    private Discovery $discovery;
    private HttpClient $http;
    private LoggerHelper $logger;

    public function __construct(
        Data $helper, 
        Discovery $discovery, 
        HttpClient $http,
        LoggerHelper $logger
    ) {
        $this->helper = $helper;
        $this->discovery = $discovery;
        $this->http = $http;
        $this->logger = $logger;
    }

    public function getAuthorizeUrl(string $baseUrl, string $state, string $nonce, string $codeVerifier): string
    {
        $this->logger->info('getAuthorizeUrl aufgerufen', [
            'baseUrl' => $baseUrl,
            'state' => substr($state, 0, 5) . '...',  // Nur einen Teil für Sicherheit loggen
            'nonce' => substr($nonce, 0, 5) . '...'   // Nur einen Teil für Sicherheit loggen
        ]);
        
        try {
            $issuer = $this->helper->getIssuer();
            $this->logger->info('OIDC Discovery wird aufgerufen', ['issuer' => $issuer]);
            
            $config = $this->discovery->getConfiguration($issuer);
            if (!isset($config['authorization_endpoint'])) {
                $error = 'Keine authorization_endpoint in OIDC Discovery gefunden';
                $this->logger->error($error, ['config' => $config]);
                throw new \Magento\Framework\Exception\LocalizedException(
                    __($error)
                );
            }
            
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
            
            $url = $authorizeEndpoint . '?' . http_build_query($params);
            $this->logger->info('Authorize URL generiert', [
                'authorizeEndpoint' => $authorizeEndpoint,
                'redirectUri' => $this->helper->getRedirectUri($baseUrl),
                'clientId' => $this->helper->getClientId()
            ]);
            
            return $url;
        } catch (\Throwable $e) {
            $error = 'Fehler beim Erstellen der Authorize URL: ' . $e->getMessage();
            $this->logger->error($error, ['exception' => $e->getTraceAsString()]);
            throw new \Magento\Framework\Exception\LocalizedException(
                __($error)
            );
        }
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
