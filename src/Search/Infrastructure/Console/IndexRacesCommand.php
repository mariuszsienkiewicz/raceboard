<?php

declare(strict_types=1);

namespace App\Search\Infrastructure\Console;

use App\RaceCatalog\Domain\Repository\RaceRepositoryInterface;
use App\Search\Domain\SearchIndexInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:search:index',
    description: 'Index races in search engine',
)]
class IndexRacesCommand extends Command
{
    public function __construct(
        private RaceRepositoryInterface $repository,
        private SearchIndexInterface $searchIndex,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->searchIndex->configureIndex();
        $races = $this->repository->findAll();
        $this->searchIndex->indexAll($races);

        $io->success(\sprintf('Indexed %d races in MeiliSearch', \count($races)));

        return Command::SUCCESS;
    }
}
