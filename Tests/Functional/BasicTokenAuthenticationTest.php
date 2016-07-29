<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional;

/**
 * Tests the different behaviours of an authentication via JSON Web Token.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class BasicTokenAuthenticationTest extends TestCase
{
    public function testAccessSecuredRoute()
    {
        static::bootKernel();
        static::$client = static::createAuthenticatedClient();
        static::$client->request('GET', '/api/secured');

        $response = static::$client->getResponse();

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('', $response->getContent());
    }

    public function testAccessSecuredRouteWithoutToken()
    {
        static::bootKernel();
        static::$client = static::createClient();
        static::$client->request('GET', '/api/secured');

        $response = static::$client->getResponse();
        $this->assertFalse($response->isSuccessful());
        $this->assertSame(401, $response->getStatusCode());

        $body = json_decode($response->getContent(), true);
        $this->assertEquals('JWT Token not found', $body['message']);
    }

    public function testAccessSecuredRouteWithInvalidToken()
    {
        static::bootKernel();
        static::$client = static::createClient();
        static::$client->request('GET', '/api/secured', [], [], ['HTTP_AUTHORIZATION' => 'Bearer dummy']);

        $response = static::$client->getResponse();
        $this->assertFalse($response->isSuccessful());
        $this->assertSame(401, $response->getStatusCode());

        $body = json_decode($response->getContent(), true);
        $this->assertEquals('Invalid JWT Token', $body['message']);
    }
}
