<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * TestCase.
 */
abstract class TestCase extends WebTestCase
{
    protected static $client;

    /**
     * {@inheritdoc}
     */
    protected static function createKernel(array $options = [])
    {
        require_once __DIR__.'/app/AppKernel.php';

        return new AppKernel('test', true, []);
    }

    protected static function createAuthenticatedClient($token = null)
    {
        if (null === static::$kernel) {
            static::bootKernel();
        }

        $client = static::$kernel->getContainer()->get('test.client');
        $token  = null === $token ? self::getAuthenticatedToken() : $token;

        if (null === $token) {
            throw new \LogicException('Unable to create an authenticated client from a null JWT token');
        }

        $client->setServerParameter('HTTP_AUTHORIZATION', sprintf('Bearer %s', $token));

        return $client;
    }

    protected static function getAuthenticatedToken(Client $client = null)
    {
        if (null === $client && null !== static::$client) {
            $client = static::$client;
        } elseif (null === $client && null === static::$client) {
            throw new \LogicException(sprintf('Method "%s()" expects a "%s" instance as first argument, "%s" given. Instead of passing the client as argument, you can define static::$client.', __METHOD__, Client::class));
        }

        $client->request('POST', '/login_check', ['_username' => 'lexik', '_password' => 'dummy']);
        $response = $client->getResponse();
        $body     = json_decode($client->getResponse()->getContent(), true);

        if (!isset($body['token'])) {
            throw new \LogicException('Unable to get a JWT Token through the "/login_check" route.');
        }

        return $body['token'];
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $fs = new Filesystem();
        $fs->remove(sys_get_temp_dir().'/LexikJWTAuthenticationBundle/');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        static::$kernel = null;
    }
}
