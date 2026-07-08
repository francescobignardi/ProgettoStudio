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
