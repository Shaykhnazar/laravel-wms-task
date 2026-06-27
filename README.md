# Inventory Management API

Laravel API for purchasing products, FIFO order allocation, refunds, and stock/profit reports.

## Requirements

- Docker & Docker Compose
- PHP 8.5 + Composer (host only, for running tests)

## Setup

### 1. Clone and configure

```bash
git clone https://github.com/Shaykhnazar/laravel-wms-task
cd laravel-wms-task

cp .env.example .env
```

Edit `.env` — only these values are required for Docker:

| Variable | Example | Notes |
|----------|---------|-------|
| `DB_DATABASE` | `inventory` | MySQL database name |
| `DB_USERNAME` | `root` | MySQL user |
| `DB_PASSWORD` | `change_me` | Used by MySQL container and the app |
| `APP_PORT` | `8000` | Host port for the API (optional) |

Leave `DB_HOST=mysql` and `DB_CONNECTION=mysql` as in `.env.example`. The app container overrides nothing else you need to touch.

`APP_KEY` is generated automatically on first container start if missing.

### 2. Start with Docker

```bash
docker compose up --build
```

On startup the app:

1. Waits for MySQL
2. Runs pending migrations
3. Seeds demo data once (empty database only)
4. Serves the API at **http://localhost:8000**

| Command | Effect |
|---------|--------|
| `docker compose down` | Stops containers, keeps data |
| `docker compose up` | Restarts with existing data |
| `docker compose down -v` | Full reset (deletes database volume) |
| `docker compose up --build -d` | Rebuild after code/config changes |

After changing `DB_PASSWORD`, run `docker compose down -v && docker compose up --build` so MySQL is recreated with the new password.

### 3. Run tests

Install dependencies on the **host** (PHPUnit is not in the Docker image):

```bash
composer install
```

**Feature tests** (SQLite, no Docker required):

```bash
php artisan test tests/Feature/Api
```

**Stress tests** (MySQL row locks — Docker MySQL must be running):

```bash
php artisan test --configuration=phpunit.stress.xml
```

Stress config connects to `127.0.0.1:3306` and uses `DB_PASSWORD` from your `.env`.

### 4. Test with Postman

Import file from the `postman/` folder:

| File | Purpose |
|------|---------|
| `postman/Inventory-API.postman_collection.json` | All 6 API endpoints in order |

In Postman:

1. Import the collection and environment
2. Select **Inventory API — Local Docker** environment
3. Run requests **top to bottom** — later steps use `batch_id` / `batch_item_id` saved from earlier responses

Seeded demo IDs (after fresh seed):

| Variable | Value |
|----------|-------|
| `provider_id` | 1 |
| `storage_id` | 1 |
| `client_id` | 1 |
| `product_id` | 1 (Earl Grey) |
| `product_id_2` | 2 (Green Tea) |

Health check: `GET http://localhost:8000/up`

## API endpoints

| Method | Path | Description |
|--------|------|-------------|
| POST | `/api/purchases` | Purchase products into storage |
| POST | `/api/batches/{batch}/refunds` | Refund unsold stock to provider |
| GET | `/api/products/available` | Products with stock (`?per_page=1–100`) |
| POST | `/api/orders` | Create order (FIFO allocation) |
| GET | `/api/storages/remaining?date=YYYY-MM-DD` | Remaining stock as of date |
| GET | `/api/batches/profit` | Profit per batch |

## Architecture

```
Request → Controller → Service → Eloquent → API Resource
```

Business logic lives in `app/Services/`.
