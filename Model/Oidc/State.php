
<?php
namespace Martinkuhl\AutheliaOidc\Model\Oidc;

use Magento\Backend\Model\Auth\Session as AdminSession;

class State
{
    private AdminSession $session;

    public function __construct(AdminSession $session)
    {
        $this->session = $session;
    }

    public function generate(): array
    {
        $state = bin2hex(random_bytes(16));
        $nonce = bin2hex(random_bytes(16));
        $codeVerifier = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');

        $this->session->setData('martinkuhl_oidc_state', $state);
        $this->session->setData('martinkuhl_oidc_nonce', $nonce);
        $this->session->setData('martinkuhl_oidc_code_verifier', $codeVerifier);

        return ['state' => $state, 'nonce' => $nonce, 'code_verifier' => $codeVerifier];
    }

    public function validate(string $state): bool
    {
        return hash_equals((string)$this->session->getData('martinkuhl_oidc_state'), $state);
    }

    public function getNonce(): ?string
    {
        return (string)$this->session->getData('martinkuhl_oidc_nonce');
    }

    public function getCodeVerifier(): ?string
    {
        return (string)$this->session->getData('martinkuhl_oidc_code_verifier');
    }

    public function clear(): void
    {
        $this->session->unsetData('martinkuhl_oidc_state');
        $this->session->unsetData('martinkuhl_oidc_nonce');
        $this->session->unsetData('martinkuhl_oidc_code_verifier');
    }
}
