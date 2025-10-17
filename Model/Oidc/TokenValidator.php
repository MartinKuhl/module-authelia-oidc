<?php

namespace Martinkuhl\AutheliaOidc\Model\Oidc;

use Magento\Framework\Exception\LocalizedException;
use Martinkuhl\AutheliaOidc\Helper\Data;
use Jose\Component\Core\JWKSet;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer;

class TokenValidator
{
    private Data $helper;
    private Discovery $discovery;
    private Jwks $jwks;
    private int $leeway = 60; // Sekunden Toleranz

    public function __construct(Data $helper, Discovery $discovery, Jwks $jwks)
    {
        $this->helper = $helper;
        $this->discovery = $discovery;
        $this->jwks = $jwks;
    }

    public function validateIdToken(string $idToken, string $expectedNonce): array
    {
        $config = $this->discovery->getConfiguration($this->helper->getIssuer());
        $jwksUri = $config['jwks_uri'];
        $keys = $this->jwks->getKeys($jwksUri);

        $serializer = new CompactSerializer();
        $jws = $serializer->unserialize($idToken);

        $header = $jws->getSignature(0)->getProtectedHeader();
        $alg = $header['alg'] ?? null;
        if ($alg !== 'RS256') {
            throw new LocalizedException(__('Unerwarteter JWT-Algorithmus: %1', $alg));
        }
        $kid = $header['kid'] ?? null;

        $jwkSet = JWKSet::createFromKeyData($keys['keys']);
        $verifier = new JWSVerifier([new RS256()]);
        $verified = false;

        foreach ($jwkSet->all() as $jwk) {
            if ($kid && ($jwk->has('kid') && $jwk->get('kid') !== $kid)) {
                continue;
            }
            try {
                if ($verifier->verifyWithKey($jws, 0, $jwk)) {
                    $verified = true;
                    break;
                }
            } catch (\Throwable $e) {
                // continue
            }
        }
        if (!$verified) {
            throw new LocalizedException(__('ID Token Signatur ungültig'));
        }

        $payload = json_decode($jws->getPayload(), true);
        if (!is_array($payload)) {
            throw new LocalizedException(__('ID Token Payload ungültig'));
        }

        $iss = rtrim($this->helper->getIssuer(), '/');
        if (($payload['iss'] ?? null) !== $iss) {
            throw new LocalizedException(__('Issuer-Claim ungültig'));
        }

        $clientId = $this->helper->getClientId();
        $aud = $payload['aud'] ?? null;
        if (is_array($aud)) {
            if (!in_array($clientId, $aud, true)) {
                throw new LocalizedException(__('Audience-Claim enthält nicht die Client ID'));
            }
        } else {
            if ($aud !== $clientId) {
                throw new LocalizedException(__('Audience-Claim entspricht nicht der Client ID'));
            }
        }

        $now = time();
        $exp = (int)($payload['exp'] ?? 0);
        $iat = (int)($payload['iat'] ?? 0);
        if ($exp <= $now - $this->leeway) {
            throw new LocalizedException(__('ID Token ist abgelaufen'));
        }
        if ($iat > $now + $this->leeway) {
            throw new LocalizedException(__('ID Token iat liegt in der Zukunft'));
        }

        if (($payload['nonce'] ?? null) !== $expectedNonce) {
            throw new LocalizedException(__('Nonce-Validierung fehlgeschlagen'));
        }

        return $payload;
    }
}
