# Inventory Management System

Welcome to the **Inventory Management System**. This application is built using the robust and modern [Laravel](https://laravel.com/) framework combined with [Livewire](https://livewire.laravel.com/) for dynamic, modern full-stack interfaces, and [Flux](https://fluxui.dev/) for UI components.

## Table of Contents

- [Overview](#overview)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage Manual](#usage-manual)
- [Development & Architecture](#development--architecture)
- [Testing](#testing)

---

## Overview

This project is an **Inventory Management** solution intended to help businesses track and manage items, stocks, and operations securely. It leverages Laravel's elegant routing and ORM alongside Laravel Livewire to offer a reactive Single Page Application (SPA)-like experience without needing a complex frontend setup.

Features include:

- Secure Authentication via Laravel Fortify.
- Real-time responsive UI driven by Livewire.
- Clean and consistent components via Flux UI.
- Comprehensive Testing suite via PestPHP.

---

## Requirements

Ensure your server meets the following requirements:

- **PHP:** ^8.4
- **PHP Extensions:** Ensure `tokenizer`, `session`, `fileinfo`, `dom`, `xml`, `xmlwriter`, and `simplexml` are enabled in your `php.ini` (along with typical Laravel extensions like `curl`, `mbstring`, `openssl`, `zip`, etc.).
- **Composer:** Latest version
- **Node.js & NPM:** Latest LTS version (for compiling frontend assets)
- **Database:** SQLite (default/dev), MySQL, or PostgreSQL.
- **Web Server:** Nginx, Apache, or Laravel's built-in server.

---

## Installation

You can get the project up and running quickly by using Composer and Artisan commands.

1. **Clone the repository:**

    ```bash
    git clone <repository-url>
    cd inventory-management
    ```

2. **Install PHP dependencies:**

    Ensure you have the required PHP extensions installed. For Alpine Linux (e.g., inside Docker), run:
    ```bash
    apk add php84-tokenizer php84-session php84-dom php84-fileinfo php84-xml php84-xmlwriter php84-simplexml
    ```

    Then, install the Composer dependencies:
    ```bash
    composer install
    ```

3. **Install Frontend dependencies & Build:**

    ```bash
    npm install
    npm run build
    ```

4. **Environment Setup:**
   Duplicate the `.env.example` to `.env`:

    ```bash
    cp .env.example .env
    ```

    Generate the application key:

    ```bash
    php artisan key:generate
    ```

5. **Database Setup:**
   By default, you can use SQLite for local development. Configure your `.env` database block if using MySQL/PGSQL. Ensure the database exists, then run migrations:

    ```bash
    php artisan migrate
    ```

6. **Quick Setup Command:**
   Alternatively, you can run the Composer `setup` script which handles the above steps automatically:
    ```bash
    composer setup
    ```

---

## Usage Manual

### Starting the Application

For a complete local development environment (serves the app, processes queues, streams logs, and watches for asset changes), run:

```bash
composer dev
```

You can now access the application in your browser (typically at `http://127.0.0.1:8000`).

### Navigating the System

1. **Registration & Login**
    - Navigate to the `/register` route to create an administrative or user account.
    - Navigate to `/login` to access the system secure dashboard.

2. **Inventory Dashboard**
    - The dashboard acts as the command center, providing key metrics on stock levels, low-inventory alerts, and recent activities.

3. **Item Management (Adding/Editing)**
    - Utilize the items module to seamlessly 'Add' new inventory items.
    - Use Livewire-powered modals to edit item details dynamically (SKU, Name, Quantity, Price) without page reloads.

4. **Settings & Profile**
    - Manage your account settings and application configurations via the dynamically rendered Settings panel.

---

## Development & Architecture

### Tech Stack

- **Backend:** Laravel ^13.0
- **Frontend / reactivity:** Livewire ^4.2 + Flux UI
- **Authentication:** Laravel Fortify ^1.34

### Project Structure

- `app/Livewire`: Contains all the component logic that interacts with your Blade views.
- `resources/views/livewire`: Holds the Blade templates corresponding to the Livewire components.
- `app/Models`: Database logic mapped via Eloquent ORM.
- `routes/web.php`: Standard web routing definitions.

### Linting

To keep code formatting clean and consistent with Laravel standards (using Laravel Pint):

```bash
composer lint
```

---

## Testing

The project uses [Pest](https://pestphp.com/) as the testing framework.

To execute the test suite, ensure your initial configuration is set, then simply run:

```bash
composer test
```

_Note: This command clears the configuration cache, lints the codebase, and runs all Feature and Unit tests._

---

_This documentation covers the baseline for the platform. As the Inventory features are expanded, this manual should be updated to reflect current database schemas and business logic flows._
