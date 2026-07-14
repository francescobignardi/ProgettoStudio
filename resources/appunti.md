# Appunti — ProgettoStudio

Quaderno personale di Francesco. Concetti chiave estratti durante il percorso di studio, in forma chirurgica.

**Regole del quaderno**
- Un concetto = un blocco, max ~15-20 righe.
- Non ripetere codice o docs ufficiali: qui va solo ciò che non è ovvio o che ho capito superando un ostacolo.
- Si pota. Se un blocco non serve più, si cancella.

## Indice

- [Composer + autoload PSR-4](#composer--autoload-psr-4)
- [Docker + Docker Compose](#docker--docker-compose)
- [Dockerfile custom + estensioni PHP](#dockerfile-custom--estensioni-php)
- [PDO — connessione a MySQL](#pdo--connessione-a-mysql)
- [Laravel — routing + artisan serve](#laravel--routing--artisan-serve)
- [Blade — grammatica minima](#blade--grammatica-minima)
- [Laravel — controller (primo passo MVC)](#laravel--controller-primo-passo-mvc)

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
