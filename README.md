<p align="center">
  <h1 align="center">Free To Game Dashboard</h1>
  <p align="center">Analytics dashboard for free-to-play games powered by the <a href="https://www.freetogame.com/api-doc">FreeToGame API</a>.</p>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel" />
  <img src="https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white" alt="Vue 3" />
  <img src="https://img.shields.io/badge/Inertia.js-2.x-7645D9?style=for-the-badge" alt="Inertia.js" />
  <img src="https://img.shields.io/badge/TypeScript-5.x-3178C6?style=for-the-badge&logo=typescript&logoColor=white" alt="TypeScript" />
  <img src="https://img.shields.io/badge/Tailwind_CSS-3.x-38BDF8?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS" />
  <img src="https://img.shields.io/badge/Postgresql-003B57?style=for-the-badge&logo=Postgresql&logoColor=white" alt="Postgresql" />
</p>

---

## Overview

**FTG Dashboard** is a web-based analytics platform that synchronises free-to-play game data from the [FreeToGame Public API](https://www.freetogame.com/api-doc) and presents it through interactive charts and a data management interface.

Built with the **Laravel + Inertia.js + Vue 3** (LIV) stack, single-page-app experience with server-side framework capability.

---

## Features

| Feature                  | Description                                                                                                             |
| ------------------------ | ----------------------------------------------------------------------------------------------------------------------- |
| **Analytics Dashboard**  | Summary cards, genre distribution pie chart, platform breakdown, and monthly release trend chart                        |
| **One-Click API Sync**   | Pulls the latest game catalogue from the FreeToGame API and upserts records intelligently (create new / update changed) |
| **Date Range Filtering** | Filter all chart data by 1 month up to 5 years, or view the full history                                                |
| **Game Data Management** | Full CRUD interface for the games table with search, genre/platform filters, multi-column sorting, and pagination       |
| **Full-Text Search**     | Search across title, description, developer, and publisher simultaneously                                               |
| **Sync Status**          | Displays the last successful sync timestamp, cached server-side for performance                                         |
| **Authentication**       | Secure login / registration via Laravel Breeze with session-based auth                                                  |
| **Responsive UI**        | Mobile-friendly layout built with Tailwind CSS                                                                          |

---

## Tech Stack

### Backend

- **[Laravel 12](https://laravel.com/)** - PHP 8.2+ application framework
- **[Inertia.js (Laravel adapter)](https://inertiajs.com/)** - Server-driven SPA routing
- **[Laravel Sail](https://laravel.com/docs/12.x/sail)** - Laravel's default Docker development environment

### Frontend

- **[Vue 3](https://vuejs.org/)** + **[TypeScript](https://www.typescriptlang.org/)** - Reactive UI with type safety
- **[Inertia.js (Vue 3 adapter)](https://inertiajs.com/)** - Seamless SPA navigation
- **[ApexCharts](https://apexcharts.com/) / [vue3-apexcharts](https://github.com/apexcharts/vue3-apexcharts)** - Interactive charts
- **[PrimeIcons](https://primevue.org/icons/)** - Icon library
- **[Tailwind CSS v3](https://tailwindcss.com/)** - Utility-first CSS framework

### Tooling

- **[Vite 6](https://vitejs.dev/)** - Lightning-fast dev server & build tool
- **[vue-tsc](https://github.com/vuejs/language-tools)** - TypeScript compiler for Vue SFCs

### Database

- **PostgreSQL** via [Supabase](https://supabase.com/)

---

## Project Structure

```
FTG-Dashboard/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── DashboardController.php   # Stats, charts & filter logic
│   │       ├── GameController.php        # CRUD for games table
│   │       └── GameSyncController.php    # FreeToGame API sync
│   └── Models/
│       └── Games.php
├── database/
│   └── migrations/
│       └── 2026_02_24_135347_create_games_table.php
├── resources/
│   └── js/
│       ├── Pages/
│       │   ├── Dashboard.vue             # Analytics dashboard
│       │   ├── ManageData.vue            # Game data management table
│       │   └── Welcome.vue              # Landing / login page
│       ├── Components/                   # Reusable Vue components
│       ├── Layouts/                      # App layout shells
│       └── types/                        # TypeScript definitions
└── routes/
    └── web.php                           # Application routes
```

---

## Database Schema

### `games` table

| Column                      | Type           | Notes                           |
| --------------------------- | -------------- | ------------------------------- |
| `id`                        | `bigint`       | Primary key                     |
| `api_id`                    | `unsigned int` | Unique FreeToGame game ID       |
| `title`                     | `string`       | Game title                      |
| `thumbnail`                 | `string`       | Thumbnail image URL             |
| `short_description`         | `text`         | Brief game description          |
| `game_url`                  | `string`       | Link to play the game           |
| `genre`                     | `string`       | e.g. Shooter, MMORPG            |
| `platform`                  | `enum`         | `PC (Windows)` or `Web Browser` |
| `publisher`                 | `string`       | Publishing company              |
| `developer`                 | `string`       | Developing studio               |
| `release_date`              | `string`       | Release date string             |
| `freetogame_profile_url`    | `string`       | FreeToGame profile link         |
| `created_at` / `updated_at` | `timestamp`    | Eloquent timestamps             |

---

## Getting Started

### Prerequisites

| Requirement | Version |
| ----------- | ------- |
| PHP         | ≥ 8.2   |
| Composer    | ≥ 2.x   |
| Node.js     | ≥ 20.x  |
| npm         | ≥ 10.x  |

### Installation

**1. Clone the repository**

```bash
git clone https://github.com/Zaidan27/FTG-Api-Dashboard.git
cd FTG-Dashboard
```

**2. One-command setup** _(installs all dependencies, generates app key, runs migrations, and builds assets)_

```bash
composer run setup
```

> **Or do it manually, step by step:**

```bash
# Install PHP dependencies
composer install

# Set up environment file
cp .env.example .env
php artisan key:generate

# Run database migrations
php artisan migrate

# Install JS dependencies & build frontend
npm install
npm run build
```

**3. Start the development server**

```bash
# Starts PHP server, queue worker, log watcher, and Vite dev server together
composer run dev
```

The app will be available at **[http://localhost:8000](http://localhost:8000)** by default.

---

## Environment Configuration

Copy `.env.example` to `.env` and adjust the following as needed:

```ini
APP_NAME=FTG-Dashboard
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_URL=postgresql://postgres.uzwtlukshqpgtfggdklj:@Zramzek1217@aws-1-ap-southeast-1.pooler.supabase.com:6543/postgres

# Queue & Cache — uses database driver by default
QUEUE_CONNECTION=database
CACHE_STORE=database
```

---

## API Routes

| Method   | URI             | Name            | Description                    |
| -------- | --------------- | --------------- | ------------------------------ |
| `GET`    | `/`             | `dashboard`     | Analytics dashboard            |
| `GET`    | `/manage`       | `manage`        | Game data management table     |
| `POST`   | `/games`        | `games.store`   | Create a new game record       |
| `PUT`    | `/games/{game}` | `games.update`  | Update a game record           |
| `DELETE` | `/games/{game}` | `games.destroy` | Delete a game record           |
| `POST`   | `/sync`         | `sync`          | Trigger FreeToGame API sync    |
| `GET`    | `/sync/status`  | `sync.status`   | Get last sync timestamp (JSON) |

---

<p align="center">
  Built with ❤️ using <a href="https://laravel.com">Laravel</a> · <a href="https://vuejs.org">Vue 3</a> · <a href="https://inertiajs.com">Inertia.js</a>
</p>
