---
description: Chiude l'argomento corrente — diary (claude + personale), roadmap, README, appunti/PROGETTO se serve, e propone il messaggio di commit
---

Stai per chiudere l'argomento corrente. È il rituale di fine sessione completo: registra la chiusura,
allinea la documentazione, e proponi (NON eseguire) il commit. Esegui **in ordine**.

## Passo 0 — Preliminari

Recupera la data con `date +%Y-%m-%d`, l'ora con `date +%H:%M`, e il nome dell'umano dalla variabile
d'ambiente `$PROJECT_DEV` (configurata in `.claude/settings.local.json`).
- Se `$PROJECT_DEV` NON è impostata: errore — chiedi al programmatore di configurarla.
- File diary claude: `resources/memory/<data-iso>.claude+${PROJECT_DEV}.diary.md`
- File diary personale: `resources/memory/<data-iso>.${PROJECT_DEV}.diary.md`
- Se il file diary claude NON esiste: errore — nessun capitolo è mai stato aperto. Avvisa e fermati.
- Se la conversazione non ha argomenti sostanziali da chiudere (appena iniziata, nulla di concreto):
  avvisa che non c'è niente da chiudere e fermati.

Trova nel file claude **l'ultimo capitolo `## HH:MM — <Titolo>`**: è la sessione che stai chiudendo.

---

## Passo 1 — Diary claude (SEMPRE)

Aggiungi **alla fine di quel capitolo** (non modificare i capitoli precedenti):

```
### Chiusura argomento — HH:MM

- **Cosa è stato deciso**: [2-4 righe delle decisioni cristallizzate]
- **Cosa è stato fatto**: [2-4 righe degli interventi concreti]
- **Aperto / sospeso**: [questioni rimaste in sospeso, o "nessuna"]
- **Prossimo passo concordato**: [se già concordato, o "nessuno"]
```

## Passo 2 — Diary personale (SEMPRE)

Aggiorna `<data>.<PROJECT_DEV>.diary.md`:
- Se NON esiste: crealo con intestazione `# Diario <PROJECT_DEV> — <data-iso>`.
- Aggiungi in fondo una sezione col **titolo identico** all'ultimo capitolo del diary claude:

```
## HH:MM — <stesso titolo del capitolo claude>

> Bozza creata da claude. Da rivedere/integrare.

- [sintesi 2-4 bullet MAX, una riga ciascuno, solo fatti concreti: creato/deciso/risolto]
```

Sintesi **molto corta**, senza dettagli tecnici profondi — è una traccia che il programmatore integrerà.

## Passo 3 — Roadmap (SEMPRE, se la sessione ha prodotto avanzamento)

Aggiorna `resources/docs/architecture/ROADMAP-GESTIONALE.md`:
- Porta il passo lavorato da `[ ]`/`[~]` a `[x]` (o `[~]` se solo iniziato) e sintetizza cosa è stato fatto.
- Aggiorna il **conteggio** del capitolo `[fatti / mappati]`.
- Aggiungi una riga al **Log sintetico di avanzamento** (in cima): `- **<data>** — <cosa si è chiuso>`.
- Se la sessione NON ha toccato la roadmap (es. solo tooling/infra): dichiaralo e salta.

## Passo 4 — README (SEMPRE, se la sessione ha prodotto lavoro applicativo)

Aggiorna la sezione **"Status"** di `README.md`:
- La voce corrente `Latest work:` scala a `Previously (<sua data>)`.
- Scrivi la nuova `Latest work: <data> — …` nello **stesso registro** delle altre (inglese, prosa densa,
  il *perché* esplicito, non solo il cosa).
- Se la sessione non ha lavoro applicativo da raccontare (solo doc/tooling): dichiaralo e salta.

## Passo 5 — Appunti (CONDIZIONALE)

`resources/appunti.md` è il quaderno didattico di Francesco. Aggiornalo **solo se** la sessione ha
introdotto **concetti nuovi** (grammatica/pattern non ancora annotati).
- Se sì: proponi un'aggiunta **chirurgica**, nello stile esistente (bullet densi, `⚠️` trappole,
  `🎯` criteri, il perché esplicito), incastrata nella sezione giusta; aggiorna l'indice se serve.
  Niente enciclopedia: solo ciò che è nuovo e non ovvio.
- Se no: **dichiaralo esplicitamente** ("Appunti: niente di nuovo, salto") — non inventare aggiunte.

## Passo 6 — PROGETTO.md (CONDIZIONALE)

`resources/PROGETTO.md` è il charter (scopo, decisioni, next-step di alto livello). Aggiornalo **solo se**
la sessione ha cambiato qualcosa a quel livello (una decisione strutturale, un next-step, una direttiva).
- Se sì: aggiorna il punto pertinente, dichiarando cosa e perché.
- Se no: **dichiaralo** ("PROGETTO.md: nessuna decisione di alto livello cambiata, salto").

## Passo 7 — Messaggio di commit (PROPONI, non eseguire)

Proponi un messaggio di commit — **non lanciare `git commit`**: lo fa il programmatore (regola cardine —
Claude non agisce sul repo da solo).
- Convenzione: **Conventional Commits**, con prefisso custom **`learn:`** per gli esercizi didattici.
- Corpo: cosa è stato fatto e il *perché* delle scelte non ovvie; menziona i doc aggiornati.
- Chiudi con la riga `Co-Authored-By:` prevista dal progetto.
- Puoi mostrare prima un `git status`/`git diff --stat` per far vedere cosa entrerà nel commit.

---

## Passo 8 — Riepilogo finale

Mostra al programmatore:
- I blocchi diary scritti (chiusura claude + bozza personale).
- L'elenco dei documenti aggiornati e di quelli **saltati con motivo** (roadmap/README/appunti/PROGETTO).
- Il messaggio di commit proposto.

Suggerisci di: rivedere/integrare la bozza personale, lanciare lui il `git commit`, poi `/clear` prima
del prossimo argomento.

## Vincoli

- NON inventare contenuti: usa solo ciò che è realmente accaduto in conversazione.
- NON modificare i capitoli/le voci precedenti dei diary: solo aggiunte.
- NON eseguire il commit né altri comandi git che scrivono: proponi soltanto.
- Sui passi condizionali: **dichiara sempre** la decisione (fatto / saltato + perché). Mai saltare in silenzio.
