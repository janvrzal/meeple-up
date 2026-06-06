# Databázové schéma

Databáze je navržená do 3. normální formy. Všechny tabulky používají `InnoDB`
(kvůli cizím klíčům a transakcím) a kódování `utf8mb4`.

## ER diagram

```mermaid
erDiagram
    users ||--o{ sessions          : "vytváří (creator)"
    users ||--o{ tournaments       : "vytváří"
    users ||--o{ participations    : "účastní se"
    users ||--o{ comments          : "píše"
    users ||--o{ session_ratings   : "hodnotí"
    users ||--o{ user_favorite_games : "oblíbil si"

    locations ||--o{ sessions      : "hostí"
    games ||--o{ sessions          : "hraje se (nullable)"
    games ||--o{ tournaments       : "téma turnaje"
    games ||--o{ user_favorite_games : "je oblíbená"

    tournaments ||--o{ sessions     : "seskupuje (nullable)"

    sessions ||--o{ participations : "má účastníky"
    sessions ||--o{ comments       : "má komentáře"
    sessions ||--o{ session_ratings : "je hodnoceno"

    users {
        int id PK
        varchar username UK
        varchar email UK
        varchar password_hash
        enum role "user | admin"
        varchar city "nullable"
        timestamp created_at
    }
    locations {
        int id PK
        varchar name
        varchar address "nullable"
        varchar city
        varchar place_id "nullable (budoucí Google)"
        decimal lat "nullable"
        decimal lng "nullable"
        timestamp created_at
    }
    games {
        int id PK
        int bgg_id UK
        varchar name
        smallint year_published "nullable"
        tinyint min_players "nullable"
        tinyint max_players "nullable"
        smallint playing_time "nullable"
        varchar thumbnail_url "nullable"
        text description "nullable"
        timestamp cached_at
    }
    tournaments {
        int id PK
        int game_id FK
        varchar name
        int creator_id FK
        text description "nullable"
        timestamp created_at
    }
    sessions {
        int id PK
        int creator_id FK
        int game_id FK "nullable"
        int tournament_id FK "nullable"
        int location_id FK
        enum status "open | cancelled | finished"
        varchar title
        datetime scheduled_at
        tinyint max_players "nullable = bez limitu"
        tinyint is_private
        text description "nullable"
        timestamp created_at
    }
    participations {
        int id PK
        int user_id FK
        int session_id FK
        enum status "pending | approved"
        timestamp created_at
    }
    comments {
        int id PK
        int session_id FK
        int user_id FK
        text body
        timestamp created_at
    }
    user_favorite_games {
        int user_id PK_FK
        int game_id PK_FK
        timestamp created_at
    }
    session_ratings {
        int id PK
        int session_id FK
        int user_id FK
        tinyint rating "1-5"
        text comment "nullable"
        timestamp created_at
    }
    bgg_catalog {
        int bgg_id PK
        varchar name
        smallint year_published "nullable"
        int rank "nullable"
        tinyint is_expansion
    }
```

## Popis tabulek

### Jádro

| Tabulka          | Účel                                                                 |
|------------------|----------------------------------------------------------------------|
| `users`          | Uživatelské účty. `role` rozlišuje běžného uživatele a admina.        |
| `locations`      | Místa konání sezení (sdílená, základ pro filtrování). Sloupce `place_id/lat/lng` jsou připravené pro budoucí napojení na mapy. |
| `games`          | **Lokální cache** dat her z BGG. `bgg_id` je unikátní — hra se z BGG stáhne jen při prvním použití. |
| `sessions`       | Herní sezení. `max_players = NULL` znamená bez limitu; `is_private` zapíná approval. |
| `participations` | M:N vazba uživatel ↔ sezení. `UNIQUE(user_id, session_id)` brání dvojí účasti; `status` řeší schvalování. |
| `comments`       | Komentáře/zprávy pod sezením.                                         |

### Rozšíření

| Tabulka               | Účel                                                            |
|-----------------------|-----------------------------------------------------------------|
| `tournaments`         | Seskupení více sezení pod jeden turnaj (ukotvený na jednu hru). |
| `user_favorite_games` | Oblíbené hry uživatele (M:N, kompozitní PK).                    |
| `session_ratings`     | Hodnocení sezení po skončení (1–5), `UNIQUE(session_id, user_id)`. |

### Pomocná

| Tabulka       | Účel                                                                          |
|---------------|-------------------------------------------------------------------------------|
| `bgg_catalog` | Vyhledávací index ~30 000 her z oficiálního BGG data dumpu. Slouží jako zdroj dat pro našeptávač bez nutnosti volat BGG API. Není provázán cizími klíči — je to samostatný read-only index. |

## Klíčová návrhová rozhodnutí

- **Volná místa se nepočítají do sloupce** — odvozují se za běhu jako
  `max_players − COUNT(participations)`. Jeden zdroj pravdy, žádná desynchronizace.
- **`ON DELETE` strategie**: závislé entity (účasti, komentáře) kaskádují s rodičem;
  lokace mají `RESTRICT` (nelze smazat používanou lokaci); hra v sezení má `SET NULL`
  (smazání hry z cache nezruší historii sezení).
- **Cache klíčovaná `bgg_id`** (`UNIQUE`) — jádro mechanismu „stáhni jen jednou".
