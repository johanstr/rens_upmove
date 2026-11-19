# UpMove (project-in-beweging)

Jongeren die leren voor een zittend beroep, zoals programmeren, lopen risico op gezondheidsproblemen door
langdurig zitten. UpMove stimuleert gezonde gewoontes door leerdoelen te combineren met korte beweegpauzes.

Gebouwd met **Laravel 13**, **Tailwind CSS 4** en **NativePHP Mobile 4** (iOS + Android).

## Installeren

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed     # maakt database/database.sqlite + testaccount
```

Testaccount: gebruikersnaam `test` (of `test@example.com`), wachtwoord `password`.

## Ontwikkelen in de browser

```bash
php artisan serve      # http://127.0.0.1:8000
npm run dev            # Vite + Tailwind met hot reload (altijd aan laten staan tijdens het coderen)
```

Of alles tegelijk: `composer run dev`.

## Draaien als mobiele app

```bash
php artisan native:install     # eenmalig, kies android/ios
npm run build
php artisan native:run
```

Zie [docs/05-nativephp-mobile.md](docs/05-nativephp-mobile.md) voor de vereisten en alle commando's.

## Testen

```bash
php artisan test
```

## Iconen

Wil je een icoon gebruiken, gebruik dan `<i class="iconoir-naam"></i>` (te vinden op https://iconoir.com/).
Zie [docs/04-styling.md](docs/04-styling.md) voor het offline gebruik in de app.

## Documentatie

| Document | Inhoud |
|---|---|
| [01-analyse-vanilla-php.md](docs/01-analyse-vanilla-php.md) | Hoe de oude vanilla PHP-versie werkte en welke problemen erin zaten |
| [02-migratieplan.md](docs/02-migratieplan.md) | Mapping oud → nieuw en gemaakte keuzes |
| [03-architectuur.md](docs/03-architectuur.md) | Routes, controllers, database, bedrijfsregels |
| [04-styling.md](docs/04-styling.md) | Tailwind-thema, dark mode, werken aan de styling |
| [05-nativephp-mobile.md](docs/05-nativephp-mobile.md) | De app bouwen en draaien op iOS/Android |
| [06-git-workflow.md](docs/06-git-workflow.md) | Branches, pull requests en merges in het team |
