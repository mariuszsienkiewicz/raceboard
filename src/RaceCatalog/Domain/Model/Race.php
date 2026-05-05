<?php

namespace App\RaceCatalog\Domain\Model;

use App\RaceCatalog\Domain\Event\RaceCreated;
use App\RaceCatalog\Domain\Exception\EditionInThePastException;
use App\RaceCatalog\Domain\Exception\DuplicateEditionException;
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
    ) {
        $this->editions = new ArrayCollection();
        $this->recordEvent(new RaceCreated($this->id));
    }

    public static function create(
        RaceId $id,
        string $name,
        string $city,
        string $voivodeship,
    ): self {
        $slug = Slugifier::slugify($name);

        return new self($id, $name, $slug, $city, $voivodeship);
    }

    public function addEdition(Edition $edition): void
    {
        if ($edition->getDate() < new \DateTimeImmutable('today')) {
            throw EditionInThePastException::forRace($this->name, $edition->getDate());
        }

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
            static fn (Edition $e): bool => $e->getDate() >= $now
        )->getValues();
    }

    public function getId(): RaceId
    {
        return $this->id;
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

    public function getCountry(): string
    {
        return $this->country;
    }

    /** @return list<Edition> */
    public function getEditions(): array
    {
        return $this->editions->getValues();
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
