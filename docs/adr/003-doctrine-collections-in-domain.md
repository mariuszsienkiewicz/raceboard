# ADR 003: Using Doctrine Collections in Domain Layer

## Status
Accepted

## Context
Doctrine ORM hydrates entity relations as `PersistentCollection`, which cannot be assigned to a property typed as `array` in PHP 8.4 with strict types. The domain needs to hold collections of child entities (Race → Editions, Edition → Distances).

## Decision
Use `Doctrine\Common\Collections\Collection` interface and `ArrayCollection` in domain entities. Getters return `array` via `$collection->getValues()` to hide the implementation detail.

## Consequences
- Domain has a dependency on `doctrine/collections` (a standalone library, not doctrine/orm)
- Internal state uses Collection, but the public API exposes plain arrays
- This is a pragmatic compromise accepted across the Symfony + DDD ecosystem
