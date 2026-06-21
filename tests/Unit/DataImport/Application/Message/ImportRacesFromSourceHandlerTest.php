<?php

declare(strict_types=1);

namespace App\Tests\Unit\DataImport\Application\Message;

use App\DataImport\Application\ImportRacesHandler;
use App\DataImport\Application\ImportResult;
use App\DataImport\Application\Message\ImportRacesFromSource;
use App\DataImport\Application\Message\ImportRacesFromSourceHandler;
use App\DataImport\Domain\ImportAdapterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ImportRacesFromSourceHandlerTest extends TestCase
{
    private ImportAdapterInterface&MockObject $adapterMock;
    private ImportRacesHandler&MockObject $importHandlerMock;
    private LoggerInterface&MockObject $loggerMock;
    private ImportRacesFromSourceHandler $handler;

    protected function setUp(): void
    {
        $this->adapterMock = $this->createMock(ImportAdapterInterface::class);
        $this->adapterMock->method('getName')->willReturn('test-source');

        $this->importHandlerMock = $this->createMock(ImportRacesHandler::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->handler = new ImportRacesFromSourceHandler(
            adapters: [$this->adapterMock],
            importHandler: $this->importHandlerMock,
            logger: $this->loggerMock,
        );
    }

    public function testHandlesValidSource(): void
    {
        $this->adapterMock->expects($this->once())->method('fetch')->willReturn([]);
        $this->importHandlerMock->expects($this->once())->method('handle')->with([])->willReturn(new ImportResult());
        $this->loggerMock->expects($this->never())->method('error');

        $this->handler->__invoke(new ImportRacesFromSource('test-source'));
    }

    public function testLogsErrorForUnknownSource(): void
    {
        $this->adapterMock->expects($this->never())->method('fetch');
        $this->loggerMock->expects($this->once())->method('error');
        $this->importHandlerMock->expects($this->never())->method('handle');

        $this->handler->__invoke(new ImportRacesFromSource('unknown-source'));
    }
}
