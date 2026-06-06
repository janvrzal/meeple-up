# 🎲 Roll Call

**Roll Call** je webová aplikace pro organizování a domlouvání sezení deskových her.
Uživatelé mohou vytvářet herní sezení, zvát ostatní hráče a přihlašovat se na sezení
ostatních — ať už v rámci uzavřené skupiny přátel, nebo širší komunity hráčů hledajících
spoluhráče.

> Semestrální projekt (VŠE). Postaveno v čistém PHP bez frameworku.

---

## ✨ Funkce

- **Účty** — registrace, přihlášení, odhlášení; hesla hashovaná (`password_hash` + salt + pepper).
- **Herní sezení** — vytvoření / úprava / mazání (datum, čas, místo, max. počet hráčů, hra, popis).
- **Přihlašování na sezení** — join / leave, počítání volných míst.
- **Soukromá sezení** — approval systém: tvůrce schvaluje žádosti o účast.
- **Komentáře** — chat-style diskuze pod sezením, viditelná jen pro účastníky.
- **Katalog her (BGG)** — našeptávač her z [BoardGameGeek](https://boardgamegeek.com),
  data se cachují lokálně do DB.
- **Přehled sezení** — filtrování podle hry, lokace a volných míst.
- **Dashboard** — moje sezení, žádosti ke schválení, statistiky.
- **Export do kalendáře** — stažení `.ics` i přímý odkaz do Google Calendar.
- **Vzhled** — světlý/tmavý režim, responzivní UI.

---

## 🛠️ Technologie

| Vrstva        | Technologie                                  |
|---------------|----------------------------------------------|
| Backend       | PHP 8.3 (čisté, bez frameworku), PDO         |
| Databáze      | MySQL / MariaDB                              |
| Frontend      | Tailwind CSS + DaisyUI, Tabler Icons         |
| Externí data  | BGG XML API2 + oficiální data dump (katalog) |
| Architektura  | MVC, vlastní router (front controller)       |

---

## 🚀 Instalace a spuštění

### Požadavky
- PHP 8.3+ (rozšíření: `pdo_mysql`, `simplexml`, `mbstring`)
- MySQL / MariaDB
- Webový server (Apache s `mod_rewrite`)

### Kroky

1. **Naklonuj repozitář** a nasměruj web root serveru do složky `public/`
   (vše ostatní — `app/`, `.env` — musí zůstat mimo veřejný dosah).

2. **Vytvoř `.env`** zkopírováním šablony a vyplň údaje:
   ```bash
   cp .env.example .env
   ```
   ```ini
   DB_HOST=localhost
   DB_NAME=rollcall
   DB_USER=...
   DB_PASS=...
   DB_CHARSET=utf8mb4
   APP_ENV=local            # 'local' zobrazuje chyby, 'production' je skrývá
   PEPPER=...               # náhodný řetězec, viz níže
   BGG_SOURCE=catalog       # 'catalog' (lokální) nebo 'api' (živé BGG, vyžaduje token)
   BGG_TOKEN=               # Bearer token z boardgamegeek.com/applications
   ```
   Vygenerování `PEPPER`:
   ```php
   echo bin2hex(random_bytes(32));
   ```

3. **Vytvoř databázi a naimportuj schéma:**
   ```sql
   CREATE DATABASE rollcall CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
   Naimportuj `app/sql/schema.sql` (např. přes phpMyAdmin nebo `mysql < app/sql/schema.sql`).

4. **Naplň katalog her** — naimportuj podmnožinu BGG data dumpu (`bg_ranks.csv`)
   do tabulky `bgg_catalog` (viz [docs/architecture.md](docs/architecture.md#bgg-pipeline)).

5. Hotovo — otevři appku v prohlížeči.

---

## 📁 Struktura projektu

```
.
├── public/                 # web root (jediná veřejná složka)
│   ├── index.php           # front controller (vstupní bod)
│   ├── .htaccess           # rewrite všech requestů do index.php
│   └── assets/             # statické soubory (maskot…)
├── app/
│   ├── config/             # config.php (čte .env)
│   ├── sql/                # schema.sql
│   ├── src/
│   │   ├── Core/           # Router, Database, Controller, Model, Auth, Csrf, Avatar
│   │   ├── Controllers/    # HTTP akce (jeden controller na zdroj)
│   │   ├── Models/         # přístup k datům (jeden model na tabulku)
│   │   └── Services/       # business logika (BGG zdroje, dashboard)
│   └── views/              # PHP šablony
├── docs/                   # podrobná dokumentace
└── .env                    # konfigurace (NENÍ v gitu)
```

> Poznámka: dle hostingu může být `public/` mapované jinak — klíčové je, že web server
> míří do složky s `index.php` a `app/` je mimo veřejný dosah.

---

## 📚 Další dokumentace

- **[docs/architecture.md](docs/architecture.md)** — MVC tok, class diagram, BGG pipeline, přehled rout.
- **[docs/database.md](docs/database.md)** — ER diagram a popis tabulek.

---

## 🔐 Bezpečnost (shrnutí)

- **Hesla**: `password_hash` (bcrypt) + per-uživatelský salt + aplikační pepper.
- **CSRF**: token ve všech formulářích, ověřovaný u POST akcí.
- **XSS**: veškerý uživatelský výstup přes `htmlspecialchars`.
- **SQL injection**: výhradně prepared statements (PDO).
- **Autorizace**: `requireLogin` / `requireOwner` / `requireAdmin` na úrovni controllerů.
- **Konfigurace**: tajemství (`.env`, `PEPPER`, `BGG_TOKEN`) mimo git i mimo web root.

---

## 📝 Poznámka k BGG API

BGG od poloviny 2025 vyžaduje pro XML API registraci aplikace a autorizační token
(se schvalováním řádově v týdnech). Aplikace je proto navržena se **dvěma zdroji dat
za společným rozhraním** (`GameSource`):

- **`catalog`** — vyhledávání z lokálně naimportovaného BGG data dumpu (funguje bez tokenu),
- **`api`** — živé volání BGG XML API2 (po schválení tokenu).

Přepnutí je otázkou jediné hodnoty v `.env` (`BGG_SOURCE`). Detaily v
[docs/architecture.md](docs/architecture.md#bgg-pipeline).
