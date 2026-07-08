# Appunti — ProgettoStudio

Quaderno personale di Francesco. Concetti chiave estratti durante il percorso di studio, in forma chirurgica.

**Regole del quaderno**
- Un concetto = un blocco, max ~15-20 righe.
- Non ripetere codice o docs ufficiali: qui va solo ciò che non è ovvio o che ho capito superando un ostacolo.
- Si pota. Se un blocco non serve più, si cancella.

## Indice

- [Composer + autoload PSR-4](#composer--autoload-psr-4)
- [Docker + Docker Compose](#docker--docker-compose)

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
