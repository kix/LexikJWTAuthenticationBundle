<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional;

use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationSuccessResponse;

class GetTokenTest extends TestCase
{
    public function __construct()
    {
        static::$client = static::createClient();
    }

    public function testGetToken()
    {
        static::$client->request('POST', '/login_check', ['_username' => 'lexik', '_password' => 'dummy']);

        $response = static::$client->getResponse();

        $this->assertInstanceOf(JWTAuthenticationSuccessResponse::class, $response);
        $this->assertTrue($response->isSuccessful());

        $body = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('token', $body, 'The response should have a "token" key containing a JWT Token.');
    }

    public function testGetTokenFromInvalidCredentials()
    {
        static::$client->request('POST', '/login_check', ['_username' => 'lexik', '_password' => 'wrong']);

        $response = static::$client->getResponse();

        $body = json_decode($response->getContent(), true);

        $this->assertFalse($response->isSuccessful());
        $this->assertSame(401, $response->getStatusCode());

        $this->assertArrayHasKey('message', $body, 'The response should have a "message" key containing the failure reason.');
        $this->assertArrayHasKey('code', $body, 'The response should have a "code" key containing the response status code.');

        $this->assertSame('Bad credentials', $body['message']);
        $this->assertSame(401, $body['code']);
    }
}
