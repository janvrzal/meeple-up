# Roll Call - Board Game Session Organizer

Roll Call (also referred to as Meeple Up) is a web application designed for organizing and coordinating board game sessions. Users can schedule game sessions, invite friends, sign up for sessions, coordinate tournaments, and manage a collection of their favorite games. The application is suited for closed groups of friends as well as broader gaming communities looking for new players.

This project was built from scratch in vanilla PHP without external frameworks as part of a university semester project (VŠE).

---

## 1. Features

* **Account Management:** User registration and secure authentication. Password hashes are protected using bcrypt combined with an application-level secret pepper.
* **Game Session Coordination:** CRUD (Create, Read, Update, Delete) operations for gaming sessions including scheduling details, max player counts, locations, and descriptions.
* **Access Control and Approvals:** Sessions can be public (anyone can join instantly) or private (requiring the creator's approval to join).
* **Discussion Comments:** Section-restricted discussion boards under each session, visible only to approved participants.
* **BoardGameGeek Integration:** Game auto-completion utilizing BoardGameGeek (BGG) metadata. Uses a dual-pipeline strategy (local catalog database file vs live BGG API) and caches retrieved games.
* **Tournaments:** Users can organize competitive board game tournaments, grouping multiple game sessions together, and users can register as tournament participants.
* **Favorites System:** Users can add board games to their favorite games collection to quickly select them when creating new sessions.
* **Calendar Export:** Downloadable iCalendar (.ics) files and direct Google Calendar creation links for scheduled sessions.
* **User Profiles:** Personalized settings allowing users to update their home city and securely change their passwords.
* **Responsive Styling:** Clean light and dark themes utilizing Tailwind CSS and DaisyUI components.

---

## 2. Technology Stack

| Layer | Technology |
|---|---|
| **Backend** | PHP 8.3 (Vanilla, zero external framework dependencies), PDO |
| **Database** | MySQL / MariaDB (InnoDB storage engine, UTF-8 charset) |
| **Frontend** | Tailwind CSS, DaisyUI component library, Tabler Icons, Vanilla JavaScript |
| **Integrations** | BoardGameGeek XML API2, iCalendar (.ics) RFC 5545 |
| **Architecture** | Custom MVC architecture with a Front Controller and regex routing |

---

## 3. Directory Structure

```
.
├── app/                        # Main application files (protected from web access)
│   ├── config/                 # Configuration files (config.php which parses .env)
│   ├── sql/                    # SQL schema setup script (schema.sql)
│   ├── src/                    # Source classes mapped to PHP autoloading
│   │   ├── Core/               # Infrastructure: Router, Database, Auth, Csrf, Controllers, Models
│   │   ├── Controllers/        # Thin HTTP controllers handling requests
│   │   ├── Models/             # Database table access models
│   │   └── Services/           # Services orchestrating logic across multiple models
│   └── views/                  # PHP templates and HTML views
├── assets/                     # Publicly accessible static assets
├── docs/                       # Technical documentation
│   ├── architecture.md         # Request lifecycles, class design, and routing tables
│   ├── database.md             # ER diagrams, table schemas, and normalization rules
│   ├── security.md             # Password hashing, CSRF tokens, XSS, and SQLi defenses
│   └── bgg_integration.md      # Strategy patterns, local catalog, and API caching details
├── .env.example                # Example configuration values template
├── .htaccess                   # Rewrite engine configuration for Apache URL routing
├── clear.php                   # Diagnostic script to clear PHP OPcache
├── diag.php                    # Diagnostic inspection script
├── import_catalog.php          # Command line / web script to import BGG CSV catalog
└── index.php                   # Front controller (web application entry point)
```

---

## 4. Installation and Setup

### Prerequisites
* PHP 8.3 or higher with extensions: `pdo_mysql`, `simplexml`, `mbstring`.
* MySQL or MariaDB database server.
* Web server (Apache with `mod_rewrite` enabled).

### Setup Steps

1. **Clone the Repository:**
   Place the project files in your web server's directory.

2. **Configure Environment Variables:**
   Create a `.env` file by copying the template file:
   ```bash
   cp .env.example .env
   ```
   Edit the `.env` file and fill in your environment variables:
   ```ini
   DB_HOST=localhost
   DB_NAME=rollcall
   DB_USER=your_db_username
   DB_PASS=your_db_password
   DB_CHARSET=utf8mb4
   APP_ENV=local            # Use 'local' to display errors; 'production' to hide them
   PEPPER=your_secret_hash   # Secure key for password hashing
   BGG_SOURCE=catalog       # Use 'catalog' for offline SQL search or 'api' for live BGG API
   BGG_TOKEN=               # BGG API Bearer Token (optional, required if BGG_SOURCE=api)
   ```
   Generate a cryptographically secure `PEPPER` key using PHP:
   ```php
   echo bin2hex(random_bytes(32));
   ```

3. **Initialize the Database Schema:**
   Create a new MySQL database:
   ```sql
   CREATE DATABASE rollcall CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
   Import the SQL schema file found at `app/sql/schema.sql`. You can use PHPMyAdmin or the MySQL command-line tool:
   ```bash
   mysql -u your_username -p rollcall < app/sql/schema.sql
   ```

4. **Populate the Board Game Catalog:**
   Place the BoardGameGeek CSV ranking data dump (`bg_ranks.csv`) in the root directory. Run the import script to populate the local lookup index:
   ```bash
   php import_catalog.php
   ```

5. **Access the Application:**
   Open the application URL in your web browser.

---

## 5. Architectural Design Decisions

### Web Root Layout and Directory Structure
* **Design Decision:** The front controller `index.php` is located directly in the root directory rather than inside a nested `public/` directory. Access to the core source code directory `app/` is blocked by an Apache directive (`Require all denied`) in `app/.htaccess`.
* **Rationale:** In standard professional environments, the web server's document root points to a nested `public/` directory, completely isolating the source code from web access. However, many shared hosting environments (including student accounts on university servers such as `vse.cz`) map URL paths directly to directories like `public_html/` and do not allow custom document root adjustments. Placing `index.php` at the root and securing the `app/` folder with `app/.htaccess` guarantees security while allowing out-of-the-box deployment on simple shared hosting setups.

---

## 6. Comprehensive Documentation Links

For deeper dives into technical segments, refer to:
* **[Architecture Guide (docs/architecture.md)](docs/architecture.md):** Detailed explanations of the request lifecycle, the custom MVC layers, class hierarchy diagrams, and the full HTTP routing table.
* **[Database Documentation (docs/database.md)](docs/database.md):** Entity-relationship diagrams, table fields, foreign key mappings, and database normalization choices.
* **[Security Overview (docs/security.md)](docs/security.md):** Information regarding bcrypt password hashing with server-side pepper, session fixation prevention, CSRF tokens, XSS output sanitization, and SQL injection security.
* **[BGG Integration (docs/bgg_integration.md)](docs/bgg_integration.md):** Strategy patterns for game catalog querying, caching schemas, and API authentication rules.
