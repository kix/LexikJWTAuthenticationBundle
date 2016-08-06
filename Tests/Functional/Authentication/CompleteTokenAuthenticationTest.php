<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\Authentication;

use Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests the different behaviours of an authentication via JSON Web Token.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class CompleteTokenAuthenticationTest extends TestCase
{
    public static function setupBeforeClass()
    {
        static::bootKernel();
    }

    public function testAccessSecuredRoute()
    {
        static::$client = static::createAuthenticatedClient();
        static::$client->request('GET', '/api/secured');

        $this->assertSuccessful(static::$client->getResponse());
    }

    public function testAccessSecuredRouteWithoutToken()
    {
        static::$client = static::createClient();
        static::$client->request('GET', '/api/secured');

        $response = static::$client->getResponse();
        $this->assertFailure($response);

        $body = json_decode($response->getContent(), true);

        return $body;
    }

    public function testAccessSecuredRouteWithInvalidToken()
    {
        static::$client = static::createClient();
        static::$client->request('GET', '/api/secured', [], [], ['HTTP_AUTHORIZATION' => 'Bearer dummy']);

        $response = static::$client->getResponse();
        $body     = json_decode($response->getContent(), true);

        $this->assertFailure($response);

        return $body;
    }

    /**
     * @group time-sensitive
     */
    public function testAccessSecuredRouteWithExpiredToken()
    {
        static::bootKernel();

        $encoderWrapper = static::$kernel->getContainer()->get('lexik_jwt_authentication.test.exp_aware_jwt_encoder.wrapper');
        $expiredToken   = $encoderWrapper->decreaseTokenExpirationTime(static::getAuthenticatedToken());

        static::$client = static::createAuthenticatedClient($expiredToken);
        static::$client->request('GET', '/api/secured');

        $response = static::$client->getResponse();
        $body     = json_decode($response->getContent(), true);

        $this->assertFailure($response);

        return $body;
    }

    protected function assertFailure(Response $response)
    {
        $this->assertFalse($response->isSuccessful());
        $this->assertSame(401, $response->getStatusCode());
    }

    protected function assertSuccessful(Response $response)
    {
        $this->assertTrue($response->isSuccessful());
        $this->assertSame(200, $response->getStatusCode());
    }
}
