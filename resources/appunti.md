# Appunti — ProgettoStudio

Quaderno personale di Francesco. Concetti chiave estratti durante il percorso di studio, in forma chirurgica.

**Regole del quaderno**
- Un concetto = un blocco, max ~15-20 righe.
- Non ripetere codice o docs ufficiali: qui va solo ciò che non è ovvio o che ho capito superando un ostacolo.
- Si pota. Se un blocco non serve più, si cancella.

## Indice

- [Composer + autoload PSR-4](#composer--autoload-psr-4)

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
