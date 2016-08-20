<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;

/**
 * Tests the overriding authentication response mechanism.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class SubscribedTokenAuthenticationTest extends CompleteTokenAuthenticationTest
{
    private static $subscriber;

    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();

        self::$subscriber = static::$kernel->getContainer()->get('lexik_jwt_authentication.test.jwt_event_subscriber');
    }

    public function testAccessSecuredRouteWithoutToken()
    {
        self::$subscriber->setListener(Events::JWT_NOT_FOUND, function (JWTNotFoundEvent $e) {
            $e->getResponse()->setMessage('Custom JWT not found message');
        });

        $response = parent::testAccessSecuredRouteWithoutToken();

        $this->assertSame('Custom JWT not found message', $response['message']);
    }

    public function testAccessSecuredRouteWithInvalidToken()
    {
        self::$subscriber->setListener(Events::JWT_INVALID, function (JWTInvalidEvent $e) {
            $e->getResponse()->setMessage('Custom JWT invalid message');
        });

        $response = parent::testAccessSecuredRouteWithInvalidToken();

        self::$subscriber->unsetListener(Events::JWT_INVALID);

        $this->assertSame('Custom JWT invalid message', $response['message']);
    }

    public function testAccessSecuredRouteWithInvalidJWTDecodedEvent()
    {
        self::$subscriber->setListener(Events::JWT_DECODED, function (JWTDecodedEvent $e) {
            $e->markAsInvalid();
        });

        static::$client = static::createAuthenticatedClient();
        static::$client->request('GET', '/api/secured');

        $responseBody = json_decode(static::$client->getResponse()->getContent(), true);

        $this->assertSame('Invalid JWT Token', $responseBody['message']);

        self::$subscriber->unsetListener(Events::JWT_DECODED);
    }

    /**
     * @group time-sensitive
     */
    public function testAccessSecuredRouteWithExpiredToken()
    {
        self::$subscriber->setListener(Events::JWT_INVALID, function (JWTInvalidEvent $e) {
            $e->getResponse()->setMessage('Custom JWT Expired Token message');
        });

        $response = parent::testAccessSecuredRouteWithExpiredToken();

        $this->assertSame('Custom JWT Expired Token message', $response['message']);

        self::$subscriber->unsetListener(Events::JWT_INVALID);
    }
}
