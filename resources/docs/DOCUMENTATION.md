# Documentation Guide

> How documentation is organized in this repository, and where things go.
> Written 2026-07-01. Living document — update it when the structure changes.

## Golden rule

**If you enter this repo — as a human or as an LLM — you should be able to figure out where to go for your question without asking anyone.**

That's the entire point of this document. If you can't find something, that's a bug in the structure, not in you.

## Where things live

```
resources/
├── PROGETTO.md              # Project charter: purpose, constraints, decisions still open
├── docs/
│   ├── DOCUMENTATION.md     # (this file) map of the map
│   ├── overview.md          # L0: what this project is, in one page
│   ├── DEV-ENVIRONMENT.md   # How to run everything locally
│   ├── standards/           # How we write things (code, tests, docs)
│   ├── decisions/           # ADRs — architectural decisions with trade-offs
│   ├── architecture/        # How the system is built (diagrams, flows, contracts)
│   └── detail/              # Component-level manuals (only when the project grows)
└── memory/                  # Daily development diaries (see DIARIES section below)
```

Not every folder exists on day one. Create a folder the first time you need it, not before.

## Three levels of documentation

Adapted from a larger project — kept small on purpose. Layers, not silos: a reader entering at L0 should be able to walk down.

- **L0 — Orientation.** `README.md`, `PROGETTO.md`, `docs/overview.md`. One page each. Answers: *what is this? where do I start?*
- **L1 — Rules and shape.** `docs/standards/`, `docs/architecture/`. Answers: *how do we write code here? how is the system laid out?*
- **L2 — Component detail.** `docs/detail/context/<component>.md`. Answers: *how does this specific piece work? what are the gotchas?* Only created when the project has real components worth documenting on their own.

Rule: **information lives in one place**. If you need to reference it elsewhere, link to it. Don't duplicate — duplicates rot.

## Cross-cutting categories

These aren't levels, they cut across everything:

- **`standards/`** — Non-negotiable rules (coding style, testing approach, dependency rules). Change only with intent and discussion.
- **`decisions/`** — Architectural Decision Records (ADRs). See below.
- **`architecture/`** — Current shape of the system. Living documents, rewritten as the system changes.

## ADR — Architectural Decision Records

An ADR captures a decision that has trade-offs and can't be inferred from the code. Filename: `NNNN-kebab-case.md`, numbered sequentially, never reused.

**Rules:**
- **Append-only.** ADRs are never modified after being accepted. If a decision is superseded, write a new ADR that references the old one (`Supersedes: ADR-0003`).
- **Title is a statement, not a question.** `ADR-0002: Use Laravel for the backend`, not `ADR-0002: Which PHP framework?`.
- **Structure**: Status · Date · Context · Decision · Consequences (positive / negative / open) · References.
- **Context matters more than the decision itself.** In six months, you'll remember *what* you decided; you won't remember *why*.

Small template in `resources/docs/decisions/TEMPLATE.md` (add it when you write the first ADR).

## Naming conventions

- **Standards and manifestos**: `ALL-CAPS.md` (`README.md`, `DOCUMENTATION.md`, `STYLE-GUIDE.md`).
- **Regular docs**: `kebab-case.md`.
- **ADRs**: `NNNN-kebab-case.md` (`0001-project-charter.md`, `0002-backend-stack.md`).
- **Diaries**: see below.
- **Dated notes** (analysis, meeting notes, one-off writeups): prefix with ISO date — `YYYY-MM-DD-topic.md`.

## Diaries — the memory folder

`resources/memory/` holds one file per working day. Two variants:

- **`YYYY-MM-DD.francesco.diary.md`** — Francesco's personal diary. Rough notes, learning log, questions.
- **`YYYY-MM-DD.claude+francesco.diary.md`** — Claude's diary of what happened in the AI-assisted sessions of that day. Written by Claude *during* the work, not after.

Inside each daily file, work is split into chapters:

```
## HH:MM — Topic title

**Intent (before)**
What I'm about to do and why.

**Outcome (after)**
What actually happened.
```

**Diaries are chronological, not thematic.** They're a log, not documentation. When something learned in a diary matters long-term, it gets promoted to a standard, an ADR, or an architecture doc.

Closing a topic: use `/chiudi` (defined in `.claude/commands/`). It writes the closing block in Claude's diary and generates a short draft in Francesco's personal diary that Francesco can integrate later.

## When to write what

| Situation | Where it goes |
|---|---|
| "How should we write X in this project?" | `standards/` |
| "We chose A over B because…" | `decisions/` (ADR) |
| "Here's how the system fits together" | `architecture/` |
| "Here's how *this component* works internally" | `detail/context/<component>.md` |
| "Here's what I did today / what I learned" | `memory/` (diary) |
| "Here's the plan for the next thing" | Not here — use conversation, tasks, or an ADR if it's a real decision |

If you're not sure, err toward the diary. Promotion is easier than demotion.

## Documents rot — that's ok

Documentation drifts from code. Fight it, but accept it. When you notice a doc lying: fix it in the same PR as the code change, or add a note at the top (`> Stale as of 2026-XX-XX — describes old flow`) and open a ticket to rewrite.

Docs that go stale silently are worse than docs that admit they're stale.
