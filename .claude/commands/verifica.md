---
description: Verifica conformità codice vs coding standards e salvataggio rapporto
model: haiku
arguments:
  - name: path
    description: "Percorso file/directory da verificare. Default: . (intero repo). Es: src/Core/, MetisKernelRivus/src/"
    required: false
    default: "."
  - name: spec
    description: "Criteri da controllare. Formati: G.1 (singolo) | G.1,G.3,G.5 (lista) | G.1-G.3 (range) | G.1-G.3,G.6 (misto). Default: tutti (G.1-G.7)"
    required: false
    default: "G.1-G.7"
---

# Procedura di Verifica

## 0. Contesto

Carica `resources/docs/overview.md` ed estrai:
- **Nome progetto**: prima parola del titolo (es. "Metis"). Usato nell'header del rapporto.
- **Prefisso interfacce**: dal namespace del componente (in `resources/docs/detail/context/<componente>.md` o `analysis/<componente>/architecture.md`) — l'ultimo segmento prima delle classi (es. `Ansoft\Metis\Kernel\*` → prefisso = `Metis`). Usato nel criterio G.1.

Se `overview.md` non esiste: procedi con `Nome progetto = "Progetto"` e `Prefisso interfacce = ""` (G.1 non verificabile automaticamente — segnalalo nel rapporto).

## 1. Parsing parametri

**Path** (default `.` = intero repo):
```bash
/verifica                          # → path=. (tutto)
/verifica MetisKernelRivus/src/    # → path=MetisKernelRivus/src/
```

**Spec** (default `G.1-G.7` = tutti):
```bash
/verifica --spec G.3           # Singolo
/verifica --spec G.1,G.3,G.5   # Lista
/verifica --spec G.1-G.3       # Range
/verifica --spec G.1-G.3,G.6   # Misto
```
Espandi i range, split le liste, deduplica, valida G.N ∈ {G.1…G.7}.

## 2. Sorgente dei criteri

I criteri sono definiti in **`resources/docs/standards/CODING-STANDARDS-PHP.md`** (regole lettera-numero A–L e checklist §M). Non esiste un file di spec separato: la fonte unica è lo standard. La mappa G.1–G.7 qui sotto è la selezione verificabile automaticamente, con il riferimento alla regola dello standard.

**Mapping criteri:**
| Codice | Criterio | Regola in CODING-STANDARDS-PHP | Auto-Detect |
|---|---|---|---|
| G.1 | Prefisso interfacce | G.1 | Grep `interface [A-Z]` → deve iniziare con `<Prefisso>` (da §0) |
| G.2 | Struttura classi (1–4 attributi) | D.1, D.2 | Grep `class [A-Z]`, conteggio attributi |
| G.3 | Catena dipendenze / gerarchia Metis | G.4 | Grep `use [A-Z]` + dipendenze da `analysis/<componente>/architecture.md` |
| G.4 | Validazione costruttore (no I/O) | C.1 | Grep `__construct`, cerca I/O |
| G.5 | CQRS naming | B.2, J | Grep `function [a-z]`, verifica pattern |
| G.6 | PHP 8.3 + strict types | A.2, I | Grep `declare`, type hints |
| G.7 | No getters/setters | D.3 | Grep `public function get/set` |

## 3. Analisi codice

Per il percorso in `path`: analizza ricorsivamente i file PHP, controlla SOLO i criteri parsati da `spec`, e per ogni violazione registra file, riga e tipo.

## 4. Generazione rapporto

- **Header:** Data/ora, Progetto (da §0), Path verificato, Criteri controllati (N/7).
- **Risultati:** tabella (Criterio | Codice | Stato | Dettagli) solo per i criteri richiesti.
- **Problemi:** lista `file:riga` per criterio controllato.
- **Raccomandazioni:** come risolvere, con rimando alla regola dello standard.

## 5. Salvataggio

Salva in:
```
resources/reports/verifica-$(date +%Y%m%d-%H%M%S).md
```
Nome con suffisso spec se parziale (`...-G1-G3-G6.md`) o `...-FULL.md` se completa. Il file resta tracciato in git per audit trail.

## 6. Output console

```
✅ Verifica completata: 5/7 criteri (G.1-G.3,G.6)
📊 Rapporto: resources/reports/verifica-YYYYMMDD-HHMMSS.md
📈 Risultati: 3✅ 1⚠️ 1❌
🔗 Criteri: resources/docs/standards/CODING-STANDARDS-PHP.md
```
Se problemi CRITICI (G.1, G.3, G.4, G.6): evidenzia con lista priorità.
