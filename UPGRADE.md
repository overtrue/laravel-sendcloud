# Upgrading to 2.0

This is a major release: PHP versions below 8.3 and Laravel versions below 13.30 are no longer supported. Upgrade your application first, then require `overtrue/laravel-sendcloud:^2.0` and update dependencies with Composer.

The Laravel 13.30 floor matches the lowest framework version admitted by Composer security advisories when this release was prepared. Keep Composer advisory blocking enabled and update to newer security releases as they become available.

## SendCloud SDK 2.x

The underlying SDK changes from `overtrue/sendcloud:^1.0` to `^2.0`, using Guzzle 7.15.2 or newer. Review custom middleware and HTTP-client integrations for Guzzle 7 compatibility.

- The default API endpoint is now `https://api.sendcloud.net/apiv2/`.
- Both `/mail/send` and `mail/send` preserve the `/apiv2/` prefix. Remove workarounds that depended on a leading slash discarding that prefix.
- Redirects are disabled by default. Explicitly enabled redirects must remain on the original origin; cross-origin and HTTPS-to-HTTP redirects throw an exception.
- Configured credentials override duplicate `apiUser` and `apiKey` fields. GET/query requests preserve unrelated parameters. Form POSTs include credentials in the form body.
- Use the SDK `upload()` helper or the `multipart` request option for uploads. Prebuilt multipart request bodies are rejected; multipart credentials are supplied by the SDK.

See the [SDK 2.0.0 release](https://github.com/overtrue/sendcloud/releases/tag/2.0.0).

## Laravel integration

Package discovery, the `SendCloud` facade, the SDK class binding, the `sendcloud` container alias, and `services.sendcloud` configuration remain available. This is an API binding, not a Laravel Mail transport.

Client construction remains transient. Configuration is now read from the container resolving the client, avoiding reliance on a different globally active application. The facade still caches its client until Laravel resets facade state; do not assume runtime configuration updates affect an existing client or facade. Octane compatibility is not claimed.

Response conversion and HTTP exceptions remain the SDK's responsibility. An HTTP 200 API failure payload is returned as data, while HTTP error responses continue to throw according to the SDK's HTTP options.
