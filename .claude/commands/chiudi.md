---
description: Chiude l'argomento corrente registrando la chiusura nel diary di claude e aggiungendo la bozza nel diary personale
---

Stai per chiudere l'argomento corrente. Esegui in ordine:

1. **Identifica i file diary di oggi**. Recupera la data con `date +%Y-%m-%d` e il nome dell'umano dalla variabile d'ambiente `$PROJECT_DEV` (configurata in `.claude/settings.local.json`).
   - Se `$PROJECT_DEV` NON è impostata: errore — chiedi al programmatore di configurarla in `.claude/settings.local.json`.
   - File claude: `resources/memory/<data-iso>.claude+${PROJECT_DEV}.diary.md`
   - File personale: `resources/memory/<data-iso>.${PROJECT_DEV}.diary.md`
   - Se il file claude NON esiste: errore — significa che non è mai stato aperto un capitolo. Avvisa il programmatore.
   - Se il file claude esiste: prosegui.

2. **Trova l'ultimo capitolo `## HH:MM — <Titolo>`** nel file claude. Quella è la sessione che stai per chiudere.

3. **Aggiungi alla fine di quel capitolo** una sezione di chiusura nel formato:

   ```
   ### Chiusura argomento — HH:MM

   - **Cosa è stato deciso**: [riepilogo 2-4 righe delle decisioni cristallizzate]
   - **Cosa è stato fatto**: [riepilogo 2-4 righe degli interventi concreti]
   - **Aperto / sospeso**: [eventuali questioni rimaste in sospeso, o "nessuna"]
   - **Prossimo passo concordato**: [se c'è un seguito già concordato, o "nessuno"]
   ```

   `HH:MM` è l'ora corrente (`date +%H:%M`).

4. **Aggiorna il diary personale** (`<data>.<PROJECT_DEV>.diary.md`):
   - Se il file NON esiste: crealo con l'intestazione `# Diario <PROJECT_DEV> — <data-iso>` seguita dalla sezione.
   - Se il file esiste: aggiungi la sezione in fondo.
   - In entrambi i casi, aggiungi una nuova sezione con il titolo identico all'ultimo capitolo del diary claude:

   ```
   ## HH:MM — <stesso titolo del capitolo claude>

   > Bozza creata da claude. Da rivedere/integrare.

   - [sintesi 2-4 bullet max, solo fatti concreti: cosa è stato creato/deciso/risolto]
   ```

   La sintesi deve essere **molto corta** (una riga per bullet), senza dettagli tecnici profondi — è una traccia per il programmatore da integrare con le sue note.

5. **Mostra al programmatore** i due blocchi scritti (chiusura claude + bozza personale).

6. **Suggerisci** al programmatore di:
   - Rivedere e integrare la bozza nel proprio diary
   - Lanciare `/clear` per pulire il contesto prima del prossimo argomento

**Vincoli**:
- NON inventare contenuti: usa solo quello che è realmente accaduto in conversazione.
- NON modificare i capitoli precedenti, solo aggiungere all'ultimo.
- Se la conversazione non ha argomenti chiusi (è appena iniziata, niente di sostanziale), avvisa che non c'è niente da chiudere.
