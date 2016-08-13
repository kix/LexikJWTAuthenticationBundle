<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\Bundle\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JWTEventSubscriber implements EventSubscriberInterface
{
    private static $enabled = false;

    public static function enable()
    {
        self::$enabled = true;
    }

    public static function disable()
    {
        self::$enabled = false;
    }

    public static function getSubscribedEvents()
    {
        if (false === self::$enabled) {
            return [];
        }

        return [
            Events::JWT_INVALID   => 'onJWTInvalid',
            Events::JWT_NOT_FOUND => 'onJWTNotFound',
        ];
    }

    public function onJWTInvalid(JWTInvalidEvent $event)
    {
        $response = $event->getResponse();

        if ($response instanceof JWTAuthenticationFailureResponse) {
            $response->setMessage('Custom JWT invalid message');
        }
    }

    public function onJWTNotFound(JWTNotFoundEvent $event)
    {
        $response = $event->getResponse();

        if ($response instanceof JWTAuthenticationFailureResponse) {
            $response->setMessage('Custom JWT not found message');
        }
    }
}
