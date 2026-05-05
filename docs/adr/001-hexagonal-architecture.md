# ADR 001: Hexagonal Architecture

## Status
Accepted

## Context
The project needs a clear separation between business logic and infrastructure (Doctrine, Symfony, external APIs) to keep the domain testable and framework-independent.

## Decision
Use hexagonal architecture with three layers per bounded context:
- **Domain** — pure PHP, no framework dependencies
- **Application** — use cases, command/query handlers
- **Infrastructure** — Doctrine, HTTP controllers, scrapers

Dependencies point inward only. Domain never imports from Application or Infrastructure.

## Consequences
- Domain classes are testable without booting Symfony kernel
- Switching infrastructure (e.g. Elasticsearch → MeiliSearch) requires only a new adapter
- Slightly more boilerplate (interfaces, adapters) than a standard Symfony CRUD app
