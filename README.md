# Laravel Clean Architecture API

Enterprise REST API sample built with Laravel 11, PostgreSQL, Redis, RabbitMQ and Clean Architecture.

## Quick start

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan test
```

Docker:

```bash
docker compose up --build
```

API base URL: `http://localhost:8080/api/v1`.

## Architecture

```text
src/
├── Domain
├── Application
├── Infrastructure
└── Interface
```

Dependencies point inward: HTTP controllers call Application use cases, use cases depend on Domain contracts, and Infrastructure implements those contracts.

## Implemented endpoints

- `POST /api/v1/projects`
- `POST /api/v1/projects/{project}/tasks`

All project endpoints use the standard JSON envelope:

```json
{ "success": true, "data": {}, "error": null, "meta": {} }
```

## Quality gates

- Laravel Pint
- PHPUnit
- PHPStan level 8
- Docker build
- OpenAPI contract at `docs/openapi/openapi.yaml`
