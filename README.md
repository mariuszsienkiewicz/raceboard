# RaceBoard

Aggregator of running races in Poland. Scrapes data from multiple sources, deduplicates and enriches race information, and provides a searchable database with full-text search powered by MeiliSearch.

**Live demo:** [raceboard.heaps.pl](https://raceboard.heaps.pl/)

A **monorepo** built as a portfolio project: a Symfony 7.4 backend with hexagonal architecture and DDD, a **Go microservice** for concurrent geocoding, an async event-driven processing layer (Symfony Messenger), and a Prometheus + Grafana observability stack.

## Features

- **Multi-source data import**: scrapes races from `MaratonyPolskie.pl` and `running.life` with automatic deduplication and data enrichment
- **Async, event-driven processing**: import, geocoding, search reindexing and rating recalculation run asynchronously via Symfony Messenger and domain events
- **Go geocoding microservice**: concurrent, rate-limited geocoding of race cities with an in-memory cache and graceful shutdown
- **Full-text search**: MeiliSearch-powered search with filters (city, voivodeship, distance)
- **REST API**: API Platform for race listings (custom paginated state providers), custom controllers for auth and user features
- **User accounts**: JWT authentication, registration, race watchlist
- **Reviews & ratings**: authenticated users can review races; average rating is recalculated asynchronously on each new review
- **Observability**: Prometheus metrics exposed by the geocoder, visualized in Grafana (dev)
- **React frontend**: TypeScript SPA consuming the API

## Architecture

The project follows **hexagonal architecture** with bounded contexts, spanning two services in a monorepo:

```
.
├── src/                 - Symfony backend (bounded contexts below)
│   ├── RaceCatalog/      - Race, Edition, Distance (Aggregate Root + Value Objects)
│   ├── DataImport/       - Scrapers, normalizers, deduplication, data enrichment
│   ├── Search/           - MeiliSearch integration, search API
│   ├── UserProfile/      - User accounts, JWT auth, watchlist
│   ├── Review/           - Race reviews and ratings
│   └── Shared/           - Shared Kernel: cross-context identifiers, Slugifier
└── geocoder/            - Go microservice: concurrent geocoding via Nominatim
```

Each bounded context follows the same structure:

```
BoundedContext/
├── Domain/              — Pure PHP, no framework dependencies
│   ├── Model/           — Entities, Value Objects
│   ├── Repository/      — Interfaces (ports)
│   ├── Service/         — Domain services & ports (e.g. cross-context checks)
│   ├── Event/           — Domain events
│   └── Exception/       — Domain-specific exceptions
├── Application/         — Use cases, command/event handlers, normalizers
└── Infrastructure/      — Doctrine, HTTP controllers, scrapers, adapters
```

### Cross-context communication

Bounded contexts stay isolated. They never reach into each other's internals (repositories, entities), they communicate only through **published domain events** and **narrow ports** defined by the consumer:

- `Review` validates that a race exists through its own `RaceExistenceCheckerInterface` port; an adapter in `Review/Infrastructure` delegates to `RaceCatalog` — the `Review` domain never imports `RaceCatalog`'s repository.
- `RaceCatalog` recalculates a race's average rating through its own `RaceRatingProviderInterface` port, fed by an adapter that reads from `Review`.
- Cross-context identifiers (`RaceId`, `UserId`) live in `Shared` as a **Shared Kernel**; context-private IDs (`ReviewId`, `EditionId`, …) stay in their own contexts.

**Key architectural decisions** are documented in `docs/adr/`.

## Geocoder microservice (Go)

A standalone Go service that geocodes race cities via the OpenStreetMap Nominatim API. Kept as a separate service to isolate rate-limited external calls and to exercise Go's concurrency model.

- **Worker pool** (fan-out/fan-in) geocodes many cities concurrently
- **Token-bucket rate limiter** (`golang.org/x/time/rate`) respects Nominatim's 1 req/s policy
- **Thread-safe in-memory cache** (`sync.RWMutex`) deduplicates lookups
- **Context propagation & cancellation** throughout the pipeline
- **Graceful shutdown** on `SIGINT`/`SIGTERM` — stops accepting new requests, drains in-flight ones (bounded by a timeout) before exiting
- **Ports & adapters**: the service depends on consumer-defined interfaces (`Cache`, `Geocoder`, `MetricsRecorder`); implementations are injected, keeping the core testable and free of Prometheus/HTTP details
- **Prometheus metrics** exposed at `/metrics`; **`/health`** for liveness

Endpoints: `POST /geocode` (batch), `GET /health`, `GET /metrics`.

## Event-driven flow

Domain events are published to a dedicated event bus and processed asynchronously. A single event can trigger multiple independent reactions:

```
Review added (POST /reviews)
    → AddReviewHandler saves review, dispatches ReviewAdded (event bus, async)
        → RaceCatalog recalculates the race's average rating (idempotent, DB-computed)

Import finished
    → RacesImported (async)
        → geocode new races (Go service)
        → send watchlist notifications
        → reindex in MeiliSearch
    → geocoding done → RacesGeocoded → reindex updated coordinates
```

The rating listener recomputes the average from the current DB state (not incrementally), so it is **idempotent and order-independent** — safe under retries and async processing.

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.4, Symfony 7.4 LTS |
| Microservice | Go (net/http, worker pool, rate limiter) |
| Async | Symfony Messenger (Redis transport), command & event buses |
| Database | PostgreSQL 16 |
| Search | MeiliSearch |
| Cache | Redis |
| Auth | JWT (LexikJWTAuthenticationBundle) |
| API | API Platform 3 + custom controllers |
| Frontend | React 19, TypeScript, Vite, Tailwind CSS |
| ORM | Doctrine with XML mapping (no annotations in Domain) |
| Observability | Prometheus, Grafana (dev) |
| Testing | PHPUnit, PHPStan level 8, PHP CS Fixer; Go: `go test -race` |
| CI | GitHub Actions (PHPStan + PHPUnit + CS Fixer + Go tests) |
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

Process the async queue (import, geocoding, reindexing, rating recalculation):

```bash
docker compose exec php bin/console messenger:consume async -vv
```

Services:

- Backend API — `http://localhost:8080`
- Geocoder — `http://localhost:8090` (`/health`, `/metrics`)
- Prometheus — `http://localhost:9090`
- Grafana — `http://localhost:3000`

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
| GET | `/api/races` | List races (paginated, custom API Platform state provider) |
| GET | `/api/races/{id}` | Race details with editions and distances |
| GET | `/api/search?q=&city=&voivodeship=&distance=` | Full-text search with filters |
| GET | `/api/races/{raceId}/reviews` | Race reviews and average rating |
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
# Backend: all tests
docker compose exec php vendor/bin/phpunit

# Backend: static analysis
docker compose exec php vendor/bin/phpstan analyse

# Backend: code style
docker compose exec php vendor/bin/php-cs-fixer fix --dry-run --diff

# Geocoder: unit tests with race detector
cd geocoder && go test -race ./...
```

## Data Import Pipeline

```
External Source → Adapter (scraper) → RawRaceData (DTO)
    → DateParser + VoivodeshipNormalizer + DistanceNormalizer
    → DuplicateDetector (slug match → fuzzy name match)
    → Race creation or enrichment (voivodeship, distances)
    → Doctrine persist + MeiliSearch index
    → RacesImported event → async geocoding, notifications, reindex
```

The import pipeline supports multiple sources via tagged adapters. Adding a new source requires implementing `ImportAdapterInterface` — no changes to the import handler.

## Observability

The geocoder exposes Prometheus metrics at `/metrics`:

- `geocode_requests_total`, `geocode_in_flight`, `geocode_duration_seconds` (histogram)
- `cache_hits_total`, `cache_misses_total`, `nominatim_errors_total`

Prometheus scrapes the service and Grafana visualizes it (request rate, in-flight requests, cache hit ratio computed from counters, p50/p95 geocoding latency via `histogram_quantile`).

Observability runs in the **dev** stack only. The production VPS deliberately omits Prometheus/Grafana to conserve memory; the `/metrics` endpoint still exists in production, so a scraper can be added later without code changes.

## License

MIT
