<?php

namespace Martinkuhl\AutheliaOidc\Model\Oidc;

use GuzzleHttp\Client as HttpClient;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\LocalizedException;

class Discovery
{
    private HttpClient $http;
    private CacheInterface $cache;
    private int $cacheTtl;

    public function __construct(HttpClient $http, CacheInterface $cache, int $cacheTtl = 3600)
    {
        $this->http = $http;
        $this->cache = $cache;
        $this->cacheTtl = $cacheTtl;
    }

    public function getConfiguration(string $issuer): array
    {
        $issuer = rtrim($issuer, '/');
        $cacheKey = 'martinkuhl_oidc_openid_conf_' . md5($issuer);
        $cached = $this->cache->load($cacheKey);
        if ($cached) {
            $conf = json_decode($cached, true);
            if (is_array($conf)) {
                return $conf;
            }
        }

        $url = $issuer . '/.well-known/openid-configuration';
        try {
            $resp = $this->http->get($url);
            if ($resp->getStatusCode() !== 200) {
                throw new LocalizedException(__('OIDC Discovery fehlgeschlagen (%1): %2', $url, $resp->getStatusCode()));
            }
            $data = json_decode((string)$resp->getBody(), true);
            if (!is_array($data) || !isset($data['authorization_endpoint'], $data['token_endpoint'], $data['jwks_uri'])) {
                throw new LocalizedException(__('Ungültige OpenID-Configuration von %1', $url));
            }
            $this->cache->save(json_encode($data), $cacheKey, [], $this->cacheTtl);
            return $data;
        } catch (\Throwable $e) {
            throw new LocalizedException(__('HTTP Fehler bei Discovery: %1', $e->getMessage()));
        }
    }
}
