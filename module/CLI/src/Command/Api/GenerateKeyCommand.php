<?php
declare(strict_types=1);

namespace Shlinkio\Shlink\CLI\Command\Api;

use Cake\Chronos\Chronos;
use Shlinkio\Shlink\Rest\Service\ApiKeyServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zend\I18n\Translator\TranslatorInterface;
use function sprintf;

class GenerateKeyCommand extends Command
{
    public const NAME = 'api-key:generate';

    /**
     * @var ApiKeyServiceInterface
     */
    private $apiKeyService;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(ApiKeyServiceInterface $apiKeyService, TranslatorInterface $translator)
    {
        $this->apiKeyService = $apiKeyService;
        $this->translator = $translator;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::NAME)
             ->setDescription($this->translator->translate('Generates a new valid API key.'))
             ->addOption(
                 'expirationDate',
                 'e',
                 InputOption::VALUE_OPTIONAL,
                 $this->translator->translate('The date in which the API key should expire. Use any valid PHP format.')
             );
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $expirationDate = $input->getOption('expirationDate');
        $apiKey = $this->apiKeyService->create(isset($expirationDate) ? Chronos::parse($expirationDate) : null);

        (new SymfonyStyle($input, $output))->success(
            sprintf($this->translator->translate('Generated API key: "%s"'), $apiKey)
        );
    }
}
