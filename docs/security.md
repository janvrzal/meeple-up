# Security Architecture

This document describes the security mechanisms implemented in the application and provides explanations of the design decisions, including why alternative solutions were not chosen.

---

## 1. Authentication and Session Security

### Password Hashing
The application secures user passwords using a two-stage hashing mechanism:
1. The plaintext password is first hashed using `hash_hmac` with the SHA-256 algorithm and a server-side secret key (the pepper).
2. The output is then passed to PHP's native `password_hash` function, which uses the bcrypt algorithm by default (with auto-generated salts).

#### Rationale
If a database leak occurs, attackers can obtain the password hashes. If a weak password is used, they can easily brute-force the bcrypt hashes using offline tools. By mixing in a server-side pepper stored in the `.env` configuration file (which is excluded from version control and the database), an attacker cannot verify hash matches even if they acquire the entire database schema and records. They would need to compromise the application server itself to retrieve the pepper.

#### Alternatives Considered
* **Standard bcrypt without a pepper (`password_hash` only):** This was rejected because if the database is compromised, there is no defense-in-depth for weak user passwords against offline brute-forcing.
* **Storing a single global salt in the database:** This was rejected because a database leak exposes both the hash and the salt, rendering the salt useless for preventing multi-target attacks.
* **MD5 or SHA-256 without bcrypt (or using simple crypt):** This was rejected because these algorithms are too fast, allowing attackers to check billions of hashes per second. Bcrypt includes a configurable cost factor that makes hashing computationally expensive.

### Session Management
User sessions are managed through PHP's native session handler. Upon successful authentication in `Auth::login()`, the session identifier is regenerated using `session_regenerate_id(true)`.

#### Rationale
This prevents session fixation attacks. In a session fixation attack, an attacker obtains a valid session ID (e.g., from their own session or by forcing one via a query parameter) and induces a victim to use it. If the application does not change the session ID upon login, the attacker can hijack the authenticated session. Regenerating the ID invalidates the old session ID and issues a new one.

---

## 2. Cross-Site Request Forgery (CSRF) Protection

All state-changing operations (HTTP POST requests) require a valid CSRF token. The `Csrf` core class generates a cryptographically secure token using `random_bytes(32)` and stores it in `$_SESSION['csrf_token']`. Form submissions include this token in a hidden input field, and controllers verify it using the `verifyCsrf()` helper before executing the request.

### Token Verification
The verification uses `hash_equals()` to compare the submitted token with the token stored in the session.

#### Rationale
Cross-Site Request Forgery allows malicious websites to submit actions to our application on behalf of a logged-in user. Including a unique, secret token that a third-party site cannot read blocks this attack. Using `hash_equals()` is crucial because it performs a constant-time string comparison. 

#### Alternatives Considered
* **Standard string comparison (`===`):** This was rejected because standard comparisons exit early upon finding the first mismatched character. This introduces timing side-channels, allowing an attacker to reconstruct the token character by character by measuring the server response times.
* **Double-submit cookies (stateless CSRF):** This was rejected because the application is stateful and already uses server-side sessions. Session-based CSRF is more secure since it does not rely on browser cookie policies (like SameSite) being configured perfectly across all browsers.

---

## 3. SQL Injection Prevention

Database communication is handled through PDO (PHP Data Objects). The application strictly uses prepared statements with parameter binding (e.g., `execute(['param' => $value])`).

### Rationale
Prepared statements separate the query structure from the data. The database engine compiles the SQL query template first, and then inserts the parameter values directly into the execution path. This guarantees that user-provided strings are treated strictly as data literals and never interpreted as executable SQL commands.

#### Alternatives Considered
* **Manual string escaping (`addslashes` or `mysqli_real_escape_string`):** This was rejected because escaping is error-prone, database-specific, and fails to handle certain character encodings or numeric contexts (where quotes are not used).
* **Using an ORM (like Eloquent or Doctrine):** This was rejected to keep the project zero-dependency and lightweight, and to demonstrate a fundamental understanding of raw SQL and database mechanics.

---

## 4. Cross-Site Scripting (XSS) Prevention

All user-generated text rendered in PHP templates is escaped using the `htmlspecialchars()` function. The default configuration uses the UTF-8 charset and escapes both double and single quotes (`ENT_QUOTES`).

### Rationale
Escaping converts characters like `<` and `>` into HTML entities (`&lt;` and `&gt;`). This prevents the browser from interpreting user input as executable JavaScript.

#### Alternatives Considered
* **Sanitizing input before storing it in the database:** This was rejected because it is a security anti-pattern. If raw input is sanitized on entry, it is permanently corrupted. If the application later wants to display the data in a non-HTML context (e.g., sending an email, exporting to a CSV, or serving a JSON API), the HTML entities would appear as raw text. Storing raw data and escaping it contextually at render time is the industry standard.
* **Markdown parsing without escaping:** This was rejected because it introduces a massive attack surface. The current architecture uses simple plain text for comments and descriptions, ensuring that escaping handles all safety concerns.

---

## 5. Access Control and Authorization

Authorization is enforced at the controller level using explicit base controller helper methods:
* `requireLogin()`: Redirects anonymous users to the login screen.
* `requireGuest()`: Redirects authenticated users away from public-only pages (like login/register).
* `requireOwner(int $ownerId)`: Restricts an action to the resource owner or an administrator.
* `requireAdmin()`: Restricts an action to users with the `admin` role.

### Rationale
Enforcing authentication at the start of each controller action provides fine-grained control and prevents unauthorized access. If an action is missed, the default state is open, but the codebase uses explicit checks on every sensitive endpoint.

#### Alternatives Considered
* **Route-level middleware:** This was rejected because the custom router is lightweight and does not support nested middleware pipelines. Defining checks directly inside the controller methods keeps the logic transparent, easy to debug, and requires no complex configuration routing.
