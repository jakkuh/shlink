<?php
declare(strict_types=1);

namespace Shlinkio\Shlink\CLI;

use Shlinkio\Shlink\Common\Service\IpApiLocationResolver;
use Shlinkio\Shlink\Common\Service\PreviewGenerator;
use Shlinkio\Shlink\Core\Service;
use Shlinkio\Shlink\Rest\Service\ApiKeyService;
use Symfony\Component\Console\Application;
use Zend\I18n\Translator\Translator;
use Zend\ServiceManager\AbstractFactory\ConfigAbstractFactory;

return [

    'dependencies' => [
        'factories' => [
            Application::class => Factory\ApplicationFactory::class,

            Command\ShortUrl\GenerateShortUrlCommand::class => ConfigAbstractFactory::class,
            Command\ShortUrl\ResolveUrlCommand::class => ConfigAbstractFactory::class,
            Command\ShortUrl\ListShortUrlsCommand::class => ConfigAbstractFactory::class,
            Command\ShortUrl\GetVisitsCommand::class => ConfigAbstractFactory::class,
            Command\ShortUrl\GeneratePreviewCommand::class => ConfigAbstractFactory::class,
            Command\ShortUrl\DeleteShortUrlCommand::class => ConfigAbstractFactory::class,

            Command\Visit\ProcessVisitsCommand::class => ConfigAbstractFactory::class,

            Command\Config\GenerateCharsetCommand::class => ConfigAbstractFactory::class,
            Command\Config\GenerateSecretCommand::class => ConfigAbstractFactory::class,

            Command\Api\GenerateKeyCommand::class => ConfigAbstractFactory::class,
            Command\Api\DisableKeyCommand::class => ConfigAbstractFactory::class,
            Command\Api\ListKeysCommand::class => ConfigAbstractFactory::class,

            Command\Tag\ListTagsCommand::class => ConfigAbstractFactory::class,
            Command\Tag\CreateTagCommand::class => ConfigAbstractFactory::class,
            Command\Tag\RenameTagCommand::class => ConfigAbstractFactory::class,
            Command\Tag\DeleteTagsCommand::class => ConfigAbstractFactory::class,
        ],
    ],

    ConfigAbstractFactory::class => [
        Command\ShortUrl\GenerateShortUrlCommand::class => [
            Service\UrlShortener::class,
            'translator',
            'config.url_shortener.domain',
        ],
        Command\ShortUrl\ResolveUrlCommand::class => [Service\UrlShortener::class, 'translator'],
        Command\ShortUrl\ListShortUrlsCommand::class => [
            Service\ShortUrlService::class,
            'translator',
            'config.url_shortener.domain',
        ],
        Command\ShortUrl\GetVisitsCommand::class => [Service\VisitsTracker::class, 'translator'],
        Command\ShortUrl\GeneratePreviewCommand::class => [
            Service\ShortUrlService::class,
            PreviewGenerator::class,
            'translator',
        ],
        Command\ShortUrl\DeleteShortUrlCommand::class => [
            Service\ShortUrl\DeleteShortUrlService::class,
            'translator',
        ],

        Command\Visit\ProcessVisitsCommand::class => [
            Service\VisitService::class,
            IpApiLocationResolver::class,
            'translator',
        ],

        Command\Config\GenerateCharsetCommand::class => ['translator'],
        Command\Config\GenerateSecretCommand::class => ['translator'],

        Command\Api\GenerateKeyCommand::class => [ApiKeyService::class, 'translator'],
        Command\Api\DisableKeyCommand::class => [ApiKeyService::class, 'translator'],
        Command\Api\ListKeysCommand::class => [ApiKeyService::class, 'translator'],

        Command\Tag\ListTagsCommand::class => [Service\Tag\TagService::class, Translator::class],
        Command\Tag\CreateTagCommand::class => [Service\Tag\TagService::class, Translator::class],
        Command\Tag\RenameTagCommand::class => [Service\Tag\TagService::class, Translator::class],
        Command\Tag\DeleteTagsCommand::class => [Service\Tag\TagService::class, Translator::class],
    ],

];
