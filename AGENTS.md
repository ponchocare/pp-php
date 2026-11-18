# pp-php

PHP 8.2 library (`ponchopay/pp-php`) for the PonchoPay HTTP API. Not an application. Public surface is `PonchoPay\Client` / `ClientInterface`, file-autoloaded `createToken` / `createJWT`, and the empty `Types` class (phpstan aliases only).

## Commands

Documented checks only. There is no CI, no `phpunit.xml`, and no `phpstan.neon`. phpstan is installed for the editor (`.phpactor.json`); do not add a config or treat `phpstan analyse` as required.

```bash
composer exec php-cs-fixer fix .          # .php-cs-fixer.dist.php; risky rules are off
composer exec phpunit tests/              # whole suite
composer exec phpunit tests/ClientTest.php
composer exec phpunit -- --filter testCreateToken
```

`composer.lock` is gitignored on purpose. Do not commit it. `tests/` is `export-ignore` in `.gitattributes` (omitted from the Packagist dist).

## Layout

- PSR-4 `PonchoPay\` → `src/`. Function files are **not** PSR-4: `src/token.php` and `src/Utils/utils.php` are `autoload.files` in `composer.json`. A new function file must be listed there.
- Tests are loaded by path. There is no `autoload-dev`. They must not call the network.
- `example/` is a separate path-repo consumer (`symlink: true` to `../`) with its own `vendor/`. `example/main.php` talks to the live API. Do not treat it as a test.
- `docs/` is hand-written and linked from `README.md`. Update it when the public API changes.
- Keep `Client` and `ClientInterface` in sync, including the `@phpstan-import-type` block. Shapes live as `@phpstan-type` on `Types`; they are not runtime types.

## API behaviour that is easy to get wrong

- Default base URL is production `https://pay.ponchopay.com/`. Demo is `https://demo.ponchopay.com/`, passed in by the caller. Do not change the default.
- Amounts are integer minor units (pence).
- Two auth schemes:
  - Initiate payment/subscription: POST body field `token` from `createToken($key, $metadata)` = standard base64 of raw SHA-256 of `"{$metadata}.{$key}"`. Success is **302**; read `location` with `getHeaders(false)`.
  - Validate / update / refund / cancel: `Authorization: Bearer` from `createJWT`. Signed with `hash('sha256', $key, true)` (raw digest, not the key string), `HS256`, `exp` is `iat + 5` seconds. `sig` is standard base64 SHA-256 of the body. GET expects **200**. PUT expects **204** and signs the body **after** `urn` and `email` are stripped.
- Webhook header is `signature` (not `Authorization`). `sha256Hmac` is URL-safe base64 **without** padding. That is not the same encoding as `sha256`.
- `Api` forces `max_redirects` => 0 and always sends `content-type: application/json` plus `x-telemetry`.
- Helpers are spelled `serialise` / `deserialise`. `serialise([])` returns `''`, not `[]`. `DateTimeInterface` becomes `DateTimeInterface::ATOM`.
- Path templates use `[param]` (`replaceParams`), not `{param}`.
- Callback bodies are capped at 2 MiB. `StreamInterface::read()` may return short; keep reading until EOF or the cap (`Client::getRawData`).

## Tests

- Do **not** mark `Api` `final`. The `/* final */` comment is intentional so `Client` tests can mock it.
- `Api` builds its HTTP client internally. `ApiTest` swaps the private `$client` with `(fn () => $this->client = $client)->call($api)`. There is no constructor injection; keep the property name if you touch that test.
- `TokenTest` / `ClientTest` assert exact token and HMAC fixtures. Changing hash inputs or encodings will fail those strings.
