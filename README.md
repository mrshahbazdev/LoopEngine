# EasySOP

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

### Core
- **Process Builder** - Create/edit decision workflows with steps, options, and transition logic
- **Execution Engine** - Step-by-step employee interface with automatic navigation
- **Self-Checking Loops (Regelkreislauf)** - Automatic feedback loops: check -> improve -> recheck -> repeat
- **Full Audit Trail** - Complete logging of every action, answer, and loop iteration
- **Process Duplication** - Deep-clone processes with steps, options, and transitions
- **Process Versioning** - Create new versions without breaking existing runs
- **Drag-and-Drop Reordering** - Reorder steps via drag-and-drop

### Dashboard & Analytics
- **Dashboard Charts** - Runs over time (30-day line chart), status breakdown bars, completion rate per process, loop distribution grid
- **Analytics Dashboard** - Completion rates, loop averages, team performance
- **CSV/PDF Export** - Export audit logs and run summaries

### Template Marketplace
- **Share Templates** - Publish your processes to the marketplace with name/description (EN/DE), category, and tags
- **Browse & Install** - Search, filter by category, sort by newest/popular/rating
- **Rate & Review** - 5-star rating system with text reviews
- **Export as JSON** - Export templates as portable JSON files for import elsewhere

### Integrations
- **Webhook Notifications** - Send real-time HTTP POST payloads when events occur:
  - `run.started`, `run.completed`, `run.paused`, `run.cancelled`, `run.looped_back`
  - `process.activated`, `process.archived`
  - `assignment.created`
- **HMAC-SHA256 Signing** - Verify webhook authenticity with signing secrets
- **Webhook Logs** - Full delivery history with response codes and payload inspection
- **Auto-disable** - Webhooks auto-disable after 10 consecutive failures
- **REST API** - Sanctum-authenticated API endpoints for mobile/external access

### Team Management
- **Team Dashboard** - Member cards with run counts, completion stats, pending assignments
- **Assign Processes** - Assign processes to specific team members with notes
- **Assignment Tracking** - Status tracking (pending/in_progress/completed) with auto-completion
- **Email Notifications** - Email alerts on process assignment and run completion

### Access Control
- **User Roles** - Admin, Team Lead, Employee with role-based routing
- **Granular Permissions** - 17 individual permissions assignable per user:
  - `processes.create`, `processes.edit`, `processes.delete`, `processes.activate`, `processes.duplicate`, `processes.version`
  - `runs.start`, `runs.export`
  - `team.assign`, `team.view`
  - `templates.share`, `templates.install`
  - `webhooks.manage`
  - `admin.analytics`, `admin.audit`, `admin.users`, `admin.permissions`
- **Admin Override** - Admins bypass all permission checks

### Frontend
- **Dark Mode** - Toggle with localStorage persistence and system preference detection
- **Animations** - CSS keyframe animations (fade-in, slide-up, scale-in) with stagger delays
- **Micro-interactions** - Card hover effects, button press feedback, smooth transitions
- **EN/DE Language Support** - Full internationalization with language switcher

## Tech Stack

| Component | Technology |
|-----------|-----------|
| Backend | Laravel 13, PHP 8.3 |
| Frontend | Alpine.js 3, Tailwind CSS 4, Chart.js 4 |
| Database | SQLite (dev) / PostgreSQL (prod) |
| Build | Vite 8 |
| Auth | Laravel built-in + Sanctum (API) |
| i18n | Laravel Localization (EN/DE) |
| Notifications | Laravel Notifications (email) |
| Containerization | Docker + docker-compose |

## Quick Start

### Prerequisites
- PHP 8.3+
- Composer
- Node.js 20+
- SQLite

### Setup

```bash
git clone https://github.com/mrshahbazdev/EasySOP.git
cd EasySOP

composer install
npm install

cp .env.example .env
php artisan key:generate

touch database/database.sqlite
# Set DB_CONNECTION=sqlite and DB_DATABASE=/full/path/to/database/database.sqlite in .env

php artisan migrate
php artisan db:seed   # optional: creates demo data
npm run build
php artisan serve
```

Visit `http://localhost:8000`

### Demo Accounts

After running `php artisan db:seed`:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@easysop.test | password |
| Team Lead | lead@easysop.test | password |
| Employee | employee@easysop.test | password |

## Docker Setup

```bash
# Start all services (app + PostgreSQL + Mailpit)
docker-compose up -d

# The app runs on http://localhost:8080
# Mailpit UI runs on http://localhost:8025
```

### docker-compose.yml Services

| Service | Port | Purpose |
|---------|------|---------|
| `app` | 8080 | Laravel app (nginx + PHP-FPM) |
| `db` | 5432 | PostgreSQL 16 |
| `mailpit` | 8025 | Email testing UI |

### Environment Variables

Set these in `docker-compose.yml` or `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=easysop
DB_USERNAME=easysop
DB_PASSWORD=secret
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
```

## API Documentation

All API endpoints require Sanctum authentication. Generate a token via the app or use:

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@easysop.test","password":"password"}'
```

### Endpoints

| Method | URL | Description |
|--------|-----|-------------|
| `GET` | `/api/user` | Current user info |
| `GET` | `/api/processes` | List active processes |
| `GET` | `/api/processes/{id}` | Process details with steps/options |
| `GET` | `/api/runs` | List user's runs (paginated) |
| `POST` | `/api/runs/start/{process_id}` | Start a new run |
| `GET` | `/api/runs/{id}` | Run details with current step |
| `POST` | `/api/runs/{id}/answer` | Submit answer (`option_id`, `response_text`) |
| `POST` | `/api/runs/{id}/pause` | Pause a run |
| `POST` | `/api/runs/{id}/resume` | Resume a paused run |
| `POST` | `/api/runs/{id}/cancel` | Cancel a run |
| `GET` | `/api/runs/{id}/summary` | Run summary with responses and audit trail |

### Example: Start and Complete a Run

```bash
# Start a run
curl -X POST http://localhost:8000/api/runs/start/1 \
  -H "Authorization: Bearer YOUR_TOKEN"

# Submit an answer
curl -X POST http://localhost:8000/api/runs/1/answer \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"option_id": 1, "response_text": "Looks good"}'

# Get summary
curl http://localhost:8000/api/runs/1/summary \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Response Format

All responses follow the format:

```json
{
  "data": { ... },
  "meta": { "current_page": 1, "last_page": 5, "total": 100 }
}
```

## Webhook Integration

### Setup

1. Go to **Integrations > Webhooks** in the sidebar
2. Click **Create Webhook**
3. Enter a URL, optional signing secret, and select events
4. The webhook will fire HTTP POST requests with JSON payloads

### Payload Format

```json
{
  "event": "run.completed",
  "timestamp": "2025-01-15T10:30:00.000000Z",
  "data": {
    "run_id": 42,
    "process": "Quality Check",
    "user": "John Doe",
    "loop_count": 2
  }
}
```

### Signature Verification

If a signing secret is configured, payloads include an `X-EasySOP-Signature` header:

```python
import hmac, hashlib
expected = hmac.new(secret.encode(), payload_json.encode(), hashlib.sha256).hexdigest()
assert request.headers['X-EasySOP-Signature'] == expected
```

### Events

| Event | Trigger |
|-------|---------|
| `run.started` | A new process run begins |
| `run.completed` | A run finishes successfully |
| `run.paused` | A run is paused |
| `run.cancelled` | A run is cancelled |
| `run.looped_back` | A self-check triggers a loop back |
| `process.activated` | A process is set to active |
| `process.archived` | A process is archived |
| `assignment.created` | A process is assigned to a team member |

## Team Management

### Roles

| Feature | Admin | Team Lead | Employee |
|---------|-------|-----------|----------|
| Create/edit processes | Yes | Own only | No |
| Delete processes | Yes | Own only | No |
| Start process runs | Yes | Yes | Yes |
| View audit trail | All | Own team | Own runs |
| Manage users | Yes | No | No |
| View analytics | Yes | Own team | No |
| Manage webhooks | Yes | Yes | No |
| Share templates | Yes | Yes | No |
| Manage permissions | Yes | No | No |

### Assigning Processes

1. Navigate to **Team > Assign Process**
2. Select a process and team member
3. Add optional notes
4. The team member receives an email notification
5. Assignment status auto-updates when the assigned run completes

### Granular Permissions

Admins can assign individual permissions at **Admin > Permissions**. This allows fine-grained control beyond role-based defaults - e.g., giving an employee permission to export data or view analytics.

## Project Structure

```
EasySOP/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AdminController.php          # Analytics, audit log, user management
│   │   │   ├── Api/ProcessApiController.php # REST API endpoints
│   │   │   ├── AuthController.php           # Login, register, logout
│   │   │   ├── DashboardController.php      # Dashboard with chart data
│   │   │   ├── ExportController.php         # CSV/PDF exports
│   │   │   ├── LocaleController.php         # EN/DE language switcher
│   │   │   ├── PermissionController.php     # Granular permission management
│   │   │   ├── ProcessController.php        # Process CRUD + steps/options/transitions
│   │   │   ├── RunController.php            # Process execution engine
│   │   │   ├── TeamController.php           # Team dashboard, assignments
│   │   │   ├── TemplateController.php       # Template marketplace
│   │   │   └── WebhookController.php        # Webhook CRUD + logs
│   │   └── Middleware/
│   │       ├── CheckRole.php                # Role-based access
│   │       └── SetLocale.php                # Language detection
│   ├── Models/
│   │   ├── Permission.php                   # Granular permissions with AVAILABLE constant
│   │   ├── Process.php                      # Process templates
│   │   ├── ProcessRun.php                   # Execution instances
│   │   ├── ProcessStep.php                  # Steps with questions (EN/DE)
│   │   ├── ProcessTemplate.php              # Marketplace templates
│   │   ├── RunLog.php                       # Audit trail entries
│   │   ├── RunResponse.php                  # Answers with loop tracking
│   │   ├── StepOption.php                   # Answer choices
│   │   ├── StepTransition.php               # Decision logic
│   │   ├── TeamAssignment.php               # Team assignments
│   │   ├── TemplateRating.php               # Template ratings/reviews
│   │   ├── User.php                         # Users with roles + permissions
│   │   ├── Webhook.php                      # Webhook endpoints
│   │   └── WebhookLog.php                   # Webhook delivery logs
│   ├── Notifications/
│   │   ├── ProcessAssigned.php              # Assignment email
│   │   └── ProcessRunCompleted.php          # Run completion email
│   └── Services/
│       ├── ProcessEngine.php                # Core loop execution + webhook dispatch
│       └── WebhookService.php               # HTTP webhook delivery with HMAC signing
├── database/migrations/                     # All table schemas
├── lang/
│   ├── en/app.php                           # English translations (370+ keys)
│   └── de/app.php                           # German translations (370+ keys)
├── resources/views/
│   ├── admin/                               # Analytics, audit, users, permissions
│   ├── auth/                                # Login, register
│   ├── dashboard/                           # Dashboard with charts
│   ├── layouts/                             # App layout + sidebar
│   ├── processes/                           # Process builder
│   ├── runs/                                # Execution views
│   ├── team/                                # Team dashboard, assignments
│   ├── templates/                           # Marketplace (index, show, share)
│   └── webhooks/                            # Webhook CRUD + logs
├── routes/
│   ├── web.php                              # Web routes (80+)
│   └── api.php                              # API routes (10 endpoints)
├── Dockerfile                               # Multi-stage production build
├── docker-compose.yml                       # App + PostgreSQL + Mailpit
└── tests/Feature/                           # PHPUnit tests (28 tests, 66 assertions)
```

## Database Schema

### Tables

| Table | Purpose |
|-------|---------|
| `users` | Users with name, email, role, team, locale |
| `processes` | Process templates with EN/DE names, status, versioning |
| `process_steps` | Ordered questions with type and loop checkpoint config |
| `step_options` | Answer choices with color coding |
| `step_transitions` | Branching logic (next/goto/loop_back/start_process/end) |
| `process_runs` | Active execution instances |
| `run_responses` | All answers with loop iteration tracking |
| `run_logs` | Complete audit trail |
| `team_assignments` | Process-to-user assignments with status |
| `notifications` | Laravel notification queue |
| `process_templates` | Marketplace templates with rating/install stats |
| `template_ratings` | User ratings and reviews |
| `webhooks` | Webhook endpoints with events and signing secrets |
| `webhook_logs` | Delivery history with response codes |
| `permissions` | Granular user permissions |
| `personal_access_tokens` | Sanctum API tokens |

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
- Webhook notifications on each loop-back event

## Routes Overview

| URL | Description |
|-----|-------------|
| `/` | Landing page |
| `/login`, `/register` | Authentication |
| `/dashboard` | Dashboard with charts |
| `/processes` | Process list |
| `/processes-create` | Process builder |
| `/processes/{id}/edit` | Edit process with steps/options/transitions |
| `/processes/{id}` | Process preview |
| `/runs` | My process runs |
| `/runs/{id}` | Execute step-by-step |
| `/runs/{id}/summary` | Run summary + audit |
| `/templates` | Template marketplace |
| `/templates/{id}` | Template details + install |
| `/templates-share` | Share a template |
| `/webhooks` | Webhook management |
| `/webhooks/create` | Create webhook |
| `/webhooks/{id}/logs` | Webhook delivery logs |
| `/team` | Team dashboard |
| `/team/assignments` | Assignment list |
| `/team/assign` | Assign process |
| `/admin/analytics` | Analytics dashboard |
| `/admin/audit-log` | Full audit log |
| `/admin/users` | User management |
| `/admin/permissions` | Permission management |
| `/export/audit/csv` | Export audit CSV |
| `/export/audit/pdf` | Export audit PDF |

## Development

```bash
# Dev server with hot reload
php artisan serve &
npm run dev

# Run tests
php artisan test

# Fresh migration with seed
php artisan migrate:fresh --seed
```

## License

MIT
