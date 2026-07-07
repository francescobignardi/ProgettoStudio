# ProgettoStudio — Direttive base

> Documento vivo. Punto di partenza del progetto, scritto con Claude il 2026-07-01.
> Man mano che prendiamo decisioni, aggiorniamo le sezioni "DA DECIDERE".

---

## 1. Perché esiste questo progetto

**Scopo primario: istruire Francesco.**

Francesco è uno sviluppatore da 2 anni. Il lavoro quotidiano è ormai molto assistito da Claude Code, e questo lascia buchi nella conoscenza "grammaticale" dei linguaggi — in particolare **PHP** e **JavaScript**. Questo progetto serve a colmare quei buchi scrivendo codice a mano, capendolo, e costruendo qualcosa di reale.

**Non è un progetto di lavoro.** Non appartiene ad An-Soft, non va sul GitLab aziendale. È un progetto personale, destinato al **GitHub personale di Francesco**, e va trattato come vetrina/portfolio: quindi deve essere **fatto bene** (codice pulito, documentato, testato quando ha senso) e possibilmente **bello da vedere**.

## 2. Vincoli e modalità di lavoro

- **Tempo disponibile**: poco. Francesco scriverà "un pochino ogni giorno". Il progetto avanza a piccoli passi, non a sprint.
- **Assistenza**: Claude aiuta, ma l'obiettivo è che Francesco scriva/comprenda personalmente. Claude:
  - spiega la grammatica di ciò che scriviamo, non solo il "cosa";
  - propone soluzioni, ma lascia che sia Francesco a battere le linee di codice quando ha valore didattico;
  - segnala quando sta facendo troppo lavoro al posto suo.
- **Complessità**: deve essere alta. Un CRUD banale non serve a nessuno. Il progetto deve toccare più aree (backend, frontend, dati, magari real-time, magari qualcosa in più) così da attraversare la grammatica dei due linguaggi in profondità.

## 3. Target del progetto — DECISO (2026-07-01)

**Il progetto è un sito portfolio personale, versatile ed eterogeneo.** Non è un singolo prodotto: è un contenitore che ospita **più sotto-sezioni tecniche diverse**, ognuna a dimostrare competenze e pezzi di stack differenti. Il recruiter arriva al portfolio, capisce chi è Francesco, e può *entrare* nelle varie sezioni per vedere cose concrete che sa fare.

**Struttura di massima:**

- **Sezione "chi sono" / vetrina** — presentazione, tecnologie, contatti, indice delle sezioni tecniche. È la prima cosa che il recruiter vede, deve essere curata graficamente.
- **Sezioni tecniche** — ognuna un mini-progetto interno che dimostra una capacità. Si aggiungono nel tempo, non tutte insieme.
  - **Prima sezione (già decisa come punto di partenza)**: **gestionale ordini** con magazzino, prodotti, utenti a ruoli, ecc. È solo un punto di partenza, i dettagli si decideranno quando ci arriveremo.
  - **Sezioni successive**: da decidere strada facendo — potranno coprire domini o tecniche diverse (es. qualcosa di visuale, un tool di analisi dati, un'integrazione con API esterna, ecc.).

**Perché questa impostazione**:
- Un portfolio eterogeneo è più forte di un singolo progetto: mostra ampiezza, non solo un caso specifico.
- L'incremento è più naturale: chiusa una sezione, se ne apre un'altra, senza dover "aggiungere feature" a un prodotto immaginario.
- Massimizza le possibilità di far vedere al recruiter una cosa che lo interessa: se non gli parla il gestionale, magari gli parla la sezione dopo.

## 4. Stack tecnologico — DECISO (2026-07-01)

**Backend**: **Laravel** (PHP moderno, ~12.x al 2026). Motivi: massima richiesta sul mercato, ecosistema ricchissimo, deploy semplice, adatto a un progetto vetrina. Francesco arriva da una scuola An-Soft molto rigorosa (Elegant Objects, Metis): Laravel è più permissivo — bene, impara a scriverlo *bene* e si differenzia. Convenzione: seguire l'idiomatica Laravel (Eloquent, Blade dove serve, service container) senza forzare pattern EO stretti dove il framework spinge in altre direzioni.

**Frontend**: **React + Next.js in TypeScript**. Motivi: standard di mercato, Next.js risolve routing/SSR/build, TypeScript è ormai dato per scontato negli annunci senior. UI con **Tailwind CSS** + libreria di componenti moderna (probabilmente **shadcn/ui**) per avere un colpo d'occhio serio senza scriverla da zero.

**Database**: **MySQL**.

**Come parlano tra loro**: Laravel espone **API REST** (o eventualmente GraphQL, ma REST è più ambito sul mercato); Next.js consuma le API lato client e/o server. Autenticazione via **Laravel Sanctum** (token-based, adatto a SPA/mobile). Da rifinire quando arriveremo al primo scambio reale.

**Ambiente locale**: **Docker Compose** per PHP, MySQL, eventualmente Redis. Frontend Next.js girerà probabilmente fuori Docker in dev (più veloce), da valutare.

**Test**: **PHPUnit** (o **Pest**, più moderno — da decidere quando scriveremo il primo test) lato Laravel; **Vitest** + **Playwright** lato Next.js. Non da subito: introduciamo test quando c'è codice a cui applicarli.

**Deploy** (quando avremo qualcosa da mostrare): Vercel per il frontend Next.js (gratis, dominio incluso), backend Laravel su Railway/Fly.io/VPS piccolo. Database gestito (Neon/Supabase/Railway).

**Estensioni scelte più avanti**: state management frontend (Zustand? React Query?), gestione form (React Hook Form + Zod?), pagamenti (Stripe test mode?). Verranno decise quando serviranno, non prima.

## 5. Come conserviamo la memoria di sessione

Il progetto eredita da un altro contesto un **sistema diary** che teniamo così com'è:

- Hook `UserPromptSubmit` in `.claude/settings.json` che a ogni prompt segnala il diary di oggi.
- Variabile `PROJECT_DEV=francesco` in `.claude/settings.local.json`.
- Per ogni giornata di lavoro:
  - `resources/memory/YYYY-MM-DD.claude+francesco.diary.md` — diary scritto da Claude, con capitoli `## HH:MM — <titolo>`.
  - `resources/memory/YYYY-MM-DD.francesco.diary.md` — diary personale di Francesco (bozza creata da Claude a fine argomento, integrata poi da Francesco).
- Comando `/chiudi` per chiudere l'argomento corrente e sincronizzare i due diary.

**Regola di sessione**: ogni sessione di lavoro va salvata in `resources/memory/` seguendo il flusso sopra.

## 6. Stato del `.claude` ereditato

Il `.claude/` che si trova ora in questo repo proviene da un altro progetto di lavoro (progetto "Metis" su GitLab aziendale An-Soft). Alcuni pezzi sono generici e li teniamo, altri sono specifici e vanno **buttati o rifatti** quando ne avremo bisogno.

### Da tenere così com'è
- `.claude/settings.json` — hook diary generico.
- `.claude/settings.local.json` — `PROJECT_DEV=francesco` è corretto. Le `permissions` (git, docker, make) restano utili.
- `commands/chiudi.md` — flusso di chiusura argomento, riusabile.
- `commands/ragiona.md` — generico, riusabile.

### Da adattare (piccole modifiche quando serviranno)
- `commands/pianifica.md` — riferimento a "Seed → Language → Kernel → WebApp/Console" (catena Metis) da rimuovere/sostituire con la nostra architettura quando esisterà.
- `commands/procedi.md` e `commands/intervieni.md` — riferimenti a `CODING-STANDARDS-PHP.md` di Metis da sostituire con i **nostri** standard (che scriveremo, semplici).

### Da buttare o riscrivere da zero
- `commands/issue.md` — tutto costruito su GitLab API v4 con "ombrello" e mappa progetti. Qui useremo GitHub Issues (se e quando servirà), quindi va riscritto.
- `commands/stato.md` — idem, dipende da GitLab.
- `commands/contesto.md` — assume una struttura `resources/docs/detail/context/<componente>.md` che qui non esiste.
- `commands/verifica.md` — checklist G.1–G.7 tarata sui coding standards Metis, non applicabile.

**Non facciamo pulizia ora**: rimuoviamo/riscriviamo un pezzo alla volta, quando quel comando servirà davvero. Nel frattempo Francesco sa che non deve invocarli.

## 7. Versionamento e GitHub

- Repo destinato al **GitHub personale di Francesco** (account personale, non An-Soft).
- **Nome del repo: `ProgettoStudio`** (deciso 2026-07-01). Il nome funziona bene come contenitore di portfolio eterogeneo: non lega il repo a un singolo dominio.
- Il repo GitHub lo apre Francesco personalmente (deciso 2026-07-01).
- Convenzione commit: da decidere (probabilmente Conventional Commits, semplice e leggibile su GitHub).
- README: deve essere curato fin da subito, perché è la vetrina del progetto.

## 8. Documenti già in essere

- **`resources/docs/DOCUMENTATION.md`** — mappa della documentazione: dove va cosa, livelli L0/L1/L2, categorie trasversali (standards, decisions, architecture), convenzioni di naming, regole per gli ADR, sistema diary.
- **`resources/docs/standards/STYLE-GUIDE.md`** — manifesto di stile agnostico allo stack: naming, fail-fast, immutabilità, no getter/setter di default, no commenti superflui, framework-first. Rigore **moderato** — pensato per non collidere con l'idiomatica dei framework moderni.

Entrambi i file sono in inglese (il progetto va sul GitHub personale, ha senso essere leggibile a chiunque). Sono **documenti vivi**, si aggiornano quando il progetto cresce.

Il nucleo di idee viene dagli standard interni scritti dal capo di Francesco (progetto An-Soft "staincampo5"). Nulla è copiato verbatim: è una versione adattata al contesto — progetto singolo, didattico, rigore moderato.

## 9. Percorso didattico e prossimi passi

Il 2026-07-07, dopo autovalutazione onesta di Francesco sui due linguaggi, è emerso un quadro molto asimmetrico:

- **PHP**: basi e OOP intorno a "livello 2/3" (visti, scriverei con qualche incertezza). Gap principale: **Composer** (livello 1) e fluenza pratica ancora da consolidare.
- **JavaScript e TypeScript**: praticamente da zero (livello 0-1 su tutto). Async, moduli e TS mai scritti.

L'ipotesi iniziale del "doppio binario parallelo PHP/JS" è stata **rivista**: si parte PHP-first e JS/TS si introducono solo quando servirà il frontend del gestionale. Vantaggio: si consolida un livello che è a portata di mano invece di rincorrere due fronti in parallelo.

### Rotta didattica (deciso 2026-07-07)

**Fase 1 — Consolidamento fluenza PHP** (micro-esercizi giornalieri)
- OOP applicato: più classi che collaborano, non oggetti-isolati (`Product` + `Warehouse` + `Order`, ecc.).
- Array e cicli su collezioni reali di oggetti.
- Type hints, nullable, union — vanno "stampati nelle dita".
- Eccezioni scritte da Francesco, gerarchie custom.

**Fase 2 — Composer + namespace applicati**
- `composer init`, PSR-4 autoload, primo pacchetto esterno installato e usato.
- Riorganizzazione degli esercizi in `src/` con namespace.

**Fase 3 — Docker (ambiente locale pulito)**
- `docker-compose.yml` semplice con PHP + MySQL. Nessun Laravel ancora — Docker come strumento generale, non come "ambiente Laravel".

**Fase 4 — Laravel (ingresso al framework)**
- Solo quando fluenza PHP + Composer + Docker sono in piedi. Altrimenti Laravel diventa magia.

**Fase 5 — JS / TS / React**
- Introdotto quando il gestionale ha bisogno di un frontend. Prima JS puro, poi TypeScript, poi React.

### Passi paralleli / di infrastruttura (indipendenti dalle fasi didattiche)

- **Primo ADR** (`ADR-0001-project-charter.md`) — identità del portfolio, stack, motivazioni. Da scrivere quando c'è un momento buono; non blocca l'avanzamento didattico.
- **Scaffold minimo del monorepo** — arriva in Fase 3/4, non prima.
- **Sezione "chi sono" / vetrina** — la costruiremo quando saremo pronti a mostrarla.
- **Prima sezione tecnica: gestionale ordini** — inizia in Fase 4 (Laravel). Solo a quel punto decidiamo modello dati, ruoli, giacenze.
- **Coding standards specifici** (`CODING-STANDARDS-PHP.md`, `CODING-STANDARDS-JS.md`) — dopo aver toccato codice reale, non prima.

Da qui in poi si procede a piccoli passi giornalieri, sempre con l'obiettivo didattico in testa.