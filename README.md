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

Latest work: 2026-07-24 — CRUD, the **U** (Update) and **D** (Delete) — the cycle is now complete. Editing serves a pre-filled Blade form (`edit`) that submits to an `update` method; deleting is an "Elimina" button per row. Both rely on **method spoofing**: an HTML form can only speak GET and POST, so `@method('PUT')` / `@method('DELETE')` add a hidden `_method` field that Laravel reads to rewrite the verb. The routes follow REST — same `/products/{id}` URL, the verb decides the action (GET→show, PUT→update, DELETE→destroy). `update` uses `findOrFail` + an **instance** `->update()` (not the static `::`), keeping the 404 guard consistent with `edit`. Full CRUD in place: `index`/`create`/`store`/`show`/`edit`/`update`/`destroy`.

Previously (2026-07-23) — CRUD, the **C** (Create). Products can now be created through the app, not just seeded: a `create` page serves a Blade form (`@csrf` + a submit button) that POSTs to a `store` method, which validates the input against the domain (`required|numeric|min:0`, …) and writes a new row via `Product::create`. It follows the **POST-Redirect-GET** pattern — after saving, it redirects to `GET /products` so a refresh never double-submits. The listing gained an explicit `orderBy` (no more relying on the DB's incidental row order) and an `<a>` link to the create form.

Previously (2026-07-22): first Eloquent slice end to end — `products` migration, `Product` model, seeder → `ProductController` reading from MySQL into a Blade view (MVC triangle closed with real data: **DB → Model → Controller → Route → View → browser**), plus a filtered list (`where('stock', '>', 0)`) and a `show` detail page (`/products/{id}`, `findOrFail` → clean 404).
