# 05 · NativePHP Mobile

UpMove draait als native iOS- en Android-app via [NativePHP Mobile](https://nativephp.com) (`nativephp/mobile` v4).
NativePHP bundelt PHP en de volledige Laravel-app in de app; de schermen worden in een WebView getoond.
Er is dus **geen server nodig**: alles (ook de SQLite-database) staat op het toestel.

## Vereisten

| Platform | Nodig |
|---|---|
| Android | Android Studio + SDK (min. SDK 33), een emulator of toestel met USB-debugging |
| iOS | macOS met Xcode + iOS-simulator (een fysiek toestel vereist een Apple Developer-account) |
| Beide | PHP 8.3+, Composer, Node 20+ |

## Eenmalige installatie

```bash
# .env: zet een unieke app-id (omgekeerde domeinnaam)
NATIVEPHP_APP_ID=nl.upmove.app
NATIVEPHP_APP_VERSION=DEBUG        # tijdens ontwikkelen ALTIJD DEBUG laten staan (zie hieronder)
NATIVEPHP_APP_VERSION_CODE=1

# Native projecten (Android Studio / Xcode) en PHP-binaries downloaden
php artisan native:install            # kies android, ios of both
```

`native:install` maakt de map `nativephp/` aan (staat in `.gitignore`). Doe dit op iedere ontwikkelmachine.

## Draaien op emulator / toestel

```bash
npm run build                 # assets bouwen (verplicht!)
php artisan native:run        # kies platform en toestel
```

Handige commando's:

```bash
php artisan native:devices    # beschikbare toestellen/emulators
php artisan native:emulator   # Android-emulator starten
php artisan native:watch      # bestanden synchroniseren naar de draaiende app
php artisan native:jump       # app testen in de NativePHP Jump-app op je telefoon (zonder Xcode/Android Studio)
php artisan native:tail       # Laravel-log van het toestel bekijken
php artisan native:debug      # omgevingsinformatie
```

Hot reload tijdens ontwikkelen:

```bash
npm run dev -- --mode=android   # of --mode=ios
```

> **Let op – `NATIVEPHP_APP_VERSION`:** de app pakt de Laravel-code (en draait de migraties) alleen
> opnieuw uit als het versienummer verandert. Met `DEBUG` gebeurt dat bij élke build. Staat er een vast
> nummer (bv. `1.0.0`), dan blijft de app na `native:run` de oude code tonen. Zet pas een echt
> versienummer bij het publiceren (`php artisan native:release`).

## Configuratie

- `config/nativephp.php` — app-id, versie, start-URL, permissies, Android/iOS-opties.
- `vite.config.js` — bevat `nativephpMobile()` en `nativephpHotFile()` zodat assets in de app goed laden.
- `axios` moet als dependency in `package.json` staan (vereist door de NativePHP-Vite-plugin).
- Gevoelige `.env`-sleutels (`*_SECRET`, `DB_PASSWORD`, …) worden bij het bundelen automatisch verwijderd
  (`cleanup_env_keys`).

## Database op het toestel

- `DB_CONNECTION=sqlite`. NativePHP voert de migraties uit bij het opstarten van de app.
- De voorbeeldchallenges worden door de migratie `create_challenges_table` ingevoegd, dus er is geen
  `db:seed` nodig op het toestel.
- Iedere installatie heeft een eigen, lokale database: accounts en XP staan alleen op dat toestel.
  Wil je later accounts tussen toestellen delen, dan is een externe API nodig
  (bijvoorbeeld een aparte Laravel-backend met Sanctum).

## Plugins

| Plugin | Waarvoor |
|---|---|
| `nativephp/mobile-camera` | Galerijkiezer voor de afbeelding bij een activiteit (`Camera.PickMedia`) |

Plugins worden pas in de app meegebouwd als ze in `app/Providers/NativeServiceProvider.php` → `plugins()`
staan (`php artisan native:plugin:register <pakket>`). Een gewone `<input type="file">` werkt **niet** in de
Android-WebView van NativePHP; daarom gebruikt `resources/js/activities.js` in de app de native kiezer
(via `import('#nativephp')`, zie `imports` in `package.json`) en in de browser een gewone bestandsupload.

## Uitbreidingsideeën met native functies

NativePHP biedt PHP-API's voor native functies, bijvoorbeeld:
- lokale **notificaties** voor beweegherinneringen (de instellingen voor frequentie, stille uren en
  werktijden bestaan al in `localStorage`);
- **haptische feedback** bij het voltooien van een challenge;
- de **camera** voor een profielfoto.

Zie de NativePHP-documentatie voor de beschikbare facades en plugins (`php artisan native:plugin:list`).

## Publiceren

```bash
php artisan native:release            # versienummer ophogen
php artisan native:package android    # ondertekende .aab/.apk
php artisan native:package ios        # .ipa
```

Ondertekening (keystore, certificaten) wordt ingesteld met `php artisan native:credentials`.
