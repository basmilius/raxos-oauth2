<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use Raxos\Http\HttpRequest;
use Raxos\Http\Structure\HttpPostMap;
use Raxos\OAuth2\Server\Error\InvalidGrantException;
use Raxos\OAuth2\Server\GrantType\RefreshTokenGrantType;
use Raxos\OAuth2\Server\SecurityProfile;
use RaxosTests\OAuth2\AtomicRefreshStore;
use RaxosTests\OAuth2\RotationClient;

$factory = new AtomicRefreshStore($argv[1], $argv[2], $argv[3]);
$grant = new RefreshTokenGrantType($factory, SecurityProfile::modern());

try {
    $response = $grant->handle(HttpRequest::create(post: new HttpPostMap(['refresh_token' => 'original', 'scope' => 'read'])), new RotationClient());
    echo json_encode(['accepted' => true, 'tokens' => $response->body], JSON_THROW_ON_ERROR);
} catch (InvalidGrantException) {
    echo json_encode(['accepted' => false], JSON_THROW_ON_ERROR);
}
