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

Latest work: 2026-08-06 — a **realistic seed built on factories**, which turns the relationship from a written method into a *used* one. Two model factories were introduced (`SupplierFactory`, `PurchaseOrderFactory`), each declaring the *shape* of one record with **Faker** (`fake()->name()`, `dateTimeBetween('-1 year', 'now')->format('Y-m-d')`, `randomElement([...])` for a domain-appropriate `status`) — replacing the hand-written `create([...])`-per-row seeders that don't scale. The bridge that lets a model say `::factory()` is the `HasFactory` trait, added to both models. The payoff line reads like a sentence: `Supplier::factory()->count(10)->has(PurchaseOrder::factory()->count(5))->create()` — ten suppliers, each with five orders, `supplier_id` wired by Laravel through the `hasMany` relation (no more `inRandomOrder()`). Where that line lives followed **existential dependency**: an order can't exist without a supplier, so the supplier is the root and the unified creation belongs in `SupplierSeeder` — `PurchaseOrderSeeder` was deleted rather than left as an empty ghost. Verified against the DB: 10 suppliers, 50 orders, five per supplier. A Tinker lesson worth keeping: `make()` is "no save" only for the *top* entity — a nested `Supplier::factory()` inside a `make()`d order still persists, because a foreign key must point at something real.

Previously (2026-08-04) — the **first real Eloquent relationship**. A `Supplier` now *has many* `PurchaseOrder`s, and a `PurchaseOrder` *belongs to* a `Supplier` — the jump from single tables to a relational model. The `purchase_orders` migration carries the foreign key the idiomatic way, `foreignId('supplier_id')->constrained()->restrictOnDelete()` (the `restrict` protects a purchase order's history — an order is an accounting document, not something to cascade-delete with its supplier). The relationship lives in two `return`s: `hasMany` on `Supplier`, `belongsTo` on `PurchaseOrder` — verified live in **Tinker** (`$order->supplier` hands back the whole Supplier object, no query written by hand). A recurring principle got sharpened: fields that are *system rules*, not user input — `status` (defaults to `draft`), `order_date` (`now()`) — are set by the system, never accepted from the form (also a guard against mass-assignment). The order header was kept deliberately minimal (supplier, number, date, status, notes); the ordered products aren't header fields — they'll be the document's **rows**, a separate table coming next. The create form introduced the `<select>` grammar: `<option value="{id}">{name}</option>` — the *value* travels to the server (the id, for the machine), the text is what the human reads (the name).

Previously (2026-07-28) — the management app begins for real. Introduced a second entity, **`Supplier`** (migration + model + minimal CRUD), built by analogy with `Product` and wired with the idiomatic `Route::resource(...)->only([...])` instead of hand-written routes. Alongside it, two housekeeping moves that pay off as the app grows: Blade views were reorganized into **per-entity folders** (`resources/views/products/`, `.../suppliers/`) with resource-style names (`index`/`show`/`create`/`edit`), and the whole UI + seed data were moved to **English** for a portfolio that reads to anyone. A subtle bug surfaced and got fixed along the way — `view()` wants a *view name* (dot notation, `products.index`) while `Redirect::to()`/`href`/`action` want a *URL* (`/products`); accidentally dotting a redirect URL produced a 404. Groundwork for the first real Eloquent relationship (`Supplier` has many `PurchaseOrder`s), coming next.

Previously (2026-07-24) — CRUD, the **U** (Update) and **D** (Delete) — the cycle is now complete. Editing serves a pre-filled Blade form (`edit`) that submits to an `update` method; deleting is a per-row button. Both rely on **method spoofing**: an HTML form can only speak GET and POST, so `@method('PUT')` / `@method('DELETE')` add a hidden `_method` field that Laravel reads to rewrite the verb. The routes follow REST — same `/products/{id}` URL, the verb decides the action (GET→show, PUT→update, DELETE→destroy). `update` uses `findOrFail` + an **instance** `->update()` (not the static `::`), keeping the 404 guard consistent with `edit`. Full CRUD in place: `index`/`create`/`store`/`show`/`edit`/`update`/`destroy`.

Previously (2026-07-23) — CRUD, the **C** (Create). Products can now be created through the app, not just seeded: a `create` page serves a Blade form (`@csrf` + a submit button) that POSTs to a `store` method, which validates the input against the domain (`required|numeric|min:0`, …) and writes a new row via `Product::create`. It follows the **POST-Redirect-GET** pattern — after saving, it redirects to `GET /products` so a refresh never double-submits. The listing gained an explicit `orderBy` (no more relying on the DB's incidental row order) and an `<a>` link to the create form.

Previously (2026-07-22): first Eloquent slice end to end — `products` migration, `Product` model, seeder → `ProductController` reading from MySQL into a Blade view (MVC triangle closed with real data: **DB → Model → Controller → Route → View → browser**), plus a filtered list (`where('stock', '>', 0)`) and a `show` detail page (`/products/{id}`, `findOrFail` → clean 404).
