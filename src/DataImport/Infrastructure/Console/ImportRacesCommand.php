<?php

declare(strict_types=1);

namespace App\DataImport\Infrastructure\Console;

use App\DataImport\Application\ImportRacesHandler;
use App\DataImport\Domain\ImportAdapterInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

#[AsCommand(
    name: 'app:import',
    description: 'Import races from external sources',
)]
class ImportRacesCommand extends Command
{
    /** @var array<string, ImportAdapterInterface> */
    private array $adaptersByName;

    /**
     * @param iterable<ImportAdapterInterface> $adapters
     */
    public function __construct(
        #[AutowireIterator('app.import_adapter')]
        iterable $adapters,
        private ImportRacesHandler $handler,
    ) {
        parent::__construct();

        $this->adaptersByName = [];
        foreach ($adapters as $adapter) {
            $this->adaptersByName[$adapter->getName()] = $adapter;
        }
    }

    protected function configure(): void
    {
        $this->addArgument('source', InputArgument::REQUIRED, 'Adapter name (e.g. maratony-polskie)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $source = $input->getArgument('source');

        if (!isset($this->adaptersByName[$source])) {
            $io->error(\sprintf(
                'Unknown source "%s". Available: %s',
                $source,
                implode(', ', array_keys($this->adaptersByName)),
            ));

            return Command::FAILURE;
        }

        $io->info(\sprintf('Importing races from %s...', $source));

        $adapter = $this->adaptersByName[$source];
        $rawData = $adapter->fetch();
        $result = $this->handler->handle($rawData);

        $io->success(\sprintf(
            'Imported %d new races (%d skipped)',
            $result->importedCount,
            $result->skippedCount,
        ));

        return Command::SUCCESS;
    }
}
