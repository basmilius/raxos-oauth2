<?php
declare(strict_types=1);

use Raxos\Http\HttpRequest;
use Raxos\Http\Structure\HttpPostMap;
use Raxos\OAuth2\Server\Client\ClientInterface;
use Raxos\OAuth2\Server\Error\{InvalidGrantException, InvalidRequestException};
use Raxos\OAuth2\Server\GrantType\RefreshTokenGrantType;
use Raxos\OAuth2\Server\Token\{AccessTokenInterface, RefreshTokenInterface, TokenFactoryInterface};

covers(RefreshTokenGrantType::class);

it('issues an access token for a valid refresh token and revokes its previous access token', function (bool $hasPrevious): void {
    $client = $this->createMock(ClientInterface::class);
    $client->method('getClientId')->willReturn('client');
    $refresh = $this->createMock(RefreshTokenInterface::class);
    $refresh->method('getClientId')->willReturn('client');
    $refresh->method('getToken')->willReturn('refresh');
    $refresh->method('getOwner')->willReturn('owner');
    $refresh->method('getScope')->willReturn('read');
    $old = $hasPrevious ? $this->createMock(AccessTokenInterface::class) : null;
    $tokens = $this->createMock(TokenFactoryInterface::class);
    $tokens->expects($this->once())->method('getRefreshToken')->with($client, 'refresh')->willReturn($refresh);
    $tokens->expects($this->once())->method('generateAccessToken')->willReturn('new-access');
    $tokens->expects($this->once())->method('getAccessTokenByAssociatedToken')->with($client, 'refresh')->willReturn($old);
    $tokens->expects($this->once())->method('saveAccessToken')->with($client, 'owner', 'read', 'new-access', 3600, 'refresh');
    $tokens->expects($hasPrevious ? $this->once() : $this->never())->method('revokeAccessToken')->with($client, $old);
    $response = new RefreshTokenGrantType($tokens)->handle(HttpRequest::create(post: new HttpPostMap(['refresh_token' => 'refresh'])), $client);
    expect($response->body)->toBe(['access_token' => 'new-access', 'token_type' => 'Bearer', 'scope' => 'read', 'expires_in' => 3600]);
})->with([false, true]);

it('rejects missing, unknown, expired and mismatched tokens before issuing access tokens', function (string $case, string $error): void {
    $client = $this->createMock(ClientInterface::class);
    $client->method('getClientId')->willReturn('client');
    $refresh = $this->createMock(RefreshTokenInterface::class);
    $refresh->method('getClientId')->willReturn($case === 'other-client' ? 'other' : 'client');
    $refresh->method('isExpired')->willReturn($case === 'expired');
    $tokens = $this->createMock(TokenFactoryInterface::class);
    $tokens->method('getRefreshToken')->willReturn($case === 'unknown' ? null : $refresh);
    $tokens->expects($this->never())->method('generateAccessToken');
    $post = $case === 'missing' ? [] : ['refresh_token' => 'refresh'];
    expect(fn () => new RefreshTokenGrantType($tokens)->handle(HttpRequest::create(post: new HttpPostMap($post)), $client))->toThrow($error);
})->with([['missing', InvalidRequestException::class], ['unknown', InvalidGrantException::class], ['expired', InvalidGrantException::class], ['other-client', InvalidGrantException::class]]);
