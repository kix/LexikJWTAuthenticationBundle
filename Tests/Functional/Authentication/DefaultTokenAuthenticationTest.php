<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\Authentication;

/**
 * Tests the different behaviours of an authentication via JSON Web Token.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class DefaultTokenAuthenticationTest extends CompleteTokenAuthenticationTest
{
    public function testAccessSecuredRouteWithoutToken()
    {
        $response = parent::testAccessSecuredRouteWithoutToken();

        $this->assertEquals('JWT Token not found', $response['message']);
    }

    public function testAccessSecuredRouteWithInvalidToken()
    {
        $response = parent::testAccessSecuredRouteWithInvalidToken();

        $this->assertEquals('Invalid JWT Token', $response['message']);
    }

    /**
     * @group time-sensitive
     */
    public function testAccessSecuredRouteWithExpiredToken()
    {
        $response = parent::testAccessSecuredRouteWithExpiredToken();

        $this->assertSame('Expired JWT Token', $response['message']);
    }
}
