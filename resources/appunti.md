# Appunti — ProgettoStudio

Quaderno personale di Francesco. Concetti chiave estratti durante il percorso di studio, in forma chirurgica.

**Regole del quaderno**
- Un concetto = un blocco, max ~15-20 righe.
- Non ripetere codice o docs ufficiali: qui va solo ciò che non è ovvio o che ho capito superando un ostacolo.
- Si pota. Se un blocco non serve più, si cancella.

## Indice

- [⚡ Avvio rapido (cheat sheet)](#-avvio-rapido-cheat-sheet)
- [Composer + autoload PSR-4](#composer--autoload-psr-4)
- [Docker + Docker Compose](#docker--docker-compose)
- [Dockerfile custom + estensioni PHP](#dockerfile-custom--estensioni-php)
- [PDO — connessione a MySQL](#pdo--connessione-a-mysql)
- [Laravel — routing + artisan serve](#laravel--routing--artisan-serve)
- [Blade — grammatica minima](#blade--grammatica-minima)
- [Laravel — controller (primo passo MVC)](#laravel--controller-primo-passo-mvc)
- [Eloquent — model, migration, seeder + flusso del dato](#eloquent--model-migration-seeder--flusso-del-dato)
- [CRUD — scrivere dati: form, POST, validazione, store (la C)](#crud--scrivere-dati-form-post-validazione-store-la-c)

---

### ⚡ Avvio rapido (cheat sheet)

Promemoria operativo: come accendo tutto e dove giro i comandi. Copia-incolla, non concetti.

**1. Accendere lo stack** (dal Mac, nella cartella del progetto)
```
docker compose up -d      # accende php + mysql in background
docker compose ps         # verifica: entrambi "Up"
```

**2. Entrare nel container** (dove girano artisan/composer, in `/app/backend`)
```
docker exec -it -w /app/backend progettostudio-php bash
```

**3. Avviare il server web** (dentro il container)
```
php artisan serve --host=0.0.0.0 --port=8000
```
Il terminale resta "appeso" (foreground). Per i comandi `artisan` di gestione serve un **secondo** terminale (nuova shell col comando del punto 2).

**4. Aprire nel browser del Mac** → **`http://localhost:8080`** (NON 8000!)
- La 8080 è il "buco nel muro" del compose (`"8080:8000"`). La 8000 è interna al container.
- `php artisan serve` stampa `http://0.0.0.0:8000`: è la vista *interna*, NON l'URL da digitare. Errore classico: aprire 8000 → "impossibile raggiungere il sito".

**Comandi DB ricorrenti** (secondo terminale, dentro il container)
```
php artisan migrate                 # applica le migration nuove
php artisan migrate:fresh --seed    # RESET totale: droppa tutto, ricrea, ripopola (solo dev!)
php artisan db:seed                 # ripopola (NB: i seeder accumulano, non resettano)
```

**Connessione SequelAce** (dal Mac): host `127.0.0.1` — port `3316` — user/pass `studio` — db `progettostudio`. Mai il nome del container come host (quello lo risolve solo Docker internamente).

---

### Composer + autoload PSR-4

**Cos'è**: gestore di dipendenze PHP che risolve tre problemi in un colpo — (1) dipendenze di terze parti, (2) caricamento automatico delle classi, (3) supporto ai namespace.

**Perché esiste**: prima di Composer, ogni classe voleva un `require` a mano; una libreria voleva uno zip scaricato dal sito; le collisioni di nome erano un incubo. `npm` per PHP, in sostanza.

**Come si usa (minimo indispensabile)**:
- `composer.json` descrive il progetto e — nella sezione `autoload.psr-4` — mappa prefissi-namespace a cartelle: `"App\\": "src/"` significa "quando cerchi `App\Qualcosa`, guarda in `src/Qualcosa.php`". Il doppio `\\` è escape JSON.
- `composer init` genera lo scheletro del `composer.json` interattivamente. Il prompt sull'autoload chiede *solo la cartella* (il namespace lo deriva dal package name, che non sempre è quello che vuoi) — meglio skippare (`n`) e aggiungere la sezione `autoload` a mano.
- `composer dump-autoload` genera `vendor/autoload.php`, il file che orchestra il tutto.
- Nell'entry point (es. `run.php`): un solo `require __DIR__ . '/vendor/autoload.php';` sostituisce N `require` di classi. Da qui in avanti, `new App\Product(...)` funziona da solo.

**Insidie**:
- `vendor/` non va **mai** in git — è rigenerabile con `composer install`. Aggiungerlo al `.gitignore`.
- `composer.lock` invece **sì**, quando ci sono dipendenze — blocca le versioni esatte per installazioni riproducibili.
- Namespace ≠ path: il namespace è una proprietà logica della classe (dichiarata con `namespace ...;` in cima al file). Il path è dove il file vive sul disco. PSR-4 è la *convenzione* che li allinea, non una legge di natura — Laravel per esempio mappa `App\Http\Controllers` → `app/Http/Controllers/` (non `src/`).
- In un file entry point: ordine PSR-12 → prima `require` di setup, poi `use`, poi il codice.

---

### Docker + Docker Compose

**Cos'è**: motore che fa girare applicazioni dentro **container** — istanze runtime, isolate e usa-e-getta, di **immagini** (template read-only stratificati, presi da un registry tipo Docker Hub). Compose è l'orchestratore che tiene insieme più container in uno `docker-compose.yml`.

**Perché esiste**: risolve il "works on my machine". L'ambiente (versione PHP, estensioni, database, ecc.) smette di essere una variabile del computer di chi lavora e diventa **codice versionato nel repo**. Chi clona il progetto e fa `docker compose up -d` ha *esattamente* lo stesso stack, bit per bit. Attenzione: Docker agisce sullo strato **environment**, non sulle dipendenze applicative PHP — quelle restano compito di Composer.

**Come si usa (minimo indispensabile)**:
- `docker-compose.yml` dichiara i `services:` (uno per container). Ogni servizio: `image`, `container_name`, `volumes`, e — se il processo principale finisce subito, come nel CLI di PHP — un `command: tail -f /dev/null` per tenere vivo il container.
- **Volumi**: `- ./:/app` è un *bind mount* (cartella del Mac visibile live nel container), `- mysql-data:/var/lib/mysql` è un *volume nominato* (persistenza dati oltre la vita del container, va dichiarato anche al top-level in `volumes:`).
- **Porte**: mapping `"3316:3306"` = `host:container`. Sul Mac esponi 3316, dentro al container MySQL ascolta sempre su 3306.
- Flusso base: `docker compose up -d` (accende tutto in background), `docker ps` (verifica), `docker exec -it <nome> bash` (entra nel container), `docker compose down` (spegne container e rete, i volumi nominati sopravvivono).

**Insidie**:
- `command:` come **lista di un elemento** (`- tail -f /dev/null`) fa cercare a Docker un programma chiamato letteralmente con gli spazi dentro → container esce subito. Forma corretta: stringa (`command: tail -f /dev/null`) oppure lista con **un token per elemento**. L'autocomplete dell'IDE tende a suggerire la forma-lista senza spezzare i token, occhio.
- Container == usa-e-getta. Se modifichi qualcosa **dentro** il container (installi un pacchetto a mano, cambi un file), muore con il container. Cosa deve sopravvivere → dichiaralo in un volume.
- Per non collidere con altri progetti Docker sul PC (es. lavoro): prefissa i `container_name`, usa porte host non-standard (MySQL su 3316 invece di 3306), le reti separate sono già gratuite grazie a Compose.
- Nomi ingannevoli: `composer.json` e `docker-compose.yml` non c'entrano niente uno con l'altro. "Compose" qui = "comporre lo stack di container".

---

### Dockerfile custom + estensioni PHP

**Cos'è**: file di ricetta (nome esatto: `Dockerfile`, senza estensione) che descrive **come costruire un'immagine partendo da un'altra**. Ogni istruzione produce un **layer** sopra la base.

**Perché esiste**: le immagini ufficiali sono deliberatamente **minimali** (es. `php:8.3-cli` non ha `pdo_mysql`, `mysqli`, ecc.). Chi ha bisogno di componenti aggiuntivi non "rifà l'immagine" — la **estende** con un Dockerfile.

**Come si usa (minimo indispensabile)**:
- `FROM <immagine-base>` dichiara il punto di partenza. `RUN <comando>` esegue un comando al momento della build (installazione pacchetti, estensioni, ecc.).
- Le immagini PHP ufficiali portano tre utility che semplificano la vita: `docker-php-ext-install`, `docker-php-ext-enable`, `docker-php-ext-configure`. Compilano le estensioni core dai sorgenti già presenti in `/usr/src/php/ext/`. Non è magia Docker: è convenzione dell'immagine ufficiale PHP.
- Nel `docker-compose.yml`: sostituire `image: <nome>` con `build: .` (il `.` è il *build context*, la dir da cui leggere `Dockerfile`). Poi `docker compose up -d --build` per forzare la ricostruzione.

**Modello mentale a 3 livelli**: (1) immagine ufficiale minimale sul registry pubblico (Docker Hub), (2) Dockerfile custom aziendale o di progetto che ci aggiunge sopra, (3) container in esecuzione. In aziende grandi c'è spesso un livello 2-bis: **registry privato** dove le immagini custom vengono pubblicate una volta e i progetti le consumano come `image: registry.esempio.it/nome:tag`, senza rifare la build ogni volta.

**Insidie**:
- `image:` e `build:` nel compose sono **mutuamente esclusivi** per lo stesso servizio.
- Senza `--build` Compose riutilizza l'immagine cachata anche se il Dockerfile è cambiato. Regola: dopo ogni modifica al Dockerfile, `--build`.
- Dichiarare l'estensione anche in `composer.json` (`"require": { "ext-pdo_mysql": "*" }`) non installa niente — è un **check di piattaforma**: Composer verifica la presenza dell'estensione a `install`. Utile come documentazione delle dipendenze e come rete di sicurezza se un giorno qualcuno gira `composer install` fuori Docker.

---

### PDO — connessione a MySQL

**Cos'è**: PHP Data Objects, l'astrazione standard di PHP per parlare con un database. Interfaccia uniforme (`PDO` + `PDOStatement`) su cui ogni database ha il suo driver (`pdo_mysql`, `pdo_sqlite`, `pdo_pgsql`, ecc.).

**Perché esiste**: prima c'erano API specifiche per database (`mysql_*`, poi `mysqli_*` per MySQL, altre per PostgreSQL, ecc.). PDO uniforma la sintassi lato PHP — cambi driver, non riscrivi tutto — e nasce nativamente con **prepared statement** contro SQL injection.

**Come si usa (minimo indispensabile)**:
- **DSN** (Data Source Name): stringa di connessione nel formato `driver:opzione1=valore1;opzione2=valore2`. Per MySQL: `mysql:host=<host>;port=<port>;dbname=<db>;charset=utf8mb4`. Il `charset=utf8mb4` è quasi obbligatorio: senza, si rischiano encoding rotti sui caratteri fuori BMP (emoji, alcune lingue).
- Istanza: `new PDO($dsn, $user, $password)`. Se qualcosa va storto lancia **`PDOException`** — sempre in `try/catch`, così controlli il messaggio invece di far esplodere lo script con stack trace.
- `PDO` e `PDOException` vivono nel namespace root — nessun `use`.

**Insidie**:
- Dentro Docker, `host` non è `localhost` né `127.0.0.1`. È il **nome del servizio nel `docker-compose.yml`** (nel nostro caso `mysql`). Compose crea una rete DNS interna scoped al progetto, dove ogni servizio è raggiungibile per nome. Container-a-container si parla su **porte interne** (3306), non su quelle mappate all'host (3316).
- MySQL Docker image crea **due utenti**: `root` (con `MYSQL_ROOT_PASSWORD`) e l'utente applicativo (`MYSQL_USER` + `MYSQL_PASSWORD`) — con permessi automatici solo sul database `MYSQL_DATABASE`. Least privilege: **mai usare `root` dall'applicazione**, root serve per operazioni amministrative.
- Il messaggio di errore PDO distingue `using password: NO` (nessuna password fornita) da `using password: YES` (password fornita ma sbagliata) — utile in diagnosi.
- Per usare `pdo_mysql`, l'estensione deve essere presente nell'immagine PHP → passa dal Dockerfile custom (vedi blocco sopra). L'estensione `mysqlnd` da sola non basta: è la libreria native driver low-level, va accoppiata a `pdo_mysql` o `mysqli`.

---

### Laravel — routing + artisan serve

**Cos'è**: una **route** in Laravel è un collegamento `(URL + verbo HTTP) → codice da eseguire`. Il codice può essere una closure inline, un metodo di controller o direttamente il rendering di una view. Le route web si dichiarano in `routes/web.php`.

**Sintassi minima**:
```php
Route::get('/ciao', function () {
    return view('helloworld', ['nome' => 'Francesco']);
});
```
- `Route::get` mappa richieste GET; equivalenti: `Route::post`, `::put`, `::delete`, `::patch`.
- Path `/ciao` è relativo alla root del sito; supporta parametri dinamici `{id}`.

**Strumenti di orientamento**:
- `php artisan route:list` → tabella di tutte le route note, incluse quelle "regalate" dal framework (`storage/{path}` per file utente, `/up` per health check in Laravel 11+).
- `php artisan list | grep make` → mappa dei generatori (`make:controller`, `make:model`, `make:view`, ecc.). Raggruppabili in famiglie: HTTP/routing, dati (ORM Eloquent), viste, asincrono, architettura. È il "sommario del framework in comandi".

**`artisan serve` in container**:
- Server di sviluppo di Laravel (basato su `php -S`, non per produzione).
- Dentro un container: `php artisan serve --host=0.0.0.0 --port=8000`. `--host=0.0.0.0` è **obbligatorio** — il default `127.0.0.1` fa ascoltare solo il loopback interno del container, invisibile dall'host anche col port mapping aperto.
- Nel `docker-compose.yml`: `ports: - "8080:8000"` (host:container). Dal Mac apri `http://localhost:8080`.

**Catena end-to-end** (utile in debug): browser → port mapping compose → `artisan serve` in ascolto sulla porta interna → router Laravel → route match in `routes/web.php` → view resa. Se qualcosa non funziona, si percorre la catena in quest'ordine.

**Insidie**:
- `php artisan <qualcosa>` va lanciato **dentro il container**, in `/app/backend`. `artisan` è un file PHP fisico, non un comando globale; l'errore `Could not open input file: artisan` significa "sono nella cartella sbagliata".
- Il server `artisan serve` occupa il terminale (foreground). Per lanciare altri comandi `artisan` servono **due terminali paralleli**: uno per il server, uno per i comandi di gestione.
- Se cambi il `docker-compose.yml` (es. aggiungi/modifichi `ports:`), serve `docker compose up -d` per applicare — non basta `restart`. `ps` mostra il container "Created 5 days ago" se non è stato ricreato; leggere `docker compose ps`, non l'output di `up`, per la verità.

---

### Blade — grammatica minima

**Cos'è**: motore di templating di Laravel. I file `.blade.php` in `resources/views/` sono templating **compilato**: Laravel li trasforma al volo in PHP puro cachato, poi li esegue. Non è un linguaggio a sé — è zucchero sopra PHP.

**Due sintassi cardine**:
- `{{ $variabile }}` → **interpolazione**: stampa il valore, con escape HTML automatico (protezione XSS base). Va nel testo del template.
- `@direttiva ... @enddirettiva` → **controllo di flusso e composizione**. Chiusura esplicita obbligatoria.

Il salto mentale che serve: Blade mescola *due modelli*. `{{ }}` è "template + segnaposto" (il modo di pensare del designer HTML); `@if / @foreach` è "programmazione" (il modo di pensare del dev). Un template maturo usa entrambi.

**Come i dati arrivano alla vista**:
```php
return view('helloworld', [
    'nome' => 'Francesco',
    'colori' => ['rosso', 'blu', 'giallo'],
    'ruoli' => ['Paolo' => 'Admin', 'Luca' => 'Operaio'],
]);
```
`view()` ha due argomenti soli: nome vista + **un array di dati**. Laravel "spacchetta" l'array associativo: ogni chiave diventa una variabile omonima nel template. Tipi arbitrari (stringhe, liste, dizionari, oggetti, ecc.).

**Direttive più usate (viste oggi)**:
```blade
@if($nome == 'Francesco')
    <p>Ciao {{ $nome }}</p>
@endif

@foreach($colori as $colore)
    <p>{{ $colore }}</p>
@endforeach

@foreach($ruoli as $persona => $ruolo)
    <p>{{ $persona }}: {{ $ruolo }}</p>
@endforeach
```

Il `@foreach` PHP ha due modalità che valgono anche in Blade: `$item` per liste (indice numerico ignorato), `$chiave => $valore` per dizionari. Simmetria elegante col `=>` dell'inizializzazione — è lo stesso simbolo per "questa chiave punta a questo valore", sia quando costruisci l'array sia quando lo scomponi.

**Insidie**:
- Chiusura obbligatoria: `@endif`, `@endforeach`, `@endforelse`, ecc. Errore tipico "unexpected end of file" = manca un `@end…`.
- Se una variabile passata alla view non esiste, il template esplode con `Undefined variable`. Non c'è "silenzioso": Laravel preferisce errore visibile.
- `{{ }}` fa escape automatico. Per iniettare HTML "così com'è" (raro, va fatto solo con dati fidati) esiste `{!! !!}`. Uso maligno = XSS servito.
- Il nome della vista in `view('helloworld')` è **senza estensione e senza percorso**: Laravel per convenzione cerca `resources/views/helloworld.blade.php`. Sottocartelle → notazione a punti: `view('admin.dashboard')` → `resources/views/admin/dashboard.blade.php`.

---

### Laravel — controller (primo passo MVC)

**Cos'è**: una classe in `app/Http/Controllers/` che raccoglie la logica di risposta alle richieste HTTP. Nella route resta solo la mappa `URL → azione`, il "cosa fa" vive nel controller. Prima incarnazione concreta di MVC: **Route** (mappa) → **Controller** (logica) → **View** (presentazione).

**Perché esiste**: la closure inline in `routes/web.php` funziona per esempi banali. Appena la logica cresce (validazione input, query, decisioni), il file routes diventa un guazzabuglio. Il controller isola la logica in una classe testabile e riusabile.

**Generazione**: `php artisan make:controller PrimoController` → crea `app/Http/Controllers/PrimoController.php` con lo scaffolding standard (namespace `App\Http\Controllers`, `extends Controller`, corpo vuoto). Convenzione nome: **PascalCase + suffisso `Controller`**.

**Metodo minimo** (letteralmente il corpo della vecchia closure):
```php
public function ciao()
{
    return view('helloworld', [
        'nome' => 'Francesco',
    ]);
}
```

**Aggancio dalla route**:
```php
use App\Http\Controllers\PrimoController;

Route::get('/ciao', [PrimoController::class, 'ciao']);
```
Leggere come frase: "GET /ciao → esegui metodo `ciao` di `PrimoController`". La coppia `[Classe::class, 'metodo']` è la forma canonica moderna (Laravel 10/11+).

**Perché array `::class` e non stringa `'PrimoController@ciao'`**: la vecchia forma stringa esiste ancora ma è "solo testo" — nessuno la valida finché non arriva una richiesta. Con `::class` l'IDE sa che la classe è reale: typo → segnalato, refactor → aggiorna anche la route.

**Verifica**: `php artisan route:list` deve mostrare la route mappata a `App\Http\Controllers\PrimoController@ciao` invece di `Closure`. È la conferma "da dentro il framework" che il refactor è andato.

**Nota MVC**: quando si sposta la logica dalla closure al controller, **la view non si tocca**. Il suo contratto è "dammi queste variabili e le mostro" — non le importa chi gliele passa. Questo è il punto del refactor: cambia il *come si costruiscono* i dati, non il *come si presentano*.

---

### Eloquent — model, migration, seeder + flusso del dato

**Cos'è Eloquent**: l'**ORM** (Object-Relational Mapping) di Laravel. Mappa **tabella ↔ classe** e **riga ↔ oggetto**, così si opera sul DB scrivendo codice PHP invece di SQL. Pattern sottostante: **Active Record** (l'oggetto *è* una riga, sa salvarsi/caricarsi da sé).

**I due pregi concreti** (oltre alla comodità):
- **Sicurezza**: sotto usa query parametrizzate (PDO prepared statement) → SQL injection e escaping non sono più un problema tuo.
- **Il codice parla di dominio, non di tabelle**: `$ordine->cliente->indirizzo` invece di JOIN a mano. (Le relazioni arriveranno col gestionale.)

#### Migration — versionare lo *schema*

**Cos'è**: un file che *descrive una trasformazione dello schema* (crea/modifica tabelle), ripetibile e versionata in git. Non è "modificare il DB a mano" (quello è SequelAce): è un **log storico + strumento di team/deploy**.

- Genera: **`php artisan make:migration create_products_table`** → `database/migrations/<timestamp>_create_products_table.php`.
- `up()` = cosa fare (crea la tabella), `down()` = come annullare (droppala). Il `down()` di una `create` è banale; il dolore nasce dalle `alter` (rimuovere colonna/PK: l'inverso non è pulito → va scritto a mano).
- Colonne: `$table->string('name')`, `$table->decimal('price', 8, 2)`, `$table->integer('stock')`, più `$table->id()` (PK auto-increment) e `$table->timestamps()` (`created_at`/`updated_at` gestiti da Eloquent).
- **Sintassi da ricordare**: `$table->TIPO('nome_colonna')` — il **tipo è il metodo** (insieme chiuso e noto), il **nome è l'argomento** (libero). NON `$table->nome('tipo')`.
- **`decimal` per i prezzi, mai `float`**: il float ha errori di rappresentazione (10.10+0.20 → 10.2999…). `decimal(precision, scale)`: precisione totale + decimali. `8,2` = euro. Scriverlo esplicito anche se è il default: è una *decisione di dominio*, va resa leggibile (l'IDE lo segnala come ridondante → falso positivo, ignoralo).
- Applica: **`php artisan migrate`** (esegue solo le migration non ancora applicate).

**Tabella `migrations`**: checklist interna (colonne `migration`, `batch`) di *quali file sono già stati eseguiti*. È il tracciamento che rende `migrate` idempotente. Diversa da git (git versiona il *codice*, incluse le migration; la tabella dice "di quei file, questi li ho già applicati *a questo* DB").

#### Model — l'oggetto che mappa la tabella

- Genera: **`php artisan make:model Product`** → `app/Models/Product.php`. Classe **quasi vuota**: Eloquent legge lo schema a runtime, non ripete le colonne (*convention over configuration*).
- **Convenzione nome**: model **singolare PascalCase** (`Product`), tabella **plurale** (`products`). Il plurale è derivato in **inglese** → con nomi italiani la convenzione si rompe (`Prodotto`→`prodottos`); per questo qui si usano nomi inglesi.
- **`$fillable`** (da aggiungere a mano): whitelist delle colonne riempibili in blocco via array. `Product::create(['name'=>…])` è **mass assignment**, che Laravel blocca di default per sicurezza (evita che un form inietti campi non previsti tipo `is_admin`). Forma standard: `protected $fillable = ['name','price','stock'];` (senza type-hint).

**Collisione EO ↔ Active Record** (da tenere a mente): Eloquent = oggetto-guscio permissivo (`new Product()` vuoto è lecito, si riempie dopo). L'approccio EO = costruttore esplicito che protegge gli invarianti (non esiste un `Product` senza nome). Due filosofie, prezzi diversi: Eloquent baratta rigore per velocità.

#### Seeder — popolare il DB con dati di partenza

- Genera: **`php artisan make:seeder ProductSeeder`** → `database/seeders/ProductSeeder.php`. Convenzione nome: suffisso **`Seeder`** intero.
- Dentro `run()`: un `Product::create([...])` **per riga** (un array = una riga). Serve `use App\Models\Product;`.
- **Va registrato** in `DatabaseSeeder` (l'*entry point* del seeding) con `$this->call(ProductSeeder::class);`. Poi:
  - **`php artisan db:seed`** → esegue `DatabaseSeeder::run()` (e quindi i seeder registrati, in ordine).
  - **`php artisan db:seed --class=ProductSeeder`** → esegue solo quello.
- **⚠️ I seeder NON hanno memoria** (nessuna tabella di tracciamento, a differenza delle migration). Rilanciare `db:seed` **riesegue tutto e accumula** (4 prodotti → 8 → 12…), e sbatte contro i vincoli `unique` (es. lo `User` di default con email unica → `UniqueConstraintViolationException`).
- **Reset pulito**: **`php artisan migrate:fresh --seed`**. `fresh` **droppa TUTTE le tabelle** (ignora i `down()`, non è un rollback per batch), rilancia tutte le migration da zero (id azzerati), poi `--seed` ripopola. È il "reset del mondo" quotidiano in sviluppo — e la risposta al dolore del `down()` chirurgico: in dev si rade al suolo, non si fanno rollback fini. **MAI in produzione** (cancella tutti i dati).

#### 🔀 Flusso del dato (mappa mentale per il debug)

La strada che un dato percorre dal DB fino allo schermo. Da tenere a mente: **quando qualcosa non appare, si percorre questa catena a ritroso** per capire *dove* si è perso.

```
  ┌──────────────┐     Product::all()      ┌──────────────────┐
  │   DATABASE    │  ────────────────────▶  │      MODEL        │
  │ tabella       │   (Eloquent genera lo    │  Product          │
  │ `products`    │    SELECT, mappa righe   │  (app/Models/)    │
  │ (righe SQL)   │    → oggetti)            │  1 oggetto = 1 riga│
  └──────────────┘                          └──────────────────┘
                                                     │
                                                     │ il controller chiede i dati
                                                     ▼
  ┌──────────────┐   Route::get('/prodotti',  ┌──────────────────┐
  │   ROUTE       │   [ProductController::      │   CONTROLLER      │
  │ routes/web.php│ ◀─── class, 'index'])       │  ProductController │
  │ URL → azione  │   ─────────────────────▶   │  ::index()         │
  └──────────────┘   (la richiesta HTTP        │  $products =       │
         ▲            entra da qui)            │   Product::all();  │
         │                                     │  return view(...)  │
   browser: GET                                └──────────────────┘
   localhost:8080/prodotti                              │
         │                                              │ view('products', ['products'=>$products])
         │                                              ▼
  ┌──────────────┐                            ┌──────────────────┐
  │   BROWSER      │  ◀───────────────────────  │      VIEW         │
  │ HTML reso     │    (Blade compila in PHP,   │ products.blade.php │
  │ (i 4 prodotti)│     interpola i dati)       │ @forelse + ->name  │
  └──────────────┘                            └──────────────────┘
```

**Come leggerlo**: la richiesta entra dal **browser** → la **route** decide *quale azione* → il **controller** chiede i dati al **model** → il model interroga il **DB** e restituisce oggetti → il controller li passa alla **view** → Blade li rende in HTML → torna al browser. Il dato viaggia DB→schermo, la richiesta viaggia in senso opposto.

**Accesso ai dati in Blade**: gli elementi ciclati sono **oggetti**, non array → si accede con la **freccia `->`**: `{{ $product->name }}`, non `{{ $product['name'] }}`. (Un oggetto Eloquent stampato "nudo" con `{{ $product }}` viene serializzato in **JSON** — non è un errore, è la forma-API dei model, quella che userà il frontend React.)

**`@forelse` invece di `@foreach`+`@isset`**: per una lista, il caso reale da gestire non è "la variabile non esiste" (è garantita dal contratto controller→view; difendersi da una garanzia *nasconde* i bug invece di esporli), ma "la lista è **vuota**". `@forelse ($products as $product) … @empty … @endforelse` copre proprio quello. Nota: dentro `@forelse`, `@empty` va **nudo** (senza parentesi) — `@empty($var)` è un *altro* costrutto che vuole `@endempty` → "unexpected end of file".

**Regola trasversale (Clean Code)**: spendi codice/verbosità dove c'è **vera incertezza** (input utente, API esterne, liste che possono essere vuote), NON dove c'è una **garanzia** (una variabile che passi tu stesso due righe prima). Il `$fillable` è verbosità *buona* (decisione di sicurezza); l'`@isset` su un dato garantito è rumore.

#### Interrogare Eloquent: `where`, `get`, `find`, `findOrFail`

Superare `all()` (che prende *tutto*) per chiedere solo una fetta.

- **`Product::where('stock', '>', 0)->get()`** → filtro. Punto chiave: `where()` **non esegue**, costruisce un *query builder* (una domanda in attesa). L'SQL parte solo con **`->get()`**. I metodi si **concatenano** (*fluent interface*): `where(...)->where(...)->orderBy(...)->get()`. `all()` era la scorciatoia costruisci+esegui, per questo non aveva `->get()`.
  - `where('colonna', 'operatore', 'valore')` a 3 argomenti (operatore stringa: `>`, `<`, `>=`, `!=`, `like`…). Forma a 2 arg sottintende `=`.
  - Filtrare col DB vs ciclare in PHP: su volumi grandi `where` restituisce solo le righe utili, il ciclo PHP le caricherebbe tutte in memoria. È una delle ragioni d'essere dell'ORM. (Rivede l'`isAvailable()` scritto a mano negli esercizi EO: stessa logica, delegata al DB.)
  - **`->orderBy('colonna', 'asc'|'desc')`** → ordinamento, altro anello *di costruzione* (va **prima** di `->get()`). Concatenabile per il tie-break: `->orderBy('price','desc')->orderBy('name','asc')` = SQL `ORDER BY price DESC, name ASC` (il 2° criterio scatta solo a parità del 1°; le direzioni possono differire). **Punto non ovvio**: un `SELECT` senza `ORDER BY` **non garantisce nessun ordine** — se "sembra" ordinato per id è un accidente di InnoDB, fragile (cambia motore/indice → cambia l'ordine senza toccare codice). `orderBy` rende l'ordine *esplicito e garantito*. Stesso criterio di `decimal(8,2)` e della guardia 404: rendere esplicita una decisione, non affidarla al default.
- **`Product::find($id)`** → cerca **per chiave primaria**, restituisce **un solo oggetto** (non una collezione → niente `@foreach`, accesso diretto `$product->name`). Se l'id non esiste restituisce **`null`** → poi `$product->name` su null = *"Attempt to read property on null"*.
- **`Product::findOrFail($id)`** → "trova **o fallisci**": come `find`, ma se non trova lancia un'eccezione che Laravel converte in **404**. Una riga, nessun `if`. = `find` + guardia null + `abort(404)` impacchettati. Nome *parlante* (Clean Code: dice cosa fa). Preferirlo, sapendo cosa nasconde.

#### Route con parametro dinamico + dettaglio (show)

- **`Route::get('/products/{id}', [ProductController::class, 'show'])`** — le graffe `{id}` catturano un segmento variabile dell'URL (`/products/1` → `id=1`). Stesso meccanismo di `storage/{path}` visto in `route:list`.
- Il valore catturato arriva come **argomento del metodo**: `public function show(int $id)`. Il nome del parametro deve **combaciare** con quello nelle graffe (accoppiati per nome, non posizione). Type-hint `int` opzionale ma documenta.
- Convenzione resource: `index` = lista, **`show`** = dettaglio singolo (poi `store`/`update`/`destroy`).

**Dove va la guardia "risorsa inesistente": nel controller, non in Blade.** Principio: il **controller decide**, la **view presenta**. Mettere `@if`/`@isset` in Blade per gestire il null significa dare una *decisione* alla view (responsabilità sbagliata) — e peggio, la pagina risponderebbe `200 OK` ("tutto bene") mentre la risorsa non esiste: una bugia verso browser/SEO/frontend. "Questa risorsa non esiste" è una decisione **HTTP** (→ 404), sta nel controller. Con la guardia lì, alla view il dato arriva *garantito* → niente `@isset` (di nuovo: no difese su dati garantiti).

#### ⚠️ Array indicizzato vs associativo (grammatica PHP, non Laravel)

Errore classico con `view()`: `return view('product', [$product])` → in Blade *"Undefined variable $product"*.
- `[$product]` = array **indicizzato**: PHP assegna la chiave numerica `0`. `view()` prova a creare una variabile da quella chiave → nome non valido → la variabile non esiste in Blade.
- `['product' => $product]` = array **associativo**: la chiave stringa `'product'` diventa `$product` nel template.
- Regola: `view()` (e molta parte di Laravel) vuole **`'nome' => $valore`**. Conta la **chiave-stringa** (= il nome dall'altra parte), NON il nome della variabile PHP passata. Stesso `=>` del `@foreach($x as $k => $v)`.

---

### CRUD — scrivere dati: form, POST, validazione, store (la C)

Finora solo **lettura** (R): il controller *restituisce* dati. Con la **C** (Create) il flusso si inverte — il controller **riceve** dati dall'utente e li **scrive**. "Creare un prodotto" sono **due rotte in coppia** (pattern resource):
```php
Route::get('/products/create', [ProductController::class, 'create']);  // mostra il FORM
Route::post('/products',       [ProductController::class, 'store']);    // RICEVE, valida, salva, redirige
```

**⚠️ Ordine delle route: specifiche prima delle generiche.** `GET /products/{id}` messa *prima* di `/products/create` → `{id}` (jolly, accetta tutto) cattura la stringa `"create"` → `show("create")`. Laravel legge dall'alto e si ferma alla **prima** che combacia. Quindi `create` va **sopra** `{id}`. (Bonus: il type-hint `int $id` ha reso il bug un **TypeError** esplicito invece di un 404 confuso → *fail-fast*.)

**GET vs POST (il verbo conta).** `GET` = *chiedo/navigo* (leggo, ricaricabile senza danni, bookmarkabile). `POST` = *agisco* (mando dati per modificare lo stato). Un form che salva usa `method="post"`.

#### Il form HTML
```blade
<form action="/products" method="post">
    @csrf
    <label for="name">Nome:</label>
    <input type="text" id="name" name="name">
    ...
    <button type="submit">Salva</button>
</form>
```
- **`for` / `id` / `name` sono tre cose diverse**: `for` (label) ↔ `id` (input) legano etichetta e campo *nel browser* (clic sull'etichetta → focus sul campo; accessibilità); `id` serve anche a CSS/JS. **`name` è l'unico che parla col server**: è la chiave con cui il dato viaggia nel POST, e va letto per `name` nel controller. Un input senza `name` non viene inviato. (I `name` combaciano con le colonne DB e col `$fillable` → è il filo form→controller→tabella.)
- **`action`** = *dove* mandare (URL/rotta); **`method`** = verbo HTTP. Il bottone `submit` è solo il grilletto: la destinazione la decide `action`, non il bottone.
- **`@csrf`** (prima riga dentro il form): genera un `<input hidden name="_token">` con il token di sessione → protezione **CSRF** (Cross-Site Request Forgery: impedisce che un sito terzo faccia partire il POST dal browser di un utente loggato). Grammatica standard: ogni form POST ha `@csrf`, come ha `method="post"`. *(Nota: su Laravel 13 con setup minimale il 419 "Page Expired" può non scattare — ma il token va messo lo stesso, è la pratica corretta e servirà in prod. **Si verifica, non si indovina**: `route:list`, `bootstrap/app.php`, grep del middleware.)*

#### Il metodo `store`
```php
public function store(Request $request)
{
    $request->validate([
        'name'  => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'stock' => 'required|integer|min:0',
    ]);
    Product::create([
        'name'  => $request->name,
        'price' => $request->price,
        'stock' => $request->stock,
    ]);
    return redirect('/products');
}
```
- **`$request`** = la richiesta HTTP incapsulata (Laravel la inietta col type-hint `Request`). I campi si leggono con `$request->name` (o `$request->input('name')`).
- **`validate()` NON protegge da injection** (quella la fa Eloquent con PDO). Valida il **dominio**: che i dati abbiano senso per un prodotto. Regole concatenate con `|`. L'IDE autocompila `required` (troppo debole: dice solo "c'è") → rinforzare con tipo e vincoli (`numeric|min:0`, `integer|min:0`) coerenti con lo **schema migrazione**.
- **`Product::create([...])`** = identico al seeder, ma i valori vengono dall'utente. Funziona solo perché `name`/`price`/`stock` sono nel **`$fillable`** (mass assignment).
- **`return` obbligatorio** (grammatica PHP): `redirect(...)` senza `return` → il metodo torna `null` → **pagina bianca**. Come `return view(...)`. Helper `redirect('/products')` (minuscolo) ≈ facade `Redirect::to('/products')` (serve `use`).

#### POST-Redirect-GET (perché `store` chiude con redirect, mai con `view`)
Se dopo il POST restituissi direttamente `view(...)`, l'URL resterebbe legato a un POST → **F5 = "reinvia modulo" = doppione nel DB** (il bug dell'ordine addebitato due volte). Il **redirect** manda il browser a una `GET /products` pulita → ricaricabile all'infinito senza risalvare. Pattern in 3 tempi: **POST** (salva) → **Redirect** ("vai a leggere") → **GET** (mostra, sola lettura). Regola: `store`/`update`/`destroy` finiscono con `return redirect(...)`, **mai** `return view(...)`.

#### 🎯 Strumento del peso giusto: `<a href>` vs `<form>`
Per *cambiare pagina* (es. pulsante "Crea" che porta al form) basta un **link**:
```blade
<a href="/products/create">Crea nuovo prodotto</a>
```
Costruire form + route + metodo `redirect()` per navigare è **un cannone per una mosca** (e apre bug: route duplicata `GET /products` che oscura `index`). Distinzione da tenere: **`<a href>` = navigare** (vai lì, GET); **`<form method=post>` = agire** (fai accadere qualcosa, modifica di stato). Un link *al* form è navigazione; l'azione (creare) avviene *dopo*, al Salva. Segnale d'allarme: se costruisci un'impalcatura per una cosa che *senti* dovrebbe essere semplice, probabilmente hai scelto lo strumento sbagliato.
