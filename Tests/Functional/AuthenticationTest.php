<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\Integration;

use Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\TestCase;

class AuthenticationTest extends TestCase
{
    private static $authorizationHeader;
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();

        static::$kernel->getContainer()->get('session')->invalidate();
    }

    public function testLogin()
    {
        $this->client->request('POST', '/login_check', ['_username' => 'lexik', '_password' => 'dummy']);

        $response = $this->client->getResponse();
        $body     = json_decode($response->getContent(), true);

        $this->assertTrue($response->isSuccessful());
        $this->assertArrayHasKey('token', $body);

        return $body['token'];
    }

    /**
     * @depends testLogin
     */
    public function testSecured($token)
    {
        self::$authorizationHeader = sprintf('Bearer %s', $token);

        $this->client->request('GET', '/api/secured', [], [], ['HTTP_AUTHORIZATION' => self::$authorizationHeader]);

        $response = $this->client->getResponse();
        var_dump($response->getContent());
        $response = $this->client->getResponse();
        $body     = json_decode($response->getContent(), true);
        $this->assertTrue($body['success']);
    }
}
