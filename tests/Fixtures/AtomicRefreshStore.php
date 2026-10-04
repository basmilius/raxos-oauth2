<?php
declare(strict_types=1);

namespace RaxosTests\OAuth2;

use LogicException;
use PDO;
use Raxos\OAuth2\Server\Client\ClientInterface;
use Raxos\OAuth2\Server\Token\AccessTokenInterface;
use Raxos\OAuth2\Server\Token\AuthorizationCodeInterface;
use Raxos\OAuth2\Server\Token\RefreshTokenInterface;
use Raxos\OAuth2\Server\Token\RotatingTokenFactoryInterface;
use Throwable;

final readonly class RotationClient implements ClientInterface
{
    public function __construct(private string $id = 'client')
    {
    }

    public function getClientId(): string
    {
        return $this->id;
    }

    public function isRedirectUriAllowed(string $redirectUri): bool
    {
        return false;
    }

    public function isSecretValid(string $clientSecret): bool
    {
        return false;
    }
}

final readonly class StoredRefreshToken implements RefreshTokenInterface
{
    public function __construct(private array $row)
    {
    }

    public function getClientId(): string
    {
        return $this->row['client'];
    }

    public function getOwner(): mixed
    {
        return 'owner';
    }

    public function getScope(): string
    {
        return $this->row['scope'];
    }

    public function getToken(): string
    {
        return $this->row['token'];
    }

    public function isExpired(): bool
    {
        return $this->row['expires'] <= time();
    }

    public function isScopeAllowed(string $scope): bool
    {
        return in_array($scope, explode(' ', $this->row['scope']), true);
    }
}

final class AtomicRefreshStore implements RotatingTokenFactoryInterface
{
    public readonly PDO $pdo;

    public function __construct(string $path, private ?string $barrier = null, private ?string $worker = null)
    {
        $this->pdo = new PDO('sqlite:' . $path);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('PRAGMA busy_timeout = 5000');
    }

    public function generateAccessToken(): string
    {
        return 'access-' . bin2hex(random_bytes(8));
    }

    public function generateRefreshToken(): string
    {
        return 'refresh-' . bin2hex(random_bytes(8));
    }

    public function generateAuthorizationCode(): string
    {
        throw new LogicException('Not used by rotation.');
    }

    public function getAccessToken(string $token): ?AccessTokenInterface
    {
        return null;
    }

    public function getAccessTokenByAssociatedToken(ClientInterface $client, string $token): ?AccessTokenInterface
    {
        return null;
    }

    public function getAuthorizationCode(ClientInterface $client, string $code): ?AuthorizationCodeInterface
    {
        return null;
    }

    public function getRefreshToken(ClientInterface $client, string $token): ?RefreshTokenInterface
    {
        $query = $this->pdo->prepare('SELECT * FROM refresh WHERE token = ? AND client = ?');
        $query->execute([$token, $client->getClientId()]);
        $row = $query->fetch(PDO::FETCH_ASSOC);

        if ($this->barrier !== null) {
            file_put_contents($this->barrier . '-' . $this->worker, 'ready');
            $deadline = microtime(true) + 5;

            while (!is_file($this->barrier) && microtime(true) < $deadline) {
                usleep(10000);
            }

            if (!is_file($this->barrier)) {
                throw new LogicException('Rotation barrier timed out.');
            }
        }

        return $row === false ? null : new StoredRefreshToken($row);
    }

    public function rotateRefreshToken(ClientInterface $client, RefreshTokenInterface $previous, string $accessToken, string $refreshToken, string $scope, int $expiresIn): bool
    {
        $this->pdo->exec('BEGIN IMMEDIATE');

        try {
            $query = $this->pdo->prepare('SELECT r.*, f.revoked AS family_revoked FROM refresh r JOIN families f ON f.id = r.family WHERE r.token = ?');
            $query->execute([$previous->getToken()]);
            $row = $query->fetch(PDO::FETCH_ASSOC);

            if (!$row || $row['client'] !== $client->getClientId() || $row['expires'] <= time() || $row['family_revoked']) {
                $this->pdo->exec('COMMIT');

                return false;
            }

            if ($row['consumed']) {
                $this->pdo->prepare('UPDATE families SET revoked = 1 WHERE id = ?')->execute([$row['family']]);
                $this->pdo->prepare('UPDATE refresh SET revoked = 1 WHERE family = ?')->execute([$row['family']]);
                $this->pdo->prepare('UPDATE access SET revoked = 1 WHERE family = ?')->execute([$row['family']]);
                $this->pdo->exec('COMMIT');

                return false;
            }

            if (array_diff(explode(' ', $scope), explode(' ', $row['scope'])) !== []) {
                $this->pdo->exec('COMMIT');

                return false;
            }

            $this->pdo->prepare('UPDATE refresh SET consumed = 1 WHERE token = ?')->execute([$row['token']]);
            $this->pdo->prepare('INSERT INTO refresh VALUES (?, ?, ?, ?, ?, 0, 0)')->execute([$refreshToken, $row['family'], $row['client'], $scope, time() + 600]);
            $this->pdo->prepare('INSERT INTO access VALUES (?, ?, ?, 0)')->execute([$accessToken, $row['family'], time() + $expiresIn]);
            $this->pdo->exec('COMMIT');

            return true;
        } catch (Throwable $error) {
            $this->pdo->exec('ROLLBACK');

            throw $error;
        }
    }

    public function revokeAccessToken(ClientInterface $client, AccessTokenInterface $accessToken): void
    {
        throw new LogicException('Rotation must be atomic.');
    }

    public function revokeAuthorizationCode(ClientInterface $client, AuthorizationCodeInterface $authorizationCode): void
    {
        throw new LogicException('Not used by rotation.');
    }

    public function revokeRefreshToken(ClientInterface $client, RefreshTokenInterface $refreshToken): void
    {
        throw new LogicException('Rotation must be atomic.');
    }

    public function saveAccessToken(ClientInterface $client, mixed $owner, string $scope, string $accessToken, int $expiresIn, ?string $refreshToken): void
    {
        throw new LogicException('Rotation must be atomic.');
    }

    public function saveAuthorizationCode(ClientInterface $client, mixed $owner, string $redirectUri, string $scope, string $authorizationCode, ?string $state = null, ?string $codeChallenge = null): void
    {
        throw new LogicException('Not used by rotation.');
    }

    public function saveRefreshToken(ClientInterface $client, mixed $owner, string $scope, string $refreshToken): void
    {
        throw new LogicException('Rotation must be atomic.');
    }

    public function consumeAuthorizationCode(ClientInterface $client, AuthorizationCodeInterface $authorizationCode): bool
    {
        throw new LogicException('Not used by rotation.');
    }
}
