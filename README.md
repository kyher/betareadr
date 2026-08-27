# BetaReadr

BetaReadr is a platform for connecting authors with beta readers.

This repository currently contains the application foundation only. The agreed domain language is documented in [CONTEXT.md](CONTEXT.md), with durable technical decisions in [docs/adr](docs/adr).

## Stack

- PHP 8.3 and Laravel 13
- Vue 3 with the Composition API
- Inertia 3
- TypeScript
- Tailwind CSS 4 and shadcn-vue
- PostgreSQL
- Vite

## Local setup

### Docker (recommended)

Docker with Compose is the only prerequisite. Start Laravel, Vite, the queue worker, logs, and PostgreSQL with one command:

```bash
docker compose up --build
```

The application is available at <http://localhost:8000>. PostgreSQL is exposed to host tools on port `5433` by default, avoiding conflicts with a locally installed PostgreSQL server.

Stop the application with `Ctrl+C`, or run `docker compose down` from another terminal. Database data is retained in the `betareadr-postgres` Docker volume. To also remove that development data, run `docker compose down --volumes`.

Run project commands inside the application container, for example:

```bash
docker compose exec app php artisan test
docker compose exec app npm run types:check
```

### Native environment

The native setup requires PHP 8.3 or newer with the `mbstring` and PostgreSQL extensions, Composer, Node.js with npm, and PostgreSQL.

Install dependencies and create the local environment file:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Create a PostgreSQL database named `betareadr`, then adjust the `DB_*` values in `.env` for your local PostgreSQL credentials and run:

```bash
php artisan migrate
npm run build
```

Start Laravel, the queue worker, application logs, and the Vite development server together:

```bash
composer run dev
```

The application is available at <http://localhost:8000>.

## Quality checks

Run the complete backend test and analysis suite:

```bash
composer test
```

Run frontend checks individually:

```bash
npm run lint:check
npm run format:check
npm run types:check
npm run build
```

Formatting fixes are available through `composer lint`, `npm run lint`, and `npm run format`.
