# Gestionale — Roadmap (a che punto siamo, in che ordine)

> Documento vivo, **compagno** di `PROGRAMMA-GESTIONALE.md`. Divisione dei ruoli:
> - **PROGRAMMA** = *cosa* costruiamo e *perché* (design: entità, relazioni, principi). Stabile.
> - **ROADMAP** (questo) = *in che ordine* e *a che punto*. Passi atomici + stato. Cambia ogni sessione.
>
> La roadmap **non ripete** il design: lo referenzia (`→ §3 ①` = relazione ① descritta nel PROGRAMMA §3).
> Ogni sessione: si sceglie il primo passo `[ ]` non fatto, lo si porta a `[x]`, si aggiorna il conteggio.

## Come si legge lo stato

- `[ ]` da fare · `[~]` in corso · `[x]` fatto.
- Ogni capitolo ha un conteggio **`[fatti / mappati]`**: passi *già previsti* di quel capitolo che sono
  completi. **Non** è una percentuale del progetto intero: i passi futuri si scoprono strada facendo,
  quindi un "% totale" sarebbe un numero inventato. Contiamo solo ciò che è realmente mappato.
- Un passo è "fatto" quando rispetta i principi del PROGRAMMA §1-bis: non solo la migration, ma
  **entità + sua UI/API minima + eventuale seed**. Una tabella senza modo di vederla non è "fatta".

---

## Capitolo A — Fondamenta relazionali (Supplier + primo documento d'ordine)   [3 / 6]

Obiettivo: passare da "singola tabella" (`Product` di oggi) a un modello con relazioni, arrivando
al primo **documento testata+righe**. È il salto concettuale più importante del backend.

- [x] **A.1** — `Supplier`: migration + model + CRUD minimo (lista/crea/mostra). Punto d'ingresso.
      Fatto con `Route::resource(...)->only([...])`, seeder, dettaglio per `id`.
- [x] **A.2** — Relazione **① `Supplier` 1-a-molti `PurchaseOrder`**: migration `PurchaseOrder`
      con FK `supplier_id`, metodi `hasMany`/`belongsTo`.   → §3 ①
- [x] **A.3** — Seed realistico via **Factory**: `SupplierFactory` + `PurchaseOrderFactory` (Faker,
      `HasFactory` sui model). Seed unificato che sfrutta la relazione: `Supplier::factory()->count(10)
      ->has(PurchaseOrder::factory()->count(5))->create()` (10 fornitori × 5 ordini). `PurchaseOrderSeeder`
      eliminato (creazione ora "da fornitore a ordini" in `SupplierSeeder`).   → §1-bis principio 2
- [ ] **A.4** — Relazione **② `PurchaseOrder` 1-a-molti `PurchaseOrderRow`**: le righe del documento;
      concetto di **cascata** on delete e di **totale derivato** dalle righe.   → §3 ②
- [ ] **A.5** — UI minima del documento: creare un ordine con le sue righe, vederlo.
      (Blade minimale — collaudo, non prodotto finale.)   → §1-bis (frontend)
- [ ] **A.6** — Consolidamento: eager loading (`with()`) per evitare query N+1 su lista ordini.

---

## Capitolo B — Il catalogo e la tripletta (il pezzo forte)   [0 / 4]

Obiettivo: introdurre l'articolo commerciale e il **molti-a-molti con attributo sul pivot** — la
grammatica Eloquent nuova che giustifica l'intero modello.

- [ ] **B.1** — `CatalogueItem`: migration + model + CRUD minimo.
- [ ] **B.2** — Relazione **③ `PurchaseOrderRow` molti-a-1 `CatalogueItem`**: la riga d'ordine punta
      a un articolo di catalogo.   → §3 ③
- [ ] **B.3** — Relazione **⑤ `CatalogueItem` molti-a-molti `Product` con `quantity` sul ponte**:
      tabella `catalogue_item_product`, `belongsToMany` + `withPivot('quantity')`.   → §3 ⑤
- [ ] **B.4** — UI che *spiega* la tripletta: mostrare "1 articolo = N× questi prodotti", così il
      concetto contro-intuitivo è chiaro a chi guarda.   → §1-bis principio 3

---

## Capitolo C — La ricezione a magazzino (chiusura acquisto→magazzino)   [0 / 4]

Obiettivo: la merce entra e si registra; comincia a esistere la **giacenza**.

- [ ] **C.1** — Relazione **④ `Supplier` 1-a-molti `Delivery`** + **`Delivery` 1-a-molti
      `DeliveryRow`**: la ricezione come secondo documento testata+righe (consolida il pattern). → §3 ④
- [ ] **C.2** — Relazione **⑥ `DeliveryRow` molti-a-1 `PurchaseOrderRow`** (tracciabilità): la riga
      di ricezione sa quale riga d'ordine evade — chiude la catena ordine→ricezione.   → §3 ⑥
- [ ] **C.3** — Flusso "genera ricezione da ordine confermato": lo stato dell'ordine avanza, la
      `DeliveryRow` collega prodotto fisico + riga d'ordine.   → §4 (flusso)
- [ ] **C.4** — **Giacenza calcolata**: `giacenza = somma quantità ricevute`. Decidere calcolo al
      volo vs colonna cache (→ §8) e mostrarla nella UI del prodotto.   → §4 punto 3

---

## Capitolo D — API + prime prove verso il frontend   [0 / 3]

Obiettivo: esporre il dominio come **API REST pulite** (il contratto col futuro frontend React).
Blade resta il collaudo; qui prepariamo ciò che React consumerà.

- [ ] **D.1** — API resource per prodotti/fornitori/ordini (endpoint REST, JSON pulito). → §1-bis (API)
- [ ] **D.2** — Seed completo e realistico per una **demo provabile** (fornitori, articoli, ordini,
      ricezioni, giacenze già presenti).   → §1-bis principio 2
- [ ] **D.3** — Documentare gli endpoint (anche solo un `.md`), così chi apre il progetto capisce il
      contratto senza leggere il codice.

---

## Fasi successive (non ancora scomposte in passi)

Restano al livello del PROGRAMMA §7; le scomporremo in capitoli quando ci arriveremo:

- **Lato vendita + uscita** — traguardo per la demo completa (chiude giacenza = ricevuto − uscito).
- **Frontend React/Next TS** — la sezione portfolio-grade dove si compie l'obiettivo "usabile".
- **Fatturazione**, **movimenti manuali di magazzino**, **auth/ruoli** — opzionali.

---

## Log sintetico di avanzamento

Una riga per sessione: data → cosa si è chiuso. (Il dettaglio narrativo sta nei diary `resources/memory/`.)

- **2026-08-06** — A.3 chiuso (seed realistico via **Factory** + Faker; `HasFactory` sui model; seed unificato `Supplier->has(PurchaseOrder)` che sfrutta la relazione ①; `PurchaseOrderSeeder` eliminato). Verificato: 10 supplier × 5 ordini = 50, distribuiti 5-a-testa.
- **2026-07-28** — A.1 chiuso (`Supplier`: migration + model + CRUD minimo, `Route::resource->only`, seeder). Refactor infrastrutturale (non un passo A–D): view riorganizzate in cartelle per entità (`products/`, `suppliers/`) con nomi resource; UI + dati seed portati in **inglese**; corretto bug redirect (`view()`=punto vs URL=slash) e markup `<form>`-in-`<p>`.
