<?php

namespace Martinkuhl\AutheliaOidc\Model\Oidc;

use GuzzleHttp\Client as HttpClient;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\LocalizedException;
use Martinkuhl\AutheliaOidc\Helper\LoggerHelper;

class Discovery
{
    private HttpClient $http;
    private CacheInterface $cache;
    private int $cacheTtl;
    private LoggerHelper $logger;

    public function __construct(
        HttpClient $http, 
        CacheInterface $cache, 
        LoggerHelper $logger, 
        int $cacheTtl = 3600
    ) {
        $this->http = $http;
        $this->cache = $cache;
        $this->cacheTtl = $cacheTtl;
        $this->logger = $logger;
    }

    public function getConfiguration(string $issuer): array
    {
        $this->logger->info('OIDC Discovery gestartet', ['issuer' => $issuer]);
        
        if (empty($issuer)) {
            $error = 'Issuer URL ist leer';
            $this->logger->error($error);
            throw new LocalizedException(__($error));
        }
        
        $issuer = rtrim($issuer, '/');
        $cacheKey = 'martinkuhl_oidc_openid_conf_' . md5($issuer);
        $cached = $this->cache->load($cacheKey);
        if ($cached) {
            $conf = json_decode($cached, true);
            if (is_array($conf)) {
                $this->logger->debug('OIDC Discovery Konfiguration aus Cache geladen', ['issuer' => $issuer]);
                return $conf;
            }
        }

        $url = $issuer . '/.well-known/openid-configuration';
        $this->logger->info('OIDC Discovery-URL', ['url' => $url]);
        
        try {
            $this->logger->debug('Sende HTTP-Anfrage an Discovery-Endpoint', ['url' => $url]);
            $resp = $this->http->get($url, ['timeout' => 10]);
            
            if ($resp->getStatusCode() !== 200) {
                $error = sprintf(
                    'OIDC Discovery fehlgeschlagen (%s): HTTP Status %s', 
                    $url, 
                    $resp->getStatusCode()
                );
                $this->logger->error($error, ['status' => $resp->getStatusCode()]);
                throw new LocalizedException(__($error));
            }
            
            $body = (string)$resp->getBody();
            $data = json_decode($body, true);
            $this->logger->debug('OIDC Discovery Antwort erhalten');
            
            if (!is_array($data)) {
                $error = sprintf(
                    'Ungültige JSON-Antwort von %s: %s', 
                    $url, 
                    substr($body, 0, 100) . (strlen($body) > 100 ? '...' : '')
                );
                $this->logger->error($error, ['body' => substr($body, 0, 500)]);
                throw new LocalizedException(__($error));
            }
            
            if (!isset($data['authorization_endpoint'], $data['token_endpoint'], $data['jwks_uri'])) {
                $missing = [];
                if (!isset($data['authorization_endpoint'])) $missing[] = 'authorization_endpoint';
                if (!isset($data['token_endpoint'])) $missing[] = 'token_endpoint';
                if (!isset($data['jwks_uri'])) $missing[] = 'jwks_uri';
                
                $error = sprintf(
                    'Fehlende erforderliche Felder in OpenID-Configuration: %s', 
                    implode(', ', $missing)
                );
                $this->logger->error($error, ['data' => $data]);
                throw new LocalizedException(__($error));
            }
            
            $this->cache->save(json_encode($data), $cacheKey, [], $this->cacheTtl);
            $this->logger->info('OIDC Discovery erfolgreich abgeschlossen', [
                'authorization_endpoint' => $data['authorization_endpoint'],
                'token_endpoint' => $data['token_endpoint'],
                'jwks_uri' => $data['jwks_uri']
            ]);
            return $data;
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            $error = 'Verbindungsfehler zum OIDC Server: ' . $e->getMessage();
            $this->logger->error($error, ['exception' => $e->getTraceAsString()]);
            throw new LocalizedException(__($error));
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $error = 'HTTP Anfragefehler: ' . $e->getMessage();
            $this->logger->error($error, ['exception' => $e->getTraceAsString()]);
            throw new LocalizedException(__($error));
        } catch (LocalizedException $e) {
            $this->logger->error('LocalizedException: ' . $e->getMessage(), ['exception' => $e->getTraceAsString()]);
            throw $e;
        } catch (\Throwable $e) {
            $error = 'Fehler bei Discovery: ' . $e->getMessage();
            $this->logger->error($error, ['exception' => $e->getTraceAsString()]);
            throw new LocalizedException(__($error));
        }
    }
}
