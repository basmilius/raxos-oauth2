<a href="https://bas.dev">
    <img src="https://bmcdn.nl/assets/branding/logo.svg" alt="Bas Milius" height="48" />
</a>

---

# Raxos OAuth2

OAuth2 authorization-server integration for Raxos Router, with persistence provided by the application.

[Documentation](https://raxos.dev/oauth2/) | [Packagist](https://packagist.org/packages/raxos/oauth2) | [Raxos](https://github.com/basmilius/raxos)

- Authorization-code and refresh-token grants.
- Authorize, token and revoke controller actions, plus bearer-token middleware.
- Client, scope and token factory contracts implemented by the application.

## Installation

Requires PHP 8.5 or later. Composer checks the remaining package and extension dependencies declared in [composer.json](composer.json).

```sh
composer require "raxos/oauth2:^3.2"
```

## Usage

```php
<?php
declare(strict_types=1);

use Raxos\OAuth2\Server\Client\ClientFactoryInterface;
use Raxos\OAuth2\Server\OAuth2Server;
use Raxos\OAuth2\Server\Scope\ScopeFactoryInterface;
use Raxos\OAuth2\Server\Token\TokenFactoryInterface;

require __DIR__ . '/vendor/autoload.php';

final class ApplicationOAuth2Server extends OAuth2Server
{
    public function __construct(
        ClientFactoryInterface $clients,
        ScopeFactoryInterface $scopes,
        TokenFactoryInterface $tokens,
        private readonly ?string $ownerId
    )
    {
        parent::__construct($clients, $scopes, $tokens);
    }

    public function getOwner(): ?string
    {
        return $this->ownerId;
    }

    public function hasOwner(): bool
    {
        return $this->ownerId !== null;
    }
}
```

Construct this server per request with your factory implementations and the authenticated owner ID, or `null` for an unauthenticated request. Extend `OAuth2Controller` to provide the consent and authentication responses, then register that controller with your router. Authorization-code flows require S256 PKCE, exact redirect matching and atomic consumption of unexpired codes by the token factory. See the [3.2 migration guide](https://github.com/basmilius/raxos/blob/main/MIGRATION.md#oauth-authorization-codes-require-s256-and-atomic-consumption) before implementing storage.

## Documentation

- [Server setup](https://raxos.dev/oauth2/server)
- [Authorization flow](https://raxos.dev/oauth2/authorization-flow)
- [Protecting routes](https://raxos.dev/oauth2/middleware)
- [Error handling](https://raxos.dev/oauth2/errors)

## Testing

Run this library's Pest suite from the Raxos workspace:

```sh
git clone --recurse-submodules https://github.com/basmilius/raxos.git
cd raxos
composer install
vendor/bin/pest --testsuite=oauth2
```

See [Testing Raxos](https://github.com/basmilius/raxos/blob/main/TESTING.md) for PHP extensions, integration services and coverage commands. The library's [Tests workflow](.github/workflows/tests.yml) also runs in GitHub Actions.

## License

[MIT](LICENSE). Copyright (c) 2017 - present Bas Milius.
