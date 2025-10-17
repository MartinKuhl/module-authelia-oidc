
<?php
namespace Martinkuhl\AutheliaOidc\Model\Oidc;

use GuzzleHttp\Client as HttpClient;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\LocalizedException;

class Jwks
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

    public function getKeys(string $jwksUri): array
    {
        $cacheKey = 'martinkuhl_oidc_jwks_' . md5($jwksUri);
        $cached = $this->cache->load($cacheKey);
        if ($cached) {
            $keys = json_decode($cached, true);
            if (is_array($keys) && isset($keys['keys'])) {
                return $keys;
            }
        }

        try {
            $resp = $this->http->get($jwksUri);
            if ($resp->getStatusCode() !== 200) {
                throw new LocalizedException(__('JWKS Abruf fehlgeschlagen (%1): %2', $jwksUri, $resp->getStatusCode()));
            }
            $data = json_decode((string)$resp->getBody(), true);
            if (!is_array($data) || !isset($data['keys'])) {
                throw new LocalizedException(__('Ungültige JWKS Daten von %1', $jwksUri));
            }
            $this->cache->save(json_encode($data), $cacheKey, [], $this->cacheTtl);
            return $data;
        } catch (\Throwable $e) {
            throw new LocalizedException(__('HTTP Fehler bei JWKS: %1', $e->getMessage()));
        }
    }
}
