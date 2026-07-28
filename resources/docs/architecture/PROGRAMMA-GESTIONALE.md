# Gestionale — Canovaccio (il nostro programma di lavoro)

> Documento vivo. È il **programma** della prima sezione tecnica del portfolio: un gestionale
> di acquisti e magazzino. Definisce *cosa* costruiamo — entità, gerarchie, relazioni, flussi —
> non *come* scrivere il codice riga per riga.
>
> Nasce (2026-07-28) scremando un gestionale reale e corposo di dominio "trading company"
> (ciclo acquisto → magazzino → vendita → spedizione → fattura). Da quel modello abbiamo tenuto
> **lo scheletro concettuale** e generalizzato tutto: nessun nome, campo o schema proprietario è
> riprodotto qui. Questo file descrive un gestionale nostro e autonomo.

---

## 1. Scopo e confini

**Cosa costruiamo**: la porzione **acquisti → magazzino** di un gestionale di magazzino.
Un'azienda compra merce dai propri **fornitori** tramite **ordini d'acquisto**; la merce **arriva**
e viene registrata in una **ricezione**; da ricezioni e (in futuro) uscite si ricava la
**giacenza** di ogni prodotto.

**Perché mezzo ciclo e non tutto** (deciso 2026-07-28): il lato vendita/spedizione/fattura è in
gran parte *speculare* all'acquisto. Fermarci a metà ci dà **tutta la grammatica di relazione
che ci serve** (1-a-molti, molti-a-molti con attributo sul ponte, documento testata+righe, valore
calcolato) senza raddoppiare entità quasi identiche. Quando questa metà sarà solida e vorremo più
materiale, il lato vendita è l'estensione naturale già mappata (vedi §7).

**Fuori scope, di proposito** (ciò che abbiamo scremato dal gestionale reale):
- entità base polimorfica / class-table inheritance (over-engineering per noi);
- dimensioni ortogonali via tabelle-ponte (stagione, brand) e sincronizzazione con ERP esterni;
- multilingua sulle descrizioni, generazione PDF/CSV, codici doganali e logica fiscale;
- autenticazione, utenti e ruoli → **rimandati** a un capitolo tecnico separato (vedi §6);
- il lato vendita, spedizione e fatturazione → **rimandati** (vedi §7).

---

## 1-bis. Obiettivo d'uso — il gestionale dev'essere *provabile*, non solo *corretto*

Questo progetto ha due obiettivi che convivono e vanno tenuti insieme in ogni passo:

- **A — istruttivo**: scrivere e capire la grammatica (PHP/Laravel ora, JS/TS/React poi). Il
  modello di dominio delle §§2–4 serve soprattutto questo.
- **B — vetrina utilizzabile**: un recruiter o un altro programmatore deve poter aprire il
  gestionale, **capirlo al volo e fare le sue prove** senza istruzioni. Questo obiettivo **non**
  è servito dal modello dati: è servito dall'interfaccia, dai dati di esempio e dalla chiarezza.

Da qui tre principi che governano *come* costruiamo, non solo *cosa*:

1. **Ogni entità nasce completa, non a metà.** Quando introduciamo un'entità non ci fermiamo alla
   migration: le diamo la sua UI minima usabile e i suoi **dati di esempio**. Una tabella vuota e
   senza schermata non è "fatta" ai fini dell'obiettivo B.

2. **Dati di seed realistici, fin da subito.** Un magazzino vuoto non si può provare. Il progetto
   include un **seeder** con fornitori, prodotti, articoli, un paio d'ordini e ricezioni già
   presenti, così che chi apre l'app trovi un sistema "vivo" con cui giocare. Il seed è parte del
   lavoro di ogni entità, non un ripensamento finale.

3. **Il concetto contro-intuitivo va spiegato dall'interfaccia.** La distinzione
   `Product` fisico ↔ `CatalogueItem` commerciale (§2) è preziosa didatticamente ma **opaca** per
   un visitatore. È compito della UI renderla evidente (es. mostrare "1 articolo = 3× questi
   prodotti"), non del visitatore indovinarla. L'intuitività è un requisito, non un di più.

**Dove si realizza l'obiettivo B — il frontend** (deciso 2026-07-28):
Il valore-vetrina vive nel **frontend React/Next in TypeScript**, non in una UI Blade curata.
Quindi:
- durante la costruzione del dominio, la UI in **Blade resta minimale** — serve solo a *collaudare*
  il backend mentre si impara, non è il prodotto finale ed è usa-e-getta;
- il backend è progettato per **esporre API pulite** (REST), perché quello sarà il **contratto**
  con il frontend React. Le entità e le operazioni delle §§2–4 vanno pensate anche come endpoint,
  non solo come schermate Blade;
- appena il dominio ha abbastanza da consumare, si apre la **sezione React/Next** che rende il
  gestionale davvero provabile e "portfolio-grade". È lì che l'obiettivo B si compie.

**Traguardo per una demo raccontabile** (§7 rivista): il ciclo giacenza si *chiude* solo con le
uscite. La parte **vendita/uscita** non è quindi un'estensione meramente opzionale: è il traguardo
che rende la demo completa (`giacenza = ricevuto − uscito`) e la storia mostrabile a un recruiter.
Ci arriviamo dopo l'acquisto→magazzino, ma è un **obiettivo dichiarato**, non un forse.

---

## 2. Le entità (scheletro del dominio)

Sei entità. Tre sono "anagrafiche/catalogo", tre compongono il **documento d'ordine** e la
**ricezione**. Nomi in inglese (coerenza con lo stile del progetto), descrizioni in italiano.

### Anagrafiche e catalogo

**`Supplier`** — il fornitore da cui compriamo.
Attributi salienti: `name`, dati di contatto essenziali (una città/paese basta), eventuale `code`.
> Volutamente snello: niente `Company`/`Address` separati come nel gestionale reale. Un fornitore
> è un fornitore. Se un domani servirà un indirizzo strutturato, sarà un'estensione consapevole.

**`Product`** — il prodotto **fisico** che movimentiamo a magazzino (lo SKU reale).
Attributi salienti: `name`, `sku`/`code`, unità di misura, e i **campi giacenza** (vedi §4 —
decideremo se colonna calcolata o valore derivato al volo).
> È l'entità che **già esisti**: partiamo dal `Product` del CRUD attuale e lo evolviamo.

**`CatalogueItem`** — l'**articolo commerciale** che compare in un ordine d'acquisto.
Attributi salienti: `code`, `description`, listino/prezzo di riferimento.
> **Perché separato dal prodotto** (il pezzo più formativo, tenuto di proposito): ciò che ordini
> a un fornitore non sempre è un singolo prodotto fisico. Un articolo di catalogo può essere un
> *insieme* di prodotti in certe quantità (es. un "kit" = 3 pezzi di prodotti diversi). Articolo
> commerciale e prodotto fisico stanno quindi in **molti-a-molti**, e il ponte porta la
> **quantità** (vedi §3, relazione ⑤). Questo è il molti-a-molti "ricco" — la grammatica Eloquent
> che vogliamo imparare.

### Il documento d'ordine

**`PurchaseOrder`** (testata) — un ordine d'acquisto a un fornitore.
Attributi salienti: `code`/numero, `status` (bozza → confermato → …), `order_date`, `supplier` (FK),
`total` (derivabile dalle righe).

**`PurchaseOrderRow`** (riga) — una riga dell'ordine: *quale* articolo, *quanto*, *a che prezzo*.
Attributi salienti: FK a `PurchaseOrder`, FK a `CatalogueItem`, `quantity`, `unit_price`, `amount`.
> Testata + righe è **il** pattern del documento gestionale. La riga traccia sempre all'indietro
> il documento a cui appartiene: è ciò che rende ricostruibile la catena.

### La ricezione (ingresso a magazzino)

**`Delivery`** (testata) — l'arrivo fisico della merce da un fornitore.
Attributi salienti: `code`, `delivery_date`, `supplier` (FK), `status`.

**`DeliveryRow`** (riga) — cosa è arrivato concretamente: *quale prodotto*, *quanti pezzi*,
collegato alla riga d'ordine che sta evadendo.
Attributi salienti: FK a `Delivery`, FK a `Product` (il **fisico**, non l'articolo!),
`quantity`, e una **FK di tracciabilità alla `PurchaseOrderRow`** che questa ricezione soddisfa.
> Nota concettuale importante: l'ordine parla di **articoli commerciali** (`CatalogueItem`), la
> ricezione parla di **prodotti fisici** (`Product`). Il ponte item↔product (relazione ⑤) è ciò
> che permette di passare dall'uno all'altro. È il fulcro del modello.

---

## 3. Le relazioni (il cuore dell'apprendimento)

```
Supplier ─1:N─► PurchaseOrder ─1:N─► PurchaseOrderRow ──(N:1)──► CatalogueItem
   │                                        ▲                          │
   │                                        │ (tracciabilità)          │ ⑤ N:N con "quantity"
   │                                        │                          ▼
   └─1:N─► Delivery ─1:N─► DeliveryRow ─────┘            (ponte)   Product
                                │                                     ▲
                                └───────────(N:1)────────────────────┘
```

Le relazioni da implementare, in ordine di crescente novità didattica:

- **① `Supplier` 1-a-molti `PurchaseOrder`** — un fornitore ha molti ordini; un ordine ha un
  fornitore. `hasMany` / `belongsTo`. *(già territorio noto in teoria, prima volta in pratica).*
- **② `PurchaseOrder` 1-a-molti `PurchaseOrderRow`** — il documento e le sue righe. Introduce
  l'idea di **cascata** (cancello l'ordine → cosa succede alle righe) e di **totale derivato**.
- **③ `PurchaseOrderRow` molti-a-1 `CatalogueItem`** — ogni riga punta a un articolo di catalogo.
- **④ `Supplier` 1-a-molti `Delivery`** e **`Delivery` 1-a-molti `DeliveryRow`** — specularità
  utile: la ricezione è un altro "documento testata+righe". Consolida il pattern.
- **⑤ `CatalogueItem` molti-a-molti `Product` (con `quantity` sul ponte)** — **il pezzo forte.**
  Tabella-ponte `catalogue_item_product` con colonna extra `quantity`. In Eloquent: `belongsToMany`
  + `withPivot('quantity')`. È il concetto nuovo che giustifica tutto il modello.
- **⑥ `DeliveryRow` molti-a-1 `PurchaseOrderRow`** (tracciabilità) — la riga di ricezione sa quale
  riga d'ordine sta evadendo. FK "all'indietro" che chiude la catena ordine → ricezione.

---

## 4. Il flusso di dominio (cosa "muove i dati")

Un gestionale non è un CRUD: c'è un **ciclo di vita** e un **valore che si evolve**.

1. **Ordine** — si crea un `PurchaseOrder` verso un `Supplier` con le sue `PurchaseOrderRow`
   (quali articoli, quante unità, a che prezzo). L'ordine nasce in stato *bozza*, poi
   *confermato*.
2. **Ricezione** — da un ordine confermato si genera una `Delivery`: per ogni riga arrivata,
   una `DeliveryRow` che dice *quale prodotto fisico* e *quanti pezzi* sono entrati, collegata
   alla riga d'ordine che evade. Qui l'articolo commerciale si "risolve" nei prodotti fisici
   tramite il ponte ⑤ e le sue quantità.
3. **Giacenza** — la quantità in magazzino di un `Product` **non è un contatore** che
   incrementiamo a mano: è il **risultato di un calcolo** sui movimenti.
   Per la fase acquisto-solo: `giacenza = somma delle quantità ricevute` (`DeliveryRow`).
   Quando aggiungeremo le uscite: `giacenza = ricevuto − uscito`.
   > Decisione tecnica aperta (§8): calcolarla al volo con una query aggregata, oppure tenere una
   > colonna cache aggiornata dagli eventi. Il gestionale reale teneva entrambe; noi sceglieremo
   > la più didattica quando ci arriveremo.

Questo flusso è ciò che trasforma sei tabelle in un *gestionale*: lo stato dell'ordine avanza,
la ricezione genera movimenti, la giacenza è una **vista derivata** sui movimenti.

---

## 5. Come si mappa sul codice che abbiamo già

- Partiamo dal **`Product` esistente** (CRUD completo già fatto) e lo teniamo come entità fisica.
- La **prima estensione** è introdurre `Supplier` + la relazione ① — il salto minimo che ci porta
  dalla "singola tabella" al "modello relazionale". È il naturale punto d'ingresso.
- Poi il **documento** (`PurchaseOrder` + righe) e infine la **tripletta** (`CatalogueItem` +
  ponte ⑤) e la **ricezione** (`Delivery`).

Manteniamo lo stile Laravel idiomatico (migration con foreign key, Eloquent `hasMany`/`belongsTo`/
`belongsToMany`, eager loading) e lo `STYLE-GUIDE.md` del progetto.

---

## 6. Rimandato — utenti e ruoli

Il gestionale reale ha un impianto di autenticazione/autorizzazione robusto. Lo **rimandiamo**: è
un capitolo *tecnico* (Laravel auth, middleware, policy) distinto dal modello di *dominio* di
questo canovaccio. Lo apriremo quando il dominio acquisti→magazzino sarà in piedi e avrà senso
proteggerlo. Nota qui solo per non perderne memoria.

---

## 7. Fasi successive già mappate

Lo scheletro è pensato per crescere in modo speculare, riusando i pattern appresi. Non tutte queste
fasi sono uguali: la prima è un **traguardo dichiarato** (chiude la demo, vedi §1-bis), le altre
restano estensioni opzionali.

- **Lato vendita + uscita — traguardo per la demo completa** (non solo opzionale): `Customer`,
  `SalesOrder` + righe (specchio dell'acquisto, riusa 1:N + documento + ponte item↔product) e
  `Shipment` + righe. È ciò che **chiude il calcolo giacenza** (`ricevuto − uscito`) e rende la
  storia mostrabile a un recruiter: un sistema dove entra *e* esce merce. Introduce anche un
  **molti-a-molti "documentale"** (una spedizione può coprire più righe d'ordine). Ci arriviamo
  dopo aver consolidato acquisto→magazzino, ma è un obiettivo, non un forse.
- **Fatturazione** (opzionale): `Invoice` a valle delle spedizioni.
- **Movimenti manuali di magazzino** (opzionale): carichi/scarichi a rettifica con una causale.
- **Autenticazione e ruoli** (opzionale, vedi §6): quando avrà senso proteggere il gestionale.

Ognuna è un incremento a sé, da aprire solo quando la fase precedente è consolidata.

---

## 8. Decisioni tecniche aperte (le affronteremo al momento giusto)

- **Giacenza**: calcolo al volo (query aggregata) vs colonna cache. → §4.
- **Cancellazione**: hard delete vs soft delete (cancellazione logica) per ordini/documenti.
- **Stati** dell'ordine e della ricezione: quanti e quali, e chi governa le transizioni.
- **Totali** (`amount`, `total`): campi persistiti vs derivati dalle righe.
- **Numerazione** dei documenti (`code`): progressiva? per anno?

Nessuna va decisa adesso: le sblocchiamo una alla volta quando la relativa entità entra in gioco.

---

## Appendice — provenienza

Modello ispirato allo studio di un gestionale reale di dominio "trading company / acquisti e
logistica". Da quello sono stati appresi *i pattern* (documento testata+righe; distinzione
prodotto fisico / articolo commerciale con ponte a quantità; giacenza come valore calcolato;
tracciabilità delle righe a monte). Tutto il resto — nomi, campi, schema, prosa — è riscritto e
generalizzato per questo progetto. Nessun contenuto proprietario è riprodotto.
