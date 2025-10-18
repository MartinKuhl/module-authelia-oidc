<?php

namespace Martinkuhl\AutheliaOidc\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH = 'martinkuhl_authelia_oidc/general/';

    public function isEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH . 'enabled', ScopeInterface::SCOPE_STORE);
    }

    public function getIssuer(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH . 'issuer', ScopeInterface::SCOPE_STORE);
    }

    public function getClientId(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH . 'client_id', ScopeInterface::SCOPE_STORE);
    }

    public function getClientSecret(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH . 'client_secret', ScopeInterface::SCOPE_STORE);
    }

    public function getScope(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH . 'scope', ScopeInterface::SCOPE_STORE) ?: 'openid email profile';
    }

    public function getUsernameClaim(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH . 'username_claim', ScopeInterface::SCOPE_STORE) ?: 'email';
    }

    public function getRedirectUri(string $baseUrl): string
    {
        $redirectPath = $this->scopeConfig->getValue(self::XML_PATH . 'redirect_uri_path', ScopeInterface::SCOPE_STORE);
        if (empty($redirectPath)) {
            $redirectPath = '/admin/authelia/oidc/callback';
        }
        return rtrim($baseUrl, '/') . $redirectPath;
    }
}
