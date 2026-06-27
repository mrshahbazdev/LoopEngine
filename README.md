# LoopEngine

**Turn Thinking Into Systems** - A process decision loop platform that helps you build reusable thinking processes your entire team can follow, with automatic decisions, self-checking feedback loops (Regelkreislauf), and full audit trails.

## What It Does

You build a thinking process once - your whole team can apply it correctly, every time.

1. **You create a decision loop** - Define questions, possible answers, and what happens next
2. **Save it as a template** - Reusable by the entire team
3. **Employee starts the process** - Simple step-by-step questions
4. **System decides automatically** - Based on answers: next step, new process, repeat, or end
5. **Self-checking loops** - If something fails, the process goes back, re-evaluates, and improves
6. **Everything is logged** - Who, when, what answers, how many loops - fully traceable

## Features

- **Process Builder** - Create/edit decision workflows with steps, options, and transition logic
- **Execution Engine** - Step-by-step employee interface with automatic navigation
- **Self-Checking Loops (Regelkreislauf)** - Automatic feedback loops: check -> improve -> recheck -> repeat
- **Full Audit Trail** - Complete logging of every action, answer, and loop iteration
- **Analytics Dashboard** - Completion rates, loop averages, team performance
- **User Roles** - Admin, Team Lead, Employee with role-based access
- **EN/DE Language Support** - Full internationalization with language switcher

## Tech Stack

| Component | Technology |
|-----------|-----------|
| Backend | Laravel 13, PHP 8.3 |
| Frontend | Livewire 4, Alpine.js, Tailwind CSS 4 |
| Database | SQLite (dev) / PostgreSQL (prod) |
| Build | Vite 8 |
| Auth | Laravel built-in auth |
| i18n | Laravel Localization (EN/DE) |

## Quick Start

### Prerequisites
- PHP 8.3+
- Composer
- Node.js 20+
- SQLite

### Setup

```bash
# Clone the repo
git clone https://github.com/mrshahbazdev/LoopEngine.git
cd LoopEngine

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Set up SQLite database
touch database/database.sqlite

# Update .env for SQLite
# DB_CONNECTION=sqlite
# DB_DATABASE=/full/path/to/database/database.sqlite

# Run migrations
php artisan migrate

# Seed demo data (optional)
php artisan db:seed

# Build assets
npm run build

# Start the server
php artisan serve
```

Visit `http://localhost:8000`

### Demo Accounts

After running `php artisan db:seed`:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@loopengine.test | password |
| Team Lead | lead@loopengine.test | password |
| Employee | employee@loopengine.test | password |

## Project Structure

```
LoopEngine/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AdminController.php      # Analytics, audit log, user management
│   │   │   ├── AuthController.php       # Login, register, logout
│   │   │   ├── DashboardController.php  # Main dashboard
│   │   │   ├── LocaleController.php     # EN/DE language switcher
│   │   │   ├── ProcessController.php    # Process builder CRUD + steps/options/transitions
│   │   │   └── RunController.php        # Process execution engine
│   │   └── Middleware/
│   │       ├── CheckRole.php            # Role-based access control
│   │       └── SetLocale.php            # Language detection
│   ├── Models/
│   │   ├── Process.php                  # Process templates
│   │   ├── ProcessStep.php              # Steps with questions (EN/DE)
│   │   ├── StepOption.php               # Answer choices with colors
│   │   ├── StepTransition.php           # Decision logic (next/goto/loop_back/end)
│   │   ├── ProcessRun.php               # Execution instances
│   │   ├── RunResponse.php              # Answers with loop iteration tracking
│   │   ├── RunLog.php                   # Full audit trail
│   │   └── User.php                     # Users with roles
│   └── Services/
│       └── ProcessEngine.php            # Core execution engine
├── database/
│   ├── migrations/                      # All table schemas
│   └── seeders/
│       └── DatabaseSeeder.php           # Demo processes + users
├── lang/
│   ├── en/app.php                       # English translations (160+ keys)
│   └── de/app.php                       # German translations (160+ keys)
├── resources/views/
│   ├── admin/                           # Analytics, audit log, user management
│   ├── auth/                            # Login, register
│   ├── dashboard/                       # Main dashboard
│   ├── layouts/                         # App layout + sidebar
│   ├── processes/                       # Process builder (create/edit/show)
│   ├── runs/                            # Execution (execute/summary/paused)
│   └── landing.blade.php               # Public landing page
└── routes/web.php                       # All route definitions
```

## Database Schema

### Core Tables

| Table | Purpose |
|-------|---------|
| `processes` | Process templates with EN/DE names, status, versioning |
| `process_steps` | Ordered questions with type (question/decision/loop_check/info/end) |
| `step_options` | Answer choices with color coding |
| `step_transitions` | Branching logic (next_step/goto_step/loop_back/start_process/end) |
| `process_runs` | Active execution instances |
| `run_responses` | All answers with loop iteration tracking |
| `run_logs` | Complete audit trail |

## How the Self-Checking Loop Works

The core innovation - steps marked as **Loop Checkpoints** create automatic feedback loops:

```
Step 1: Do the work
Step 2: [Loop Checkpoint] "Is the result OK?"
  -> YES -> Continue to next phase
  -> NO  -> Loop back to Step 1 (with loop counter)
  -> After max_loops reached -> Auto-continue or escalate
```

Each loop iteration is tracked. The system records:
- How many times a step was revisited
- What answer was given each time
- Total loop count per run
- Duration and completion status

## User Roles & Permissions

| Feature | Admin | Team Lead | Employee |
|---------|-------|-----------|----------|
| Create/Edit processes | Yes | Own only | No |
| Delete processes | Yes | Own only | No |
| Start process runs | Yes | Yes | Yes |
| View audit trail | All | Own team | Own runs |
| Manage users | Yes | No | No |
| View analytics | Yes | Own team | No |

## Routes

| URL | Description |
|-----|-------------|
| `/` | Landing page |
| `/login` | Login |
| `/register` | Register |
| `/dashboard` | Main dashboard |
| `/processes` | Process list |
| `/processes-create` | Process builder |
| `/processes/{id}/edit` | Edit process with steps/options/transitions |
| `/processes/{id}` | Process preview with flow visualization |
| `/runs` | My process runs |
| `/runs/{id}` | Execute a process step-by-step |
| `/runs/{id}/summary` | Run completion summary + audit trail |
| `/admin/analytics` | Analytics dashboard |
| `/admin/audit-log` | Full audit log |
| `/admin/users` | User management |

## Development

```bash
# Run dev server with hot reload
php artisan serve &
npm run dev

# Run tests
php artisan test

# Fresh migration with seed
php artisan migrate:fresh --seed
```

## License

MIT
