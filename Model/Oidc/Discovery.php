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
        if (empty($issuer)) {
            throw new LocalizedException(__('Issuer URL ist leer'));
        }
        
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
            $resp = $this->http->get($url, ['timeout' => 10]);
            if ($resp->getStatusCode() !== 200) {
                throw new LocalizedException(__(
                    'OIDC Discovery fehlgeschlagen (%1): HTTP Status %2', 
                    $url, 
                    $resp->getStatusCode()
                ));
            }
            
            $body = (string)$resp->getBody();
            $data = json_decode($body, true);
            
            if (!is_array($data)) {
                throw new LocalizedException(__(
                    'Ungültige JSON-Antwort von %1: %2', 
                    $url, 
                    substr($body, 0, 100) . (strlen($body) > 100 ? '...' : '')
                ));
            }
            
            if (!isset($data['authorization_endpoint'], $data['token_endpoint'], $data['jwks_uri'])) {
                $missing = [];
                if (!isset($data['authorization_endpoint'])) $missing[] = 'authorization_endpoint';
                if (!isset($data['token_endpoint'])) $missing[] = 'token_endpoint';
                if (!isset($data['jwks_uri'])) $missing[] = 'jwks_uri';
                
                throw new LocalizedException(__(
                    'Fehlende erforderliche Felder in OpenID-Configuration: %1', 
                    implode(', ', $missing)
                ));
            }
            
            $this->cache->save(json_encode($data), $cacheKey, [], $this->cacheTtl);
            return $data;
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            throw new LocalizedException(__('Verbindungsfehler zum OIDC Server: %1', $e->getMessage()));
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            throw new LocalizedException(__('HTTP Anfragefehler: %1', $e->getMessage()));
        } catch (LocalizedException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new LocalizedException(__('Fehler bei Discovery: %1', $e->getMessage()));
        }
    }
}
