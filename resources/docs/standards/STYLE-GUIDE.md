# Style Guide

> Stack-agnostic coding principles for this project.
> Language- and framework-specific rules will extend this file (see `CODING-STANDARDS-PHP.md` and `CODING-STANDARDS-JS.md` — added once the stack is chosen).
>
> Written 2026-07-01. Living document.

## Why this exists

This is a learning project. The point is to internalize the *grammar* of the languages and frameworks we choose — not to reproduce any particular corporate style. The rules below are the intersection of:

- What experienced OOP practice teaches (Elegant Objects, clean code, etc.)
- What modern PHP and JS frameworks actually use idiomatically
- What survives contact with a solo developer working an hour a day

When a rule would fight the framework's idioms, the framework wins. Follow the spirit, not the letter.

---

## 1. Naming

- **Names describe what a thing *is*, not what it *does to itself*.** A method that returns the user's email is `email()`, not `getEmail()`. A property is `$email`, not `$userEmail` if it already lives on a `User`.
- **Verbs for commands, nouns for queries** (CQRS). `sendInvitation()` changes something; `invitations()` reads.
- **Singulars over prefixes.** If the context is a `User`, its property is `$email`, not `$userEmail`. Redundant prefixes signal missing structure.
- **No `-er` / `-Manager` / `-Helper` classes** unless truly unavoidable. `Validation`, not `Validator`; `Authentication`, not `AuthManager`. A class named after an *action* usually should be a method somewhere else.
- **Boolean names are questions.** `$isActive`, `$hasAccess`, `canEdit()`.

## 2. Constructors and validation

- **Fail fast.** If a value is invalid, throw in the constructor. No silent defaults, no "we'll check later".
- **Constructors do three things only**: assign, validate the *shape* of arguments, coerce to the project's types. No I/O, no business logic, no side effects.
- **If it can't be constructed, it doesn't exist.** No half-built objects with `init()` methods.

## 3. Immutability by default

- **Prefer immutable objects.** Once constructed, state doesn't change. When you need a modified copy, return a new instance (`withEmail($new)` style).
- **Mutability is a choice you justify**, not a default. Framework entities (Doctrine, Eloquent, React state) will need mutation — that's fine, it's their contract. Your own domain objects shouldn't.

## 4. Getters, setters, and object shape

- **Avoid getters and setters as a default.** They leak state and turn objects into data bags. Prefer methods that *do something* with the state.
- **Framework exceptions.** Doctrine entities, Eloquent models, DTOs, form objects — these are boundary objects where getters/setters are idiomatic. Don't fight the framework.
- **Small classes.** If a class has more than a handful of properties, it's probably two classes. Not a hard number — a smell.

## 5. Inheritance and interfaces

- **Concrete classes are `final` by default.** If you want it extended, make it `abstract` and prefix it (`BaseController`, `AbstractUser`). Extending a concrete class is almost always a mistake.
- **Interfaces name capabilities, not implementations.** `Countable`, `Renderable`, `Storable`. Not `IUserService`, not `UserInterface`.
- **One class implements one interface** as the primary contract. Multiple capability-interfaces are fine.

## 6. Nulls, absence, and errors

- **Distinguish "not there" from "there but empty".** `null` returned from a lookup is a real answer. Prefer explicit types: `?User` for "maybe", `Collection` for "possibly empty".
- **Null Object pattern where it fits.** For values that flow through many methods (`Logger`, `User`, `Config`), an `EmptyLogger` that does nothing is safer than `null`.
- **Exceptions are for exceptional situations**, not control flow. `throw` when a caller broke a contract; return a value when a caller asked a valid question with a negative answer.

## 7. Comments

- **Default: no comments.**
- Names, small methods, and clear structure carry the meaning. If you need a comment to explain *what* code does, rewrite the code.
- **Exceptions.** A comment is fine when it explains *why*, not *what*: a non-obvious constraint, a workaround for a specific bug, a subtle invariant that would surprise a reader.
- **Never write comments that summarize commit history** ("added for issue #42", "used by X"). Those belong in commits and PR descriptions; they rot fast in code.

## 8. Types

- **Use the type system as much as the language allows.** PHP: `declare(strict_types=1)`, typed properties, typed returns. TypeScript over plain JS if the framework supports it well.
- **Types at boundaries, minimum inside.** Public methods should have declared types; internal helpers can be looser if it makes the code cleaner.

## 9. Reflection and introspection

- **Avoid reflection**, `instanceof` chains, and dynamic property access as a design tool.
- These are fine in framework internals (they *need* to introspect). In application code, they're almost always a symptom of missing structure.

## 10. Framework-first, not framework-fighting

- **Learn the framework's idioms before improving on them.** If Laravel says use Eloquent, use Eloquent. If React says use hooks, use hooks. The point of picking a modern framework is to inherit its wisdom — not to smuggle in a different architecture.
- **The rules above apply within what the framework leaves to you.** They shape *your* domain code, not the framework's shape.

## 11. Files and modules

- **One primary export per file.** Class per file in PHP (already enforced by autoloading). Component or module per file in JS.
- **Files short enough to read in one screen.** Rough target: 100–200 lines. Not a rule, a smell threshold.

## 12. Dependencies

- **Dependencies flow from the specific to the general**, never the other way. Your `Order` doesn't know about your HTTP controller; your HTTP controller knows about `Order`.
- **No circular dependencies.** If A depends on B and B depends on A, one of them is misnamed.
- Formalize this later (`PACKAGE-DEPENDENCY-RULES.md`) if the project grows enough modules to need it. For now: notice it in review.

---

## Grey areas — decide when we get there

These are open questions that will become rules once we've hit them a few times:

- **Testing style** — Test file layout, mocking, integration vs unit boundary. Wait until we have real code to test.
- **Error handling in the framework** — Every framework has its own exception/response conventions. Adopt them, don't invent.
- **Formatting and linting** — Delegate to tools (PHP-CS-Fixer / Pint for PHP, Prettier + ESLint for JS). No manual style debates.
- **Frontend state** — Rules here depend heavily on framework choice (React vs Vue vs Svelte have very different idioms).

---

## Origin note

The backbone of this guide (fail-fast, immutability, CQRS naming, no-getters, one-interface-per-class, no reflection) is adapted from an internal Ansoft standard authored by Francesco's tech lead — a rigorous Elegant Objects treatment for PHP. That standard is deliberately more strict than this one. The choice here is *moderate rigor*: keep the ideas that make code better in any language and any framework, drop the dogmas that would fight modern PHP/JS framework idioms.

Nothing in this file is copied verbatim from that source.
