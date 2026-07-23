<?php

declare(strict_types=1);

namespace Tests\Unit\RaceCatalog\Application\Handler;

use App\RaceCatalog\Application\Handler\RecalculateRaceAverageOnReviewAddedHandler;
use App\RaceCatalog\Domain\Model\Race;
use App\RaceCatalog\Domain\Repository\RaceRepositoryInterface;
use App\RaceCatalog\Domain\Service\RaceRatingProviderInterface;
use App\Review\Domain\Event\ReviewAdded;
use App\Shared\Domain\Model\RaceId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RecalculateRaceAverageOnReviewAddedHandlerTest extends TestCase
{
    private RaceRatingProviderInterface&MockObject $raceRatingProvider;
    private RaceRepositoryInterface&MockObject $raceRepository;
    private RecalculateRaceAverageOnReviewAddedHandler $recalculateRaceAverageOnReviewAddedHandler;

    public function setUp(): void
    {
        $this->raceRatingProvider = $this->createMock(RaceRatingProviderInterface::class);
        $this->raceRepository = $this->createMock(RaceRepositoryInterface::class);
        $this->recalculateRaceAverageOnReviewAddedHandler = new RecalculateRaceAverageOnReviewAddedHandler($this->raceRatingProvider, $this->raceRepository);
    }

    public function testRaceAverageIsRecalculated(): void
    {
        $race = Race::create(RaceId::generate(), 'Test Race', 'Test City', 'Test Voivodeship');

        $this->raceRatingProvider->expects($this->once())
            ->method('getAverageRating')
            ->willReturn(4.5);
        $this->raceRepository->expects($this->once())
            ->method('findById')
            ->willReturn($race);
        $this->raceRepository->expects($this->once())
            ->method('save');

        $this->recalculateRaceAverageOnReviewAddedHandler->__invoke(new ReviewAdded($race->getId()));

        $this->assertEqualsWithDelta(4.5, $race->getAverageRating(), 0.001);
    }

    public function testRaceAverageIsNotRecalculatedIfRaceIsNotFound(): void
    {
        $this->raceRatingProvider->expects($this->once())
            ->method('getAverageRating')
            ->willReturn(4.5);
        $this->raceRepository->expects($this->once())
            ->method('findById')
            ->willReturn(null);
        $this->raceRepository->expects($this->never())
            ->method('save');

        $this->recalculateRaceAverageOnReviewAddedHandler->__invoke(new ReviewAdded(RaceId::generate()));
    }

    public function testRaceAverageIsNotRecalculatedIfRaceRatingIsNotAvailable(): void
    {
        $this->raceRatingProvider->expects($this->once())
            ->method('getAverageRating')
            ->willReturn(null);
        $this->raceRepository->expects($this->never())
            ->method('findById');
        $this->raceRepository->expects($this->never())
            ->method('save');

        $this->recalculateRaceAverageOnReviewAddedHandler->__invoke(new ReviewAdded(RaceId::generate()));
    }
}
