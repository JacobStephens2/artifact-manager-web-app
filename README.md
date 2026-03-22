# Artifact Manager

A web app for tracking artifact usage and operationalizing the [Minimalists' 90/90 Rule](https://www.theminimalists.com/ninety/) — if you haven't used something in the last 90 days and won't in the next 90, it's time to let it go.

Live at [artifact.stewardgoods.com](https://artifact.stewardgoods.com/).

## Tech Stack

- **Backend**: PHP / MySQL (MySQLi) / Apache
- **Frontend**: Vanilla HTML/CSS/JavaScript
- **Auth**: JWT tokens + session-based auth with bcrypt
- **Email**: PHPMailer via SendGrid
- **Testing**: PHPUnit 10
- **Dependencies**: Composer (`firebase/php-jwt`, `phpmailer/phpmailer`)

## Features

### Artifact Tracking

- Create and manage artifacts across types: board games, books, films, equipment, toys, instruments, food, drinks, and more
- Track acquisition dates, player counts, complexity, ratings
- Organize into primary/secondary collections or archive
- Candidate/exploration status for items under consideration

### Interaction Logging

- Record usage events with dates, notes, and participants
- Multi-player support per interaction
- Sweet spot tracking (ideal player counts per artifact)
- Per-artifact frequency overrides

### Usage Analysis

- Calculate "use-by" dates based on configurable interaction frequency
- List artifacts ordered by next due date to prioritize what to use next
- Default and per-artifact interval settings

### Player & Playgroup Management

- Player profiles with priority and menu ordering
- Playgroup selection for defining active participants
- Aversion tracking

### REST API

- JWT + API key authentication
- CRUD endpoints for artifacts, uses, types, and users
- Cursor-based and offset-based pagination
- Search and filtering
- Rate limiting (60 requests/minute per IP)
- Structured request logging

### Multi-Tenant

- Each user manages their own artifacts, players, and data
- User roles (regular, admin)
- Registration, login, and password reset flows

## Project Structure

```
ui/                         Web-accessible frontend
├── artifacts/              Artifact CRUD pages
├── uses/                   Interaction recording
├── players/                Player management
├── playgroup/              Playgroup selection
├── types/                  Artifact type management
├── users/                  User management
├── settings/               User settings
├── explore/                Candidate exploration
├── aversions/              Aversion tracking
├── shared/                 Header/footer templates
└── style.css               Global stylesheet

api/                        REST API endpoints
├── artifact.php            Single artifact CRUD
├── artifacts.php           Artifact listing/search
├── uses.php                Interaction recording
├── types.php               Artifact types
├── users.php               User management
└── private/                API-specific initialization

private/                    Backend logic (not web-accessible)
├── initialize.php          App bootstrap & constants
├── database.php            Database connection
├── environment_variables.php  Config (git-ignored)
├── functions.php           CSRF & utility helpers
├── validation_functions.php   Input validation
├── auth_functions.php      Authentication logic
├── rate_limiter.php        Rate limiting
├── cache.php               File-based caching
├── app_logger.php          Structured logging
├── classes/                OOP data access layer
│   ├── DatabaseObject.class.php
│   ├── Artifact.class.php
│   └── User.class.php
├── query_functions/        Domain-specific query modules
├── crons/                  Scheduled tasks
└── oneTimeScripts/         Migration scripts

tests/                      PHPUnit test suite
docs/                       DATABASE_SCHEMA.md with ERD
```

## Setup

### Prerequisites

- PHP 7.x+ with MySQLi extension
- MySQL / MariaDB
- Apache with mod_rewrite
- Composer

### Installation

```bash
# Install dependencies
composer install
cd private && composer install
cd ../api/private && composer install

# Configure environment
cp private/environment_variables_template.php private/environment_variables.php
# Edit with your DB credentials, SendGrid key, JWT secret, etc.

# Set up database (see docs/DATABASE_SCHEMA.md)

# Ensure logs/ and cache/ are writable
chmod 777 logs/ cache/
```

### Configuration

`private/environment_variables.php` defines:

| Constant | Purpose |
|---|---|
| `DB_SERVER`, `DB_USER`, `DB_PASS`, `DB_NAME` | Database connection |
| `SENDGRID_API_KEY` | Email delivery |
| `ARTIFACTS_API_KEY` | API authentication key |
| `JWT_SECRET` | JWT signing key |
| `ARTIFACTS_DOMAIN` | Application domain |
| `API_ORIGIN` | API domain for CORS |
| `APP_ENV` | `development` or `production` |

### Running Tests

```bash
./vendor/bin/phpunit
```

## Acknowledgments

The following LinkedIn Learning courses by Kevin Skoglund were very helpful in the development of this app: _PHP Essential Training_, _PHP with MySQL Essential Training: 1 The Basics_, and _PHP with MySQL Essential Training 2: Build a CMS_.
