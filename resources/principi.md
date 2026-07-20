# Principi — ProgettoStudio

Taccuino dei **principi di sviluppo software** toccati con mano lungo il progetto.
Fratello di `appunti.md`, ma diverso per natura: `appunti.md` è la **lingua** (PHP/JS),
questo è il **come si scrive bene** (coesione, SOLID, pattern, refactoring).

**Perché esiste**: gli annunci middle/senior chiedono padronanza dei principi. Non si
imparano studiandoli a parte: si imparano quando il codice fa male e un principio spiega
*perché*. Qui traccio quei momenti, così tra qualche anno ricorderò il *quando*, non lo slogan.

**Regole del taccuino**
- Un principio = un blocco. Non la definizione (quella si googla): il **problema reale nel
  nostro codice**, il **prima/dopo**, e il **trade-off** (ogni principio ha un costo).
- Si entra qui solo dopo aver *sentito* il dolore, mai per averlo letto. Niente caccia ai pattern.
- Rigore **moderato** (come tutto il progetto): un principio è uno strumento, non un comandamento.
  Se applicarlo complica invece di chiarire, si annota anche quello.
- Si pota. Un blocco che non serve più si cancella.

## Mappa (cosa incontreremo lungo il gestionale)

Promemoria per *riconoscere* i principi quando arrivano — non da studiare in anticipo.
Ogni riga diventa un blocco vero solo quando la tocco davvero.

| Quando, nel gestionale | Il dolore | Il nome |
|---|---|---|
| Model Eloquent + "ordine solo se c'è giacenza" | Dove metto la regola? Controller, model, classe a parte? | Coesione, fat controller, **service layer** |
| Secondo tipo di sconto / secondo magazzino | Per aggiungerne uno devo modificare codice che già funziona | **Open/Closed** (SOLID) |
| Prodotti/ordini/utenti crescono insieme | Cambio qui, si rompe là | Accoppiamento, **Single Responsibility** |
| Il controller usa il magazzino senza sapere com'è fatto dentro | Perché conosce i dettagli interni? | **Dependency Inversion**, astrazioni |
| Export/notifiche in forme diverse | Stessa struttura, comportamento che cambia | Primi **pattern GoF**: Strategy, forse Factory |
| Ovunque | Sto copiando questo pezzo / questo nome non dice cosa fa | **DRY**, naming (*Clean Code*) |

## Indice dei principi toccati

_(vuoto — il primo arriverà con Eloquent + la regola giacenza-ordine)_

---
