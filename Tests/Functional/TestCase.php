<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional;

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

        if (isset($options['extra_parameters'])) {
            return new AppKernel('test', true, $options['extra_parameters']);
        }

        return new AppKernel('test', true, []);
    }

    protected static function createAuthenticatedClient()
    {
        if (null === static::$kernel) {
            static::bootKernel();
        }

        $client = static::$kernel->getContainer()->get('test.client');

        $client->request('POST', '/login_check', ['_username' => 'lexik', '_password' => 'dummy']);
        $response = $client->getResponse();
        $body     = json_decode($client->getResponse()->getContent(), true);

        if (!isset($body['token'])) {
            throw new \LogicException('Unable to get a JWT Token through the "/login_check" route.');
        }

        $client->setServerParameter('HTTP_AUTHORIZATION', sprintf('Bearer %s', $body['token']));

        return $client;
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
