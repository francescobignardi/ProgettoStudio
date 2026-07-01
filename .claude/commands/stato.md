---
description: Analisi dello stato di progetto con dettagli su milestone, completamento e blocchi
model: sonnet
arguments:
  - name: target
    description: "Cosa analizzare: ometti per panoramica, usa 'milestone N' o 'M2' per una milestone, 'blocchi' per item bloccati, 'prossimi' per next steps"
    required: false
    default: "panoramica"
---

# Procedura: Analisi Stato Progetto

## 0. Contesto architetturale

Carica il contesto dalla documentazione (convenzione in `resources/docs/standards/DOCUMENTATION.md`):

- `resources/docs/overview.md` (L0) — identità del progetto e **tabella componenti** (nomi, ruolo, stato di massima).
- `resources/docs/analysis/<componente>/architecture.md` (L1) — confini e punti di design aperti, per ciascun componente elencato in overview.

Usa questi elementi per nominare correttamente componenti, dipendenze e milestone nel report.

## 1. Stato da GitLab

Lo stato vivo (milestone in corso, issue aperti/chiusi, blocchi) vive su **GitLab**, non in repo (vedi `resources/docs/standards/GITLAB-WORKFLOW.md`). Leggilo in **sola lettura** con la stessa infrastruttura della skill `/issue`:

```bash
cd "$(git rev-parse --show-toplevel)"
CFG=".claude/llm-gitlab.local.json"
TOKEN=$(python3 -c "import json;print(json.load(open('$CFG'))['credentials']['personal_access_token'])")
URL=$(python3 -c "import json;print(json.load(open('.claude/llm-gitlab.json'))['gitlab']['instance_url'])")
GROUP_ENC=$(python3 -c "import json;print(json.load(open('.claude/llm-gitlab.json'))['gitlab']['group_enc'])")
HDR="PRIVATE-TOKEN: $TOKEN"
# milestone di gruppo
curl -s --max-time 25 -H "$HDR" "$URL/api/v4/groups/$GROUP_ENC/milestones?per_page=100"
# issue di gruppo (per milestone e stato)
curl -s --max-time 25 -H "$HDR" "$URL/api/v4/groups/$GROUP_ENC/issues?per_page=100&scope=all"
```

Se `.claude/llm-gitlab.local.json` non è configurato: usa come fallback lo snapshot manuale `resources/docs/analysis/<componente>/milestones/current.md` se presente, altrimenti segnala che lo stato non è disponibile (vedi `DOCUMENTATION.md` §9.3).

Da questi dati estrai, per ogni milestone: issue totali, chiusi, aperti, bloccati (label `vincolo::bloccante` o link `relates_to` a issue aperti). Identifica gli issue con `<modulo>#<numero>`.

## 2. Parsing parametro `target`

- `milestone N` / `M2`: mostra SOLO quella milestone, con % completamento.
- `blocchi`: filtra gli item bloccati, elenca con motivo.
- `prossimi`: elenca i prossimi 3 step.
- omesso / `panoramica`: panoramica completa di tutte le milestone.

## 3. Calcolo completamento

Per ogni milestone: `Completamento = issue_chiusi / issue_totali * 100`.

## 4. Report strutturato

### `/stato` (panoramica)
```
Sprint 1 — <titolo>: X/Y (XX%)
Sprint 2 — <titolo>: X/Y (XX%)
...
```

### `/stato sprint-4`
```
Completamento: X/Y (XX%)
✅ Chiusi · ❌ Aperti · 🔴 Bloccati   (ciascuno come <modulo>#<numero>)
Prossimi step per chiudere la milestone
```

### `/stato blocchi` / `/stato prossimi`
Versione filtrata.

## 5. Nota

La skill è **sola lettura**: non modifica issue/milestone su GitLab né scrive file. Per cambiare lo stato si agisce su GitLab.
