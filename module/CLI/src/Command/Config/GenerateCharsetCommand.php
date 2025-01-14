<?php
declare(strict_types=1);

namespace Shlinkio\Shlink\CLI\Command\Config;

use Shlinkio\Shlink\Core\Service\UrlShortener;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zend\I18n\Translator\TranslatorInterface;
use function sprintf;
use function str_shuffle;

class GenerateCharsetCommand extends Command
{
    public const NAME = 'config:generate-charset';

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::NAME)
             ->setDescription(sprintf($this->translator->translate(
                 'Generates a character set sample just by shuffling the default one, "%s". '
                 . 'Then it can be set in the SHORTCODE_CHARS environment variable'
             ), UrlShortener::DEFAULT_CHARS));
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $charSet = str_shuffle(UrlShortener::DEFAULT_CHARS);
        (new SymfonyStyle($input, $output))->success(
            sprintf($this->translator->translate('Character set: "%s"'), $charSet)
        );
    }
}
