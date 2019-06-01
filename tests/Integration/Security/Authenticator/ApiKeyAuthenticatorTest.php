<?php
declare(strict_types = 1);
/**
 * /tests/Integration/Security/Authenticator/ApiKeyAuthenticatorTest.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */

namespace App\Tests\Integration\Security\Authenticator;

use App\Security\Authenticator\ApiKeyAuthenticator;
use App\Security\Provider\ApiKeyUserProvider;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use function json_encode;

/**
 * Class ApiKeyAuthenticatorTest
 *
 * @package App\Tests\Integration\Security\Authenticator
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class ApiKeyAuthenticatorTest extends KernelTestCase
{
    /**
     * @dataProvider dataProviderTestThatSupportReturnsExpected
     *
     * @param bool    $expected
     * @param Request $request
     */
    public function testThatSupportReturnsExpected(bool $expected, Request $request): void
    {
        /**
         * @var MockObject|ApiKeyUserProvider $apiKeyUserProvider
         */
        $apiKeyUserProvider = $this->getMockBuilder(ApiKeyUserProvider::class)->disableOriginalConstructor()->getMock();

        $authenticator = new ApiKeyAuthenticator($apiKeyUserProvider);

        static::assertSame($expected, $authenticator->supports($request));
    }

    public function testThatStartMethodReturnsExpected(): void
    {
        /**
         * @var MockObject|ApiKeyUserProvider $apiKeyUserProvider
         */
        $apiKeyUserProvider = $this->getMockBuilder(ApiKeyUserProvider::class)->disableOriginalConstructor()->getMock();

        $authenticator = new ApiKeyAuthenticator($apiKeyUserProvider);

        $output = $authenticator->start(new Request());

        static::assertSame(401, $output->getStatusCode());
        static::assertJsonStringEqualsJsonString(
            json_encode(['message' =>  'Authentication Required']),
            $output->getContent()
        );
    }

    /**
     * @dataProvider dataProviderTestThatGetCredentialsReturnsExpected
     *
     * @param array|null $expected
     * @param Request    $request
     */
    public function testThatGetCredentialsReturnsExpected(?array $expected, Request $request): void
    {
        /**
         * @var MockObject|ApiKeyUserProvider $apiKeyUserProvider
         */
        $apiKeyUserProvider = $this->getMockBuilder(ApiKeyUserProvider::class)->disableOriginalConstructor()->getMock();

        $authenticator = new ApiKeyAuthenticator($apiKeyUserProvider);

        static::assertSame($expected, $authenticator->getCredentials($request));
    }

    /**
     * @dataProvider dataProviderTestThatGetUserReturnsExpected
     *
     * @param mixed $credentials
     */
    public function testThatGetUserReturnsExpectedWhenCredentialsIsInvalid($credentials): void
    {
        /**
         * @var MockObject|ApiKeyUserProvider $apiKeyUserProvider
         */
        $apiKeyUserProvider = $this->getMockBuilder(ApiKeyUserProvider::class)->disableOriginalConstructor()->getMock();

        $authenticator = new ApiKeyAuthenticator($apiKeyUserProvider);

        static::assertNull($authenticator->getUser($credentials, $apiKeyUserProvider));
    }

    /**
     * @return Generator
     */
    public function dataProviderTestThatSupportReturnsExpected(): Generator
    {
        yield [false, new Request()];

        $request = new Request();
        $request->headers = new ParameterBag(['Authorization' => 'ApiKey']);

        yield [false, $request];

        $request = new Request();
        $request->headers = new ParameterBag(['Authorization' => 'ApiKey somekey']);

        yield [true, $request];
    }

    /**
     * @return Generator
     */
    public function dataProviderTestThatGetCredentialsReturnsExpected(): Generator
    {
        yield [null, new Request()];

        $request = new Request();
        $request->headers = new ParameterBag(['Authorization' => 'ApiKey']);

        yield [null, $request];

        $request = new Request();
        $request->headers = new ParameterBag(['Authorization' => 'ApiKey somekey']);

        yield [['token' => 'somekey'], $request];
    }

    /**
     * @return Generator
     */
    public function dataProviderTestThatGetUserReturnsExpected(): Generator
    {
        yield [null];
        yield ['foobar'];
        yield [123];
        yield [new stdClass()];
        yield [[]];
        yield [['foobar']];
        yield [['foobar' => 'barfoo']];
        yield [['token' => null]];
    }
}
