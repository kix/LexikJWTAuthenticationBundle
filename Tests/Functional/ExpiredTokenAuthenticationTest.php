<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional;

/**
 * Tests the different behaviours of an authentication via JSON Web Token.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class ExpiredTokenAuthenticationTest extends TestCase
{
    public function testAccessSecuredRouteWithExpiredToken()
    {
        static::$kernel = static::createKernel([
            'extra_parameters' => ['lexik_jwt_authentication.token_ttl' => 1],
        ]);
        static::$kernel->boot();
        static::$client = static::createAuthenticatedClient();

        sleep(5); // let the token expire

        static::$client->request('GET', '/api/secured');

        $response = static::$client->getResponse();
        $this->assertFalse($response->isSuccessful());
        $this->assertSame(401, $response->getStatusCode());

        $body = json_decode($response->getContent(), true);
        $this->assertEquals('Expired JWT Token', $body['message']);
    }
}
