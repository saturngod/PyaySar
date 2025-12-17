# Pyaysar - Invoice Management System

## Project Overview

**Pyaysar** is a modern invoice management application built with the **Laravel 12** framework and a **React 19** frontend using **Inertia.js 2.0**. It provides a robust platform for managing customers, inventory items, and invoices, complete with status tracking and PDF generation capabilities.

### Key Features
*   **Dashboard:** Overview of business metrics.
*   **Invoice Management:** Create, update, and track invoices with status history.
*   **Customer Management:** Maintain a database of client details.
*   **Inventory/Item Management:** Manage products or services for easy invoicing.
*   **PDF Generation:** Generate professional PDF invoices (using `@react-pdf/renderer`).
*   **Authentication:** Secure user authentication powered by Laravel Fortify.

---

## Tech Stack

### Backend
*   **Framework:** Laravel 12.x
*   **Language:** PHP 8.2+
*   **Testing:** Pest PHP
*   **Authentication:** Laravel Fortify

### Frontend
*   **Framework:** React 19
*   **Glue:** Inertia.js 2.0 (Classic Monolith Feel, SPA Experience)
*   **Styling:** Tailwind CSS 4.0
*   **UI Components:** Radix UI, Headless UI, Lucide React
*   **Build Tool:** Vite

---

## Getting Started

### Prerequisites
*   PHP >= 8.2
*   Composer
*   Node.js & NPM

### Setup Command
The project includes a unified setup script in `composer.json` that handles dependencies, environment setup, key generation, and migrations.

```bash
composer run setup
```

### Development Server
To start the full development environment (Laravel server, Queue, Pail, and Vite):

```bash
composer run dev
```

Or run frontend only:
```bash
npm run dev
```

### Testing
Run the PHP test suite (Pest):

```bash
composer run test
```

### Linting & Formatting
*   **Lint (JS/TS):** `npm run lint` (ESLint)
*   **Format (JS/TS):** `npm run format` (Prettier)

---

## Project Structure

### Backend (`app/`)
*   **`Models/`**: Core data entities (`Invoice`, `Customer`, `Item`, `User`).
*   **`Http/Controllers/`**: Request handling logic.
    *   `InvoiceController.php`: Manages invoice CRUD and status updates.
    *   `CustomerController.php`: Manages customer data.
*   **`Http/Requests/`**: Form validation logic.

### Frontend (`resources/js/`)
*   **`pages/`**: Top-level Inertia views (maps to Routes).
    *   `invoices/`: Invoice listing, creation, and editing pages.
    *   `customers/`: Customer management pages.
*   **`components/`**: Reusable UI components.
    *   `ui/`: Base UI elements (likely Shadcn/Radix wrappers).
*   **`layouts/`**: App wrappers (Sidebar, Header).

### Configuration
*   **`routes/web.php`**: Application entry points and route definitions.
*   **`vite.config.ts`**: Frontend build configuration.
*   **`tailwind.config.js`** (or CSS variables in `app.css`): Styling configuration.

---

## Development Conventions

*   **Routing:** All web routes are defined in `routes/web.php` and use Resource controllers where possible.
*   **Inertia:** The app uses Inertia.js to render React components from Laravel controllers. `return Inertia::render('PageName', [...data])`.
*   **Typing:** TypeScript is strictly used for the frontend (`.tsx`, `.ts`).
*   **Styling:** Utility-first CSS with Tailwind. Avoid custom CSS files where possible.
