# ADR 004: UUID Value Objects for Entity Identity

## Status
Accepted

## Context
Entities need unique identifiers. Options: auto-increment integers (database generates ID), UUIDs as strings, or UUID value objects.

## Decision
Every entity has a dedicated ID value object (RaceId, EditionId, DistanceId) extending a shared `AbstractId` class. IDs are UUIDs generated in application code, not by the database.

## Consequences
- Type safety: PHPStan catches mixing up RaceId and EditionId at compile time
- Application controls identity, not the database — entities have IDs before being persisted
- Requires custom Doctrine types (AbstractIdType + concrete types) for serialization
- IDs are stored as VARCHAR(36) in PostgreSQL