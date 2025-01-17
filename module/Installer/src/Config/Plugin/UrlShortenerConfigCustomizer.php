<?php
declare(strict_types=1);

namespace Shlinkio\Shlink\Installer\Config\Plugin;

use Shlinkio\Shlink\Core\Service\UrlShortener;
use Shlinkio\Shlink\Installer\Model\CustomizableAppConfig;
use Shlinkio\Shlink\Installer\Util\AskUtilsTrait;
use Symfony\Component\Console\Style\SymfonyStyle;
use function array_diff;
use function array_keys;
use function str_shuffle;

class UrlShortenerConfigCustomizer implements ConfigCustomizerInterface
{
    use AskUtilsTrait;

    public const SCHEMA = 'SCHEMA';
    public const HOSTNAME = 'HOSTNAME';
    public const CHARS = 'CHARS';
    public const VALIDATE_URL = 'VALIDATE_URL';
    private const EXPECTED_KEYS = [
        self::SCHEMA,
        self::HOSTNAME,
        self::CHARS,
        self::VALIDATE_URL,
    ];

    public function process(SymfonyStyle $io, CustomizableAppConfig $appConfig): void
    {
        $urlShortener = $appConfig->getUrlShortener();
        $doImport = $appConfig->hasUrlShortener();
        $keysToAskFor = $doImport ? array_diff(self::EXPECTED_KEYS, array_keys($urlShortener)) : self::EXPECTED_KEYS;

        if (empty($keysToAskFor)) {
            return;
        }

        $io->title('URL SHORTENER');
        foreach ($keysToAskFor as $key) {
            $urlShortener[$key] = $this->ask($io, $key);
        }
        $appConfig->setUrlShortener($urlShortener);
    }

    private function ask(SymfonyStyle $io, string $key)
    {
        switch ($key) {
            case self::SCHEMA:
                return $io->choice(
                    'Select schema for generated short URLs',
                    ['http', 'https'],
                    'http'
                );
            case self::HOSTNAME:
                return $this->askRequired($io, 'hostname', 'Hostname for generated URLs');
            case self::CHARS:
                return $io->ask(
                    'Character set for generated short codes (leave empty to autogenerate one)'
                ) ?: str_shuffle(UrlShortener::DEFAULT_CHARS);
            case self::VALIDATE_URL:
                return $io->confirm('Do you want to validate long urls by 200 HTTP status code on response');
        }

        return '';
    }
}
