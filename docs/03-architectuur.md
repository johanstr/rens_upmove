# 03 · Architectuur van de Laravel-versie

## Overzicht

```
app/
  Http/Controllers/
    AuthController.php        login, registratie, uitloggen
    HomeController.php        home-scherm
    ChallengeController.php   volgende challenge, challenge voltooien
    ActivityController.php    eigen activiteiten toevoegen, overzicht, detailpagina, afbeelding
    SettingsController.php    instellingenpagina
    AccountController.php     profiel, wachtwoord, account verwijderen
  Models/
    User.php                  + username, full_name, birth_date, xp
    Challenge.php
    ChallengeCompletion.php
    Activity.php
  Services/
    ChallengeService.php      90-minutenblokken, wissellimiet, XP toekennen
bootstrap/app.php             redirects voor gast/ingelogd + CSRF-afhandeling
database/migrations/          users, challenges, challenge_completions
resources/
  css/app.css                 Tailwind 4 (1-op-1 uit src/input.css)
  js/                         app-settings, footer-nav, login, home, settings-page, account-page
  views/
    layouts/app.blade.php     gedeelde <head> (vroeger components/header.php)
    components/footer-nav.blade.php
    auth/login.blade.php
    home.blade.php
    settings/index.blade.php
    settings/account.blade.php
routes/web.php
tests/Feature/                AuthTest, ChallengeTest, AccountTest, ActivityTest
```

## Routes

| Methode | URL | Naam | Middleware | Actie |
|---|---|---|---|---|
| GET | `/login` | `login` | guest | `AuthController@showLogin` |
| POST | `/login` | `login.store` | guest | `AuthController@login` |
| POST | `/register` | `register` | guest | `AuthController@register` |
| POST | `/logout` | `logout` | auth | `AuthController@logout` |
| GET | `/` | `home` | auth | `HomeController` (`?screen=home\|bewegen\|leren\|stats`) |
| POST | `/challenges/next` | `challenges.next` | auth | `ChallengeController@next` |
| POST | `/challenges/complete` | `challenges.complete` | auth | `ChallengeController@complete` |
| GET | `/activiteiten` | `activities.index` | auth | `ActivityController@index` (formulier + laatste 5) |
| POST | `/activiteiten` | `activities.store` | auth | `ActivityController@store` |
| GET | `/activiteiten/overzicht` | `activities.all` | auth | `ActivityController@all` (5 per pagina, alleen bij > 5 activiteiten) |
| GET | `/activiteiten/{activity}/afbeelding` | `activities.image` | auth | `ActivityController@image` (alleen eigenaar) |
| GET | `/settings` | `settings` | auth | `SettingsController@index` |
| GET | `/settings/account` | `account` | auth | `AccountController@show` |
| PUT | `/settings/account/profile` | `account.profile` | auth | `AccountController@updateProfile` |
| PUT | `/settings/account/password` | `account.password` | auth | `AccountController@updatePassword` |
| DELETE | `/settings/account` | `account.destroy` | auth | `AccountController@destroy` |

Een gast die `/` opent, gaat naar `/login`; een ingelogde gebruiker die `/login` opent, gaat naar `/`.

## Databaseschema

### `users`
| Kolom | Type | Opmerking |
|---|---|---|
| id | integer PK | |
| email | string(254), uniek | |
| username | string(30), uniek | `^[A-Za-z0-9_.-]{3,30}$` |
| password | string | bcrypt-hash (cast `hashed`) |
| full_name | string(150) | |
| birth_date | date | |
| xp | unsigned integer, standaard 0 | totaal verdiende XP |
| remember_token, timestamps | | Laravel-standaard |

### `challenges`
| Kolom | Type |
|---|---|
| id | integer PK |
| title | string |
| description | text, nullable |
| img | string (URL of pad onder `public/`) |
| kcal_burned | unsigned integer |
| difficulty | string (bv. "Makkelijk", "Gemiddeld", "Moeilijk") |
| xp_reward | unsigned integer |

### `challenge_completions`
| Kolom | Type |
|---|---|
| id | integer PK |
| user_id | FK → users, cascade delete |
| challenge_id | FK → challenges, cascade delete |
| challenge_interval | unsigned big integer (`intdiv(timestamp, 5400)`) |
| timestamps | |
| **uniek** | (`user_id`, `challenge_id`, `challenge_interval`) |

### `activities`
| Kolom | Type |
|---|---|
| id | integer PK |
| user_id | FK → users, cascade delete |
| title | string(150) |
| description | text (max. 2000 tekens) |
| image_path | string, nullable (pad op de `local`-schijf: `activities/{userId}/{uuid}.{ext}`) |
| timestamps | |

## Eigen activiteiten (`ActivityController`)

- Via de knop **Activiteiten** rechtsboven op home.
- Titel en beschrijving zijn verplicht, de afbeelding is optioneel (JPG, PNG, WebP, GIF of HEIC, max. 10 MB).
- Het overzicht onder het formulier toont de **laatste 5**. Bij **meer dan 5** verschijnt de link naar
  `/activiteiten/overzicht`: alle activiteiten met afbeelding en volledige beschrijving, **5 per pagina**.
- Afbeeldingen staan privé in `storage/app/private` en worden via `activities.image` geserveerd
  (alleen aan de eigenaar). Zo werkt het zonder `storage:link`, zowel in de browser als in de app.
- **Afbeelding kiezen**: in de browser een gewone bestandsupload; in de NativePHP-app de native
  galerijkiezer (`nativephp/mobile-camera`). Die geeft een pad op het toestel terug (`native_image_path`).
  Dat pad wordt alleen geaccepteerd in de app, alleen uit de map `Gallery/` van de plugin met de naam
  `gallery_selected_*`, en alleen als het een echte afbeelding is. Daarna wordt de tijdelijke kopie verwijderd.
- Bij het verwijderen van een account worden ook de afbeeldingen verwijderd.

## Bedrijfsregels (`ChallengeService`)

| Constante | Waarde | Betekenis |
|---|---|---|
| `INTERVAL_SECONDS` | 5400 | Een challenge-blok duurt 90 minuten |
| `DAILY_CHANGE_LIMIT` | 2 | Max. aantal keer per dag direct wisselen |

Sessiesleutels (identiek aan de vanilla-versie):

| Sleutel | Inhoud |
|---|---|
| `home_challenge` | `['interval' => int, 'id' => int]` |
| `manual_challenge_changes_{userId}` | `['date' => 'Y-m-d', 'count' => int]` |
| `challenge_notice` (flash) | melding bij wisselen |
| `challenge_completion_notice` (flash) | melding bij voltooien |
| `auth_message`, `auth_message_type`, `auth_mode` (flash) | meldingen op het loginscherm |
| `status_message`, `status_type`, `password_form_open` (flash) | meldingen op de accountpagina |

Voltooien gebeurt in één databasetransactie: completion-rij invoegen (een dubbele rij geeft
"Je hebt deze opdracht al voltooid.") en daarna `users.xp` ophogen.

## Client-side (ongewijzigd gedrag)

| Bestand | Doel | localStorage-sleutels |
|---|---|---|
| `app-settings.js` | dark mode toepassen, `window.UpMoveSettings` | `upmove:dark-mode`, `upmove:work-hours` |
| `settings-page.js` | instellingenpagina | `upmove:settings:{userId}` |
| `home.js` | melding wegklikken + aftelklok | `project-in-beweging:alert-dismissed-until:{userId}` |
| `footer-nav.js` | schermwissel zonder herladen | – |
| `login.js` | login ↔ registratie | – |
| `account-page.js` | verwijderdialoog, wachtwoordcontrole | – |
| `activities.js` | afbeelding kiezen (native galerij of bestandsupload + voorbeeld) | – |

`app-settings.js` wordt in de `<head>` geladen (niet als module), zodat dark mode wordt toegepast
vóórdat de pagina wordt getekend, net als vroeger.
