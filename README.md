# ProgettoStudio

A personal portfolio project by [Francesco Bignardi](https://github.com/francescobignardi) — built in the open, one small step at a time.

## What this is

A **portfolio site** that hosts several small technical sections, each demonstrating a different piece of what I can build. It's meant to be a place a recruiter — or anyone curious — can land, get a sense of who I am, and click into concrete things I've made.

The project is **actively under construction**. What you see today is the very beginning.

## Why I'm building it

I've been writing software professionally for about two years. My day job leans heavily on AI-assisted coding, and I want a place to slow down, write things by hand, and deepen my grip on the languages I use every day — mainly **PHP** and **JavaScript** with modern frameworks.

Every commit here comes from a short, focused session. Progress is deliberately slow.

## Stack

- **Backend**: Laravel (PHP 8.x)
- **Frontend**: Next.js + React + TypeScript
- **Styling**: Tailwind CSS + shadcn/ui
- **Database**: MySQL
- **Dev environment**: Docker Compose

Bootstrap is in progress: PHP fundamentals, Composer, and Docker Compose are already in place. Laravel and the frontend come next.

## Repository layout

```
ProgettoStudio/
├── src/                     # PHP classes (namespace App\, PSR-4 autoload)
├── run.php                  # Entry point for current PHP exercises
├── db-check.php             # PDO connection check against the MySQL container
├── composer.json            # PSR-4 autoload + platform requirements
├── docker-compose.yml       # Local dev environment (PHP 8.3 + MySQL 8.0)
├── Dockerfile               # Custom PHP image (adds pdo_mysql on top of php:8.3-cli)
├── resources/
│   ├── PROGETTO.md          # Project charter — purpose, decisions, next steps
│   ├── appunti.md           # Personal study notes (in Italian)
│   ├── docs/                # Standards, architecture notes, ADRs
│   └── memory/              # Working diaries (yes, they're versioned on purpose)
├── backend/                 # Laravel (routes, controllers, Eloquent models, migrations, seeders, Blade views)
└── frontend/                # Next.js — coming
```

The exercises directly under root (`src/`, `run.php`) are the early PHP-fluency playground, kept as a record. Application code now lives under `backend/`, where Laravel runs.

## Where to look

- **`resources/PROGETTO.md`** — the living charter of the project. Read this first if you want the full story.
- **`resources/docs/DOCUMENTATION.md`** — how documentation is organized in this repo.
- **`resources/docs/standards/STYLE-GUIDE.md`** — the coding principles applied throughout.
- **`resources/memory/`** — daily development diaries. A running record of what was built, when, and why.

## Status

Learning roadmap — PHP-first, JS/TS deferred until the frontend is needed:

- [x] **Phase 1** — PHP fluency (OOP, type hints, collaborating classes)
- [x] **Phase 2** — Composer + namespaces + PSR-4 autoload
- [x] **Phase 3** — Docker Compose (PHP + MySQL local environment)
- [ ] **Phase 4** — Laravel
- [ ] **Phase 5** — JavaScript / TypeScript / React

Latest work: 2026-07-22 — First Eloquent slice, end to end. A `products` table (migration), a `Product` model, and a seeder now feed a `ProductController` that reads from MySQL and renders the rows in a Blade view. The MVC triangle is closed with real data: **DB → Model → Controller → Route → View → browser**. The controller then grew a filtered list (`where('stock', '>', 0)`) and a single-product detail page (`show` via a `/products/{id}` route, `findOrFail` → a clean 404 when the id doesn't exist).
