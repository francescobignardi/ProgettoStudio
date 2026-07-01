---
description: Legge issue e milestone del progetto da GitLab via API v4
model: sonnet
---

Interroga GitLab in **sola lettura** per mostrare issue e milestone del progetto corrente.
Argomenti ricevuti: `$ARGUMENTS`

## Configurazione

La configurazione vive in **due file** (vedi `GITLAB-WORKFLOW.md` §11):

- `.claude/llm-gitlab.json` — committato, contiene `instance_url`, `group_enc`, mappa progetti
- `.claude/llm-gitlab.local.json` — gitignored, contiene solo il PAT

Estrai sempre così, **senza mai stampare il token**:

```bash
cd "$(git rev-parse --show-toplevel)"
CFG_PROJ=".claude/llm-gitlab.json"
CFG_LOCAL=".claude/llm-gitlab.local.json"
TOKEN=$(python3 -c "import json;print(json.load(open('$CFG_LOCAL'))['credentials']['personal_access_token'])")
URL=$(python3 -c "import json;print(json.load(open('$CFG_PROJ'))['gitlab']['instance_url'])")
GROUP_ENC=$(python3 -c "import json;print(json.load(open('$CFG_PROJ'))['gitlab']['group_enc'])")
HDR="PRIVATE-TOKEN: $TOKEN"
```

Se `.claude/llm-gitlab.local.json` non esiste o contiene ancora `<INSERIRE-IL-PROPRIO-PAT>`:
fermati e indica al programmatore di compilarlo copiando `resources/templates/llm-gitlab.template.json`
(procedura in `resources/docs/standards/DOCUMENTATION.md`, appendice E).

Se `.claude/llm-gitlab.json` non esiste: fermati e indica che manca il file di configurazione
di progetto (va creato seguendo `GITLAB-WORKFLOW.md` §11.2).

## Mappa progetti e identificatore

La mappa alias → id/path è nel file committato `.claude/llm-gitlab.json`, sezione `projects`.
Caricala così:

```bash
PROJECTS=$(python3 -c "import json;print(json.dumps(json.load(open('$CFG_PROJ'))['projects']))")
```

GitLab numera gli issue **per progetto** (incrementale): gli `iid` collidono fra progetti.
**L'identificatore canonico è `<alias>#<numero>`** (es. `kernel#2`, `ombrello#10`).
In GitLab `#` = issue/work item, `!` = merge request.

Per risolvere un alias in id numerico e path completo:

```bash
PROJECT_ID=$(python3 -c "import json;print(json.load(open('$CFG_PROJ'))['projects']['<ALIAS>']['id'])")
FULL_PATH=$(python3 -c "import json;print(json.load(open('$CFG_PROJ'))['projects']['<ALIAS>']['path'])")
```

Per ricavare l'alias da un `web_url`: estrai il path dopo l'`instance_url` e confrontalo con i
`path` nella mappa.

Modello di organizzazione: l'**ombrello** ospita issue di *coordinamento* per macrotema, a cui
sono agganciati i Task concreti dei sotto-progetti. **Attenzione a due meccanismi distinti**:

- **Child items** (gerarchia parent/child): legame primario, visibile come "Child items" nella UI.
  **Si legge solo via GraphQL**, NON dall'endpoint REST `/links`.
- **Issue links** (`relates_to`/`blocks`/`is_blocked_by`): legame secondario, restituito da `/links`.

## Comportamento per argomento

### `list` (anche se nessun argomento) — elenco completo del gruppo

```bash
curl -s --max-time 25 -H "$HDR" "$URL/api/v4/groups/$GROUP_ENC/issues?per_page=100&scope=all&order_by=created_at&sort=asc"
```

Raggruppa per **milestone** (ordinata) e poi per `iid`. Per ogni issue stampa:
`[x|spazio] <alias>#<iid> — <titolo>` (la `x` se `state==closed`).
L'alias si ricava da `project_id` cercandolo nei valori `id` della mappa progetti.

### `milestones` — elenco milestone con statistiche

```bash
curl -s --max-time 25 -H "$HDR" "$URL/api/v4/groups/$GROUP_ENC/milestones?per_page=100&state=active"
```

Per ciascuna milestone mostra titolo, stato e (se utile) conteggio issue aperti/chiusi.

### `<alias> <iid>` — dettaglio di un singolo issue

Esempio `/issue kernel 2` (= `kernel#2`). Risolvi `<alias>` in id numerico e full-path
dalla mappa, poi:

```bash
# 1) dettaglio issue + issue links (REST)
curl -s --max-time 25 -H "$HDR" "$URL/api/v4/projects/<PROJECT_ID>/issues/<IID>"
curl -s --max-time 25 -H "$HDR" "$URL/api/v4/projects/<PROJECT_ID>/issues/<IID>/links"

# 2) gerarchia parent/child (GraphQL) — i "child items" NON sono nei /links
GQL='query { project(fullPath: "<FULL_PATH>") { workItems(iid: "<IID>") { nodes { widgets { ... on WorkItemWidgetHierarchy { parent { iid title webUrl } children { nodes { iid title state webUrl workItemType { name } } } } } } } } }'
curl -s --max-time 25 -H "$HDR" -H "Content-Type: application/json" -X POST "$URL/api/graphql" \
  --data "$(python3 -c "import json,sys;print(json.dumps({'query':sys.argv[1]}))" "$GQL")"
```

Mostra: titolo, stato, milestone, label, assegnatario, la **descrizione completa**
(che di solito contiene i criteri di accettazione), i **child items** (dal GraphQL: per ciascuno
`<alias>#<iid>` + stato + tipo, con l'alias ricavato da `webUrl` confrontando con la mappa),
l'eventuale **parent** e gli **issue links** correlati (dal secondo endpoint REST:
`references.full` + `link_type` + stato).

### `<numero>` senza alias — ricerca per numero

Se viene passato solo un numero (es. `/issue 10`), cerca l'issue in tutti i progetti della mappa
e mostra i risultati trovati con il loro identificatore `<alias>#<iid>`.

## Regole

- **Sola lettura**: mai mutazioni. Sul REST: mai POST/PUT/DELETE. Sul GraphQL: solo `query`,
  **mai `mutation`** (la POST a `/api/graphql` con un `query {}` è una lettura ed è consentita).
- Non stampare mai il PAT, nemmeno in caso di errore: se curl fallisce, mostra solo lo status HTTP.
  In caso di errore GraphQL, mostra il contenuto di `errors` senza il PAT.
- Output in italiano, sintetico. Per `list` privilegia la tabella/elenco compatto.
