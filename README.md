
# Authelia OIDC for Magento 2

Magento 2.4.8 Admin-Login via Authelia (OpenID Connect) mit PKCE, JWKS/RS256 und Caching.

## Installation
```
composer require martinkuhl/module-authelia-oidc:^1.0
bin/magento module:enable martinkuhl_AutheliaOidc
bin/magento setup:upgrade
bin/magento cache:flush
```

## Konfiguration
- Stores > Configuration > Advanced > martinkuhl Authelia OIDC
  - Issuer, Client ID/Secret, Scopes, Username Claim
- Redirect URI in Authelia:
  - https://YOUR-HOST/admin/authelia/oidc/callback

## Sicherheit
- HTTPS erzwingen
- RS256-Signaturprüfung (web-token/jwt-framework)
- PKCE S256
- Discovery/JWKS Caching

## Lizenz
MIT
