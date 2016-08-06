<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\Authentication;

/**
 * Tests the overriding authentication response mechanism.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class SubscribedTokenAuthenticationTest extends CompleteTokenAuthenticationTest
{
    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();

        static::$kernel->getContainer()->get('lexik_jwt_authentication.test.jwt_event_subscriber')->enable();
    }

    public function testAccessSecuredRouteWithoutToken()
    {
        $response = parent::testAccessSecuredRouteWithoutToken();

        $this->assertSame('Custom JWT not found message', $response['message']);
    }

    public function testAccessSecuredRouteWithInvalidToken()
    {
        $response = parent::testAccessSecuredRouteWithInvalidToken();

        $this->assertSame('Custom JWT invalid message', $response['message']);
    }

    /**
     * @group time-sensitive
     */
    public function testAccessSecuredRouteWithExpiredToken()
    {
        $response = parent::testAccessSecuredRouteWithExpiredToken();

        $this->assertSame('Custom JWT invalid message', $response['message']);
    }
}
