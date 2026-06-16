# LibraStack MVC

LibraStack MVC is a small PHP MVC book-management application. Users can register, log in, manage their profile, and keep a personal list of books with title, author, ISBN, search, sorting, pagination, autocomplete, detail, create, update, and delete flows.

The project is intentionally lightweight: there is no Composer setup, framework bootstrap, or build step. PHP files are loaded directly from the front controller in `index.php`, and data is stored in MySQL through PDO.

## Features

- User registration, login, logout, profile editing, and password reset token flow
- Session-based authentication
- CSRF protection for user forms and logout
- Per-user book ownership checks
- Book list search, sorting, pagination, and autocomplete
- Book create, detail, update, delete, and delete confirmation pages
- Bootstrap-based interface with local custom CSS

## Requirements

- PHP 8.0 or newer
- MySQL or MariaDB
- PHP PDO MySQL extension
- A web server that can serve PHP, or PHP's built-in development server

## Project Structure

```text
.
├── index.php                 # Front controller and route dispatcher
├── controllers/              # Request handlers
│   ├── BookController.php
│   └── UserController.php
├── models/                   # Database-backed domain models
│   ├── Book.php
│   └── User.php
├── views/                    # Page templates
│   └── users/                # Authentication and profile templates
├── inc/                      # Shared config, header, and footer
├── migrations/               # SQL migrations for user/account changes
├── css/                      # Application stylesheet
└── assets/                   # Logo and static assets
```

## Configuration

Create `inc/config.inc.php` locally. This file is ignored by Git because it contains database credentials.

Example:

```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('Europe/Amsterdam');

$db_hostname = 'localhost';
$db_username = 'your_user';
$db_password = 'your_password';
$db_database = 'your_database';
$charset = 'utf8mb4';

$dsn = "mysql:host=$db_hostname;dbname=$db_database;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $db_username, $db_password, $options);
} catch (PDOException $e) {
    echo "FOUT: geen connectie naar database. <br>";
    echo "Error message: " . $e->getMessage();
    exit();
}
```

## Database Setup

The application expects two tables:

- `users`
- `mvc_boeken`

For a fresh database, create `users` first:

```bash
mysql -u your_user -p your_database < migrations/001_create_users_table.sql
```

Then create `mvc_boeken`:

```sql
CREATE TABLE IF NOT EXISTS mvc_boeken (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  author VARCHAR(255) NOT NULL,
  isbn VARCHAR(50) NOT NULL,
  date_added DATETIME NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  CONSTRAINT mvc_boeken_ibfk_1
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
    ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

For an existing database that already has `mvc_boeken`, run the included migrations as needed. `migrations/002_add_user_id_to_mvc_boeken.sql` is currently empty, so add the column manually first if it does not exist:

```sql
ALTER TABLE mvc_boeken ADD COLUMN user_id BIGINT UNSIGNED NULL;
```

Then apply the type and foreign-key fixes:

```bash
mysql -u your_user -p your_database < migrations/003_alter_user_id_nullable.sql
mysql -u your_user -p your_database < migrations/004_fix_user_id_type.sql
```

Note: `004_fix_user_id_type.sql` drops a foreign key named `mvc_boeken_ibfk_1` before recreating it. If your database does not have that foreign key yet, create the fresh table definition above or adjust the migration for your local schema.

## Running Locally

From the project root:

```bash
php -S localhost:8000
```

Open:

```text
http://localhost:8000
```

Register a user first. Book pages require an authenticated session.

## Routes

User routes:

| Route | Method | Description |
| --- | --- | --- |
| `index.php?controller=user&action=login` | GET, POST | Show login form or log in |
| `index.php?controller=user&action=register` | GET, POST | Show register form or create account |
| `index.php?controller=user&action=profile` | GET, POST | Show or update the current user's profile |
| `index.php?controller=user&action=logout` | POST | Log out |
| `index.php?controller=user&action=forgot` | GET, POST | Request password reset |
| `index.php?controller=user&action=reset&token=...` | GET | Show password reset form |
| `index.php?controller=user&action=reset` | POST | Save new password |

Book routes:

| Route | Method | Description |
| --- | --- | --- |
| `index.php` | GET | List current user's books |
| `index.php?id=123` | GET | Show book details |
| `index.php?voegtoe=true` | GET, POST | Show create form or add a book |
| `index.php?pasaan=123` | GET, POST | Show update form or update a book |
| `index.php?verwijder=123` | GET, POST | Confirm or delete a book |
| `index.php?controller=book&action=autocomplete&q=...` | GET | Return JSON autocomplete suggestions |

## Security Notes

- Keep `inc/config.inc.php` out of version control.
- Use HTTPS in production so the session cookie can be sent securely.
- Passwords are stored with `password_hash()`.
- SQL queries use prepared PDO statements.
- Book list, edit, and delete operations are scoped to the logged-in user.
- Password reset currently creates a token but does not send email; integrate a mailer before using it in production.

## Development Notes

- There is no automated test suite in this repository yet.
- Bootstrap is loaded from a CDN in `inc/header.php`.
- The application contains Dutch UI labels and messages.
- The front controller keeps both newer `controller/action` routes and legacy query-string routes for books.
