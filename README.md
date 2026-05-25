# RaceBoard

Aggregator of running races in Poland. Scrapes data from multiple sources, deduplicates and enriches race information, and provides a searchable database with full-text search powered by MeiliSearch.

Built as a portfolio project demonstrating Symfony 7.4 with hexagonal architecture, DDD patterns, and clean separation of concerns.

## Features

- **Multi-source data import**:scrapes races from `MaratonyPolskie.pl` and `running.life` with automatic deduplication and data enrichment
- **Full-text search**: MeiliSearch-powered search with filters (city, voivodeship, distance)
- **REST API**: API Platform for race listings, custom controllers for auth and user features
- **User accounts**: JWT authentication, registration, race watchlist
- **Reviews**: authenticated users can rate and review races
- **React frontend**: TypeScript SPA consuming the API

## Architecture

The project follows **hexagonal architecture** with bounded contexts:

```
src/
├── RaceCatalog/         - Race, Edition, Distance (Aggregate Root + Value Objects)
├── DataImport/          - Scrapers, normalizers, deduplication, data enrichment
├── Search/              - MeiliSearch integration, search API
├── UserProfile/         - User accounts, JWT auth, watchlist
├── Review/              - Race reviews and ratings
└── Shared/              - AbstractId, Slugifier
```

Each bounded context follows the same structure:

```
BoundedContext/
├── Domain/              — Pure PHP, no framework dependencies
│   ├── Model/           — Entities, Value Objects
│   ├── Repository/      — Interfaces (ports)
│   └── Exception/       — Domain-specific exceptions
├── Application/         — Use cases, handlers, normalizers
└── Infrastructure/      — Doctrine, HTTP controllers, scrapers (adapters)
```

**Key architectural decisions** are documented in `docs/adr/`.

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.4, Symfony 7.4 LTS |
| Database | PostgreSQL 16 |
| Search | MeiliSearch |
| Cache | Redis |
| Auth | JWT (LexikJWTAuthenticationBundle) |
| API | API Platform 3 + custom controllers |
| Frontend | React 19, TypeScript, Vite, Tailwind CSS |
| ORM | Doctrine with XML mapping (no annotations in Domain) |
| Testing | PHPUnit, PHPStan level 8, PHP CS Fixer |
| CI | GitHub Actions (PHPStan + PHPUnit + CS Fixer) |
| Containers | Docker Compose |

## Getting Started

### Prerequisites

- Docker & Docker Compose
- Node.js 20+ (for frontend)

### Backend

```bash
git clone https://github.com/mariuszsienkiewicz/raceboard.git
cd raceboard
docker compose up -d
docker compose exec php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec php bin/console lexik:jwt:generate-keypair
```

Import races and build search index:

```bash
docker compose exec php bin/console app:import maratony-polskie
docker compose exec php bin/console app:import running-life
docker compose exec php bin/console app:search:index
```

Backend runs at `http://localhost:8080`.

### Frontend

```bash
cd frontend
npm install
npm run dev
```

Frontend runs at `http://localhost:5173`.

## API Endpoints

### Public

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/races` | List all races (API Platform) |
| GET | `/api/races/{id}` | Race details with editions and distances |
| GET | `/api/search?q=&city=&voivodeship=&distance=` | Full-text search with filters |
| GET | `/api/races/{raceId}/reviews` | Race reviews |
| POST | `/api/register` | Create account |
| POST | `/api/login` | Get JWT token |

### Authenticated (requires `Authorization: Bearer <token>`)

| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/races/{raceId}/reviews` | Add review |
| GET | `/api/me/watchlist` | User's watchlist with race details |
| POST | `/api/me/watchlist/{raceId}` | Add race to watchlist |
| DELETE | `/api/me/watchlist/{raceId}` | Remove from watchlist |
| GET | `/api/me/watchlist/{raceId}/check` | Check if race is watched |

## Testing

```bash
# All tests
docker compose exec php vendor/bin/phpunit

# Static analysis
docker compose exec php vendor/bin/phpstan analyse

# Code style
docker compose exec php vendor/bin/php-cs-fixer fix --dry-run --diff
```

## Data Import Pipeline

```
External Source → Adapter (scraper) → RawRaceData (DTO)
    → DateParser + VoivodeshipNormalizer + DistanceNormalizer
    → DuplicateDetector (slug match → fuzzy name match)
    → Race creation or enrichment (voivodeship, distances)
    → Doctrine persist + MeiliSearch index
```

The import pipeline supports multiple sources via tagged adapters. Adding a new source requires implementing `ImportAdapterInterface` — no changes to the import handler.

## License

MIT
