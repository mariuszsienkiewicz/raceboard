<?php

declare(strict_types=1);

namespace App\DataImport\Infrastructure\Console;

use App\DataImport\Application\Handler\GeocodeRacesHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:geocode')]
class GeocodeRacesCommand extends Command
{
    public function __construct(
        private GeocodeRacesHandler $handler,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $result = $this->handler->handle();

        if ($result->failedCount > 0) {
            $io->warning(\sprintf('Failed to geocode %d races', $result->failedCount));
        }

        $io->success(\sprintf('Geocoded %d races', $result->geocodedCount));

        return Command::SUCCESS;
    }
}
