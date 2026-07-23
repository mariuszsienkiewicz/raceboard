<?php

declare(strict_types=1);

namespace App\RaceCatalog\Domain\Model;

use App\RaceCatalog\Domain\Event\RaceCreated;
use App\RaceCatalog\Domain\Exception\DuplicateEditionException;
use App\Shared\Domain\Model\RaceId;
use App\Shared\Domain\Slugifier;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Race
{
    /** @var Collection<int, Edition> */
    private Collection $editions;

    /** @var list<object> */
    private array $domainEvents = [];

    public function __construct(
        private readonly RaceId $id,
        private string $name,
        private string $slug,
        private string $city,
        private string $voivodeship,
        private string $country = 'PL',
        private ?float $latitude = null,
        private ?float $longitude = null,
        private ?float $averageRating = null,
    ) {
        $this->editions = new ArrayCollection();
        $this->recordEvent(new RaceCreated($this->id));
    }

    public static function create(
        RaceId $id,
        string $name,
        string $city,
        string $voivodeship,
        string $country = 'PL',
        ?float $latitude = null,
        ?float $longitude = null,
    ): self {
        $slug = Slugifier::slugify($name);

        return new self($id, $name, $slug, $city, $voivodeship, $country, $latitude, $longitude);
    }

    public function addEdition(Edition $edition): void
    {
        foreach ($this->editions as $existing) {
            if ($existing->getDate()->format('Y') === $edition->getDate()->format('Y')) {
                throw DuplicateEditionException::forYear($this->name, (int) $edition->getDate()->format('Y'));
            }
        }

        $edition->assignRace($this);
        $this->editions->add($edition);
    }

    /** @return list<Edition> */
    public function getUpcomingEditions(): array
    {
        $now = new \DateTimeImmutable('today');

        return $this->editions->filter(
            static fn (Edition $e): bool => $e->getDate() >= $now,
        )->getValues();
    }

    public function getId(): RaceId
    {
        return $this->id;
    }

    public function getIdString(): string
    {
        return $this->id->toString();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getVoivodeship(): string
    {
        return $this->voivodeship;
    }

    public function updateVoivodeship(string $voivodeship): void
    {
        if ('' !== $this->voivodeship) {
            return;
        }

        $this->voivodeship = $voivodeship;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function hasCoordinates(): bool
    {
        return null !== $this->latitude && null !== $this->longitude;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setCoordinates(float $latitude, float $longitude): void
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public function getAverageRating(): ?float
    {
        return $this->averageRating;
    }

    public function updateAverageRating(float $averageRating): void
    {
        $this->averageRating = $averageRating;
    }

    /** @return list<Edition> */
    public function getEditions(): array
    {
        return $this->editions->getValues();
    }

    public function findEditionByDate(\DateTimeImmutable $date): ?Edition
    {
        foreach ($this->editions as $edition) {
            $diff = abs($edition->getDate()->getTimestamp() - $date->getTimestamp());
            if ($diff <= 86400) {
                return $edition;
            }
        }

        return null;
    }

    /** @return list<object> */
    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }

    private function recordEvent(object $event): void
    {
        $this->domainEvents[] = $event;
    }
}
