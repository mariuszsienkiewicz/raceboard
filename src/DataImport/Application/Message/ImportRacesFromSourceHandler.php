<?php

declare(strict_types=1);

namespace App\DataImport\Application\Message;

use App\DataImport\Application\ImportRacesHandler;
use App\DataImport\Domain\ImportAdapterInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ImportRacesFromSourceHandler
{
    /** @var array<string, ImportAdapterInterface> */
    private array $adaptersByName;

    /**
     * @param iterable<ImportAdapterInterface> $adapters
     */
    public function __construct(
        #[AutowireIterator('app.import_adapter')]
        iterable $adapters,
        private ImportRacesHandler $importHandler,
        private LoggerInterface $logger,
    ) {
        $this->adaptersByName = [];
        foreach ($adapters as $adapter) {
            $this->adaptersByName[$adapter->getName()] = $adapter;
        }
    }

    public function __invoke(ImportRacesFromSource $message): void
    {
        $adapter = $this->adaptersByName[$message->sourceName] ?? null;
        if (null === $adapter) {
            $this->logger->error(\sprintf('Unknown import source: %s', $message->sourceName));

            return;
        }

        $this->logger->info(\sprintf('Starting async import from %s', $message->sourceName));

        $rawData = $adapter->fetch();
        $result = $this->importHandler->handle($rawData);

        $this->logger->info(\sprintf(
            'Import from %s completed: %d imported, %d updated, %d skipped',
            $message->sourceName,
            $result->importedCount,
            $result->updatedCount,
            $result->skippedCount,
        ));
    }
}
