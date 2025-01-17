<?php
declare(strict_types=1);

namespace ShlinkioTest\Shlink\Rest\Action\ShortUrl;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\UriInterface;
use Shlinkio\Shlink\Core\Entity\ShortUrl;
use Shlinkio\Shlink\Core\Service\UrlShortenerInterface;
use Shlinkio\Shlink\Rest\Action\ShortUrl\SingleStepCreateShortUrlAction;
use Shlinkio\Shlink\Rest\Service\ApiKeyServiceInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequestFactory;
use Zend\I18n\Translator\Translator;

class SingleStepCreateShortUrlActionTest extends TestCase
{
    /**
     * @var SingleStepCreateShortUrlAction
     */
    private $action;
    /**
     * @var ObjectProphecy
     */
    private $urlShortener;
    /**
     * @var ObjectProphecy
     */
    private $apiKeyService;

    public function setUp()
    {
        $this->urlShortener = $this->prophesize(UrlShortenerInterface::class);
        $this->apiKeyService = $this->prophesize(ApiKeyServiceInterface::class);

        $this->action = new SingleStepCreateShortUrlAction(
            $this->urlShortener->reveal(),
            Translator::factory([]),
            $this->apiKeyService->reveal(),
            [
                'schema' => 'http',
                'hostname' => 'foo.com',
            ]
        );
    }

    /**
     * @test
     */
    public function errorResponseIsReturnedIfInvalidApiKeyIsProvided()
    {
        $request = ServerRequestFactory::fromGlobals()->withQueryParams(['apiKey' => 'abc123']);
        $findApiKey = $this->apiKeyService->check('abc123')->willReturn(false);

        /** @var JsonResponse $resp */
        $resp = $this->action->handle($request);
        $payload = $resp->getPayload();

        $this->assertEquals(400, $resp->getStatusCode());
        $this->assertEquals('INVALID_ARGUMENT', $payload['error']);
        $this->assertEquals('No API key was provided or it is not valid', $payload['message']);
        $findApiKey->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function errorResponseIsReturnedIfNoUrlIsProvided()
    {
        $request = ServerRequestFactory::fromGlobals()->withQueryParams(['apiKey' => 'abc123']);
        $findApiKey = $this->apiKeyService->check('abc123')->willReturn(true);

        /** @var JsonResponse $resp */
        $resp = $this->action->handle($request);
        $payload = $resp->getPayload();

        $this->assertEquals(400, $resp->getStatusCode());
        $this->assertEquals('INVALID_ARGUMENT', $payload['error']);
        $this->assertEquals('A URL was not provided', $payload['message']);
        $findApiKey->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function properDataIsPassedWhenGeneratingShortCode()
    {
        $request = ServerRequestFactory::fromGlobals()->withQueryParams([
            'apiKey' => 'abc123',
            'longUrl' => 'http://foobar.com',
        ]);
        $findApiKey = $this->apiKeyService->check('abc123')->willReturn(true);
        $generateShortCode = $this->urlShortener->urlToShortCode(
            Argument::that(function (UriInterface $argument) {
                Assert::assertEquals('http://foobar.com', (string) $argument);
                return $argument;
            }),
            [],
            null,
            null,
            null,
            null
        )->willReturn((new ShortUrl())->setLongUrl(''));

        $resp = $this->action->handle($request);

        $this->assertEquals(200, $resp->getStatusCode());
        $findApiKey->shouldHaveBeenCalled();
        $generateShortCode->shouldHaveBeenCalled();
    }
}
