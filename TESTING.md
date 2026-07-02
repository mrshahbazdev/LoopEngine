# EasySOP / LoopEngine Testing Guide

## Table of Contents

1. [Quick Start](#quick-start)
2. [Test Accounts (Seeder)](#test-accounts-seeder)
3. [Running Tests](#running-tests)
4. [Test Architecture](#test-architecture)
5. [Multi-Tenant Testing](#multi-tenant-testing)
6. [Feature Test Scenarios](#feature-test-scenarios)
7. [API Testing](#api-testing)
8. [Manual Testing Checklist](#manual-testing-checklist)
9. [Writing New Tests](#writing-new-tests)

---

## Quick Start

```bash
# Install dependencies
composer install
npm install --ignore-scripts

# Setup environment
cp .env.example .env
php artisan key:generate

# Create database and run migrations
touch database/database.sqlite
php artisan migrate --force

# Seed demo data (two companies with isolated data)
php artisan db:seed

# Run all tests
php artisan test

# Run a specific test file
php artisan test tests/Feature/AuthTest.php

# Run a specific test method
php artisan test --filter=test_user_can_register
```

---

## Test Accounts (Seeder)

The seeder (`database/seeders/DatabaseSeeder.php`) creates two companies with fully isolated data:

### Company 1: Acme Corp

| User          | Email                  | Password   | Role       |
|---------------|------------------------|------------|------------|
| Admin User    | admin@easysop.test     | password   | admin      |
| Team Lead     | lead@easysop.test      | password   | team_lead  |
| Employee User | employee@easysop.test  | password   | employee   |

- Has a **Quality Check Process** (4 steps, loop checkpoints, transitions)

### Company 2: TechStart GmbH

| User            | Email                  | Password   | Role     | Locale |
|-----------------|------------------------|------------|----------|--------|
| Max Mustermann  | admin@techstart.test   | password   | admin    | de     |
| Lisa Schmidt    | lisa@techstart.test    | password   | employee | de     |

- Has a **Bug Report Workflow** (3 steps, loop checkpoints)

### Data Isolation

- Logging in as `admin@easysop.test` shows only Acme Corp data (Quality Check Process).
- Logging in as `admin@techstart.test` shows only TechStart data (Bug Report Workflow).
- Users from one company **cannot** see or access processes, runs, webhooks, or team members from another company.

---

## Running Tests

```bash
# Run the full test suite (28 tests)
php artisan test

# Run with verbose output
php artisan test --verbose

# Run a specific test class
php artisan test tests/Feature/ProcessEngineTest.php

# Run tests matching a name pattern
php artisan test --filter=test_loop_back

# Run only Feature tests
php artisan test tests/Feature/

# Run only Unit tests
php artisan test tests/Unit/
```

### Test Database

Tests use `RefreshDatabase`, which runs migrations on a fresh SQLite database for each test class. No manual database setup is needed for tests.

---

## Test Architecture

### Test Files

| File                          | Tests | What it covers                                      |
|-------------------------------|-------|-----------------------------------------------------|
| `AuthTest.php`                | 5     | Login, register (with company), dashboard auth, landing page |
| `ProcessEngineTest.php`       | 8     | Core engine: start run, answer, loop back, max loops, pause/resume/cancel, audit trail |
| `ProcessDuplicationTest.php`  | 4     | Process cloning, transition preservation, versioning, duplicate via route |
| `TeamAssignmentTest.php`      | 3     | Team dashboard access, process assignment, role-based restrictions |
| `ApiTest.php`                 | 5     | API auth, list/show processes, start & complete run, user endpoint |
| `ExampleTest.php`             | 2     | Basic health checks                                 |
| `Unit/ExampleTest.php`        | 1     | Basic unit health check                             |

### Key Testing Patterns

**All tests create users with a company** via `Company::factory()` + `User::factory()`:

```php
protected function setUp(): void
{
    parent::setUp();
    $this->company = Company::factory()->create();
    $this->admin = User::factory()->create([
        'role' => 'admin',
        'company_id' => $this->company->id,
    ]);
}
```

**Processes must include `company_id`** when created in tests:

```php
$process = Process::create([
    'name_en' => 'Test',
    'created_by' => $this->admin->id,
    'company_id' => $this->company->id,  // Required!
    'status' => 'active',
    'version' => 1,
]);
```

---

## Multi-Tenant Testing

### How Tenant Isolation Works

1. **`BelongsToCompany` trait** on `Process` and `Webhook` models adds a global scope (`CompanyScope`) that automatically filters queries by `auth()->user()->company_id`.
2. **`EnsureCompany` middleware** blocks authenticated users who don't have a `company_id`.
3. **Controllers** explicitly filter `User` queries with `where('company_id', ...)` since users don't use the global scope (would break auth).

### Testing Isolation Manually

```bash
# 1. Seed the database
php artisan db:seed

# 2. Start the dev server
php artisan serve

# 3. Login as Acme Corp admin -> should see "Quality Check Process"
#    POST /login: email=admin@easysop.test, password=password

# 4. Logout, login as TechStart admin -> should see "Bug Report Workflow"
#    POST /login: email=admin@techstart.test, password=password

# 5. Neither should see the other's processes
```

### Writing Isolation Tests

To verify company A cannot access company B's data:

```php
public function test_company_isolation(): void
{
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    $userA = User::factory()->create(['company_id' => $companyA->id]);
    $userB = User::factory()->create(['company_id' => $companyB->id]);

    // Create process for company B
    $process = Process::create([
        'name_en' => 'Secret Process',
        'created_by' => $userB->id,
        'company_id' => $companyB->id,
        'status' => 'active',
        'version' => 1,
    ]);

    // User A should NOT see company B's process
    $response = $this->actingAs($userA)->get(route('processes.index'));
    $response->assertDontSee('Secret Process');

    // User A should get 404 trying to access it directly
    $response = $this->actingAs($userA)->get(route('processes.show', $process));
    $response->assertNotFound();
}
```

---

## Feature Test Scenarios

### 1. Authentication & Registration

| Scenario                             | Expected Result                               |
|--------------------------------------|-----------------------------------------------|
| Visit `/login`                       | 200 - Login form renders                      |
| Visit `/register`                    | 200 - Registration form with company name field |
| POST `/register` with company_name, name, email, password | Creates company + admin user, redirects to `/dashboard` |
| POST `/login` with valid credentials | Redirects to `/dashboard`                     |
| Visit `/dashboard` unauthenticated   | Redirects to `/login`                         |
| Visit `/` (landing page)             | 200 - Public landing page                     |

### 2. Process Management (admin/team_lead)

| Scenario                             | Expected Result                               |
|--------------------------------------|-----------------------------------------------|
| Create a new process                 | Process created with user's `company_id`      |
| Edit process (own company)           | 200 - Edit form                               |
| Activate a draft process             | Status changes to `active`                    |
| Archive an active process            | Status changes to `archived`                  |
| Duplicate a process                  | New draft process created with "(Copy)" suffix |
| Create new version                   | New version created, old marked non-latest    |
| Delete a process                     | Process and related data removed              |

### 3. Process Runs

| Scenario                             | Expected Result                               |
|--------------------------------------|-----------------------------------------------|
| Start a run on an active process     | Run created with `in_progress` status         |
| Answer a question (next_step)        | Moves to the next step                        |
| Answer triggers loop_back            | Returns to target step, `loop_count` increments |
| Exceed max_loops                     | Run ends automatically                        |
| Pause a run                          | Status changes to `paused`                    |
| Resume a paused run                  | Status changes to `in_progress`               |
| Cancel a run                         | Status changes to `cancelled`, `completed_at` set |
| Complete final step (end)            | Status changes to `completed`                 |

### 4. Team Management

| Scenario                             | Expected Result                               |
|--------------------------------------|-----------------------------------------------|
| Admin views team dashboard           | Sees all company members with stats           |
| Team lead views team dashboard       | Sees same-team members only                   |
| Employee views team dashboard        | Sees only their own stats                     |
| Admin assigns process to user        | Assignment created with `pending` status      |
| Employee tries to assign             | 403 Forbidden                                 |

### 5. Company Settings (admin only)

| Scenario                             | Expected Result                               |
|--------------------------------------|-----------------------------------------------|
| Visit `/company/settings`            | 200 - Company profile form                    |
| Update company name/description      | Company updated, success flash                |
| Non-admin visits company settings    | 403 Forbidden                                 |

### 6. Webhooks (admin/team_lead)

| Scenario                             | Expected Result                               |
|--------------------------------------|-----------------------------------------------|
| Create webhook                       | Webhook created with user's `company_id`      |
| List webhooks                        | Shows only company's webhooks                 |
| Toggle webhook active/inactive       | `is_active` toggled                           |
| View webhook logs                    | Shows dispatch history                        |

### 7. Admin Panel (admin only)

| Scenario                             | Expected Result                               |
|--------------------------------------|-----------------------------------------------|
| View analytics                       | Company-scoped process/run stats              |
| View audit log                       | Company-scoped run logs                       |
| Manage users                         | List/create/edit/delete company users only    |
| Manage permissions                   | Grant/revoke permissions for company users    |
| Export audit CSV/PDF                  | Company-scoped export                         |

---

## API Testing

The REST API uses Laravel Sanctum token authentication.

### Generate a Token

```bash
# Via tinker
php artisan tinker
> $user = \App\Models\User::where('email', 'admin@easysop.test')->first();
> $token = $user->createToken('test')->plainTextToken;
> echo $token;
```

### API Endpoints

```bash
# Get authenticated user (includes company info)
curl -H "Authorization: Bearer $TOKEN" http://localhost:8000/api/user

# List processes (company-scoped)
curl -H "Authorization: Bearer $TOKEN" http://localhost:8000/api/processes

# Show a specific process
curl -H "Authorization: Bearer $TOKEN" http://localhost:8000/api/processes/1

# Start a run
curl -X POST -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/runs/start/1

# Submit an answer
curl -X POST -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"option_id": 1}' \
  http://localhost:8000/api/runs/1/answer

# List runs
curl -H "Authorization: Bearer $TOKEN" http://localhost:8000/api/runs

# Pause/Resume/Cancel a run
curl -X POST -H "Authorization: Bearer $TOKEN" http://localhost:8000/api/runs/1/pause
curl -X POST -H "Authorization: Bearer $TOKEN" http://localhost:8000/api/runs/1/resume
curl -X POST -H "Authorization: Bearer $TOKEN" http://localhost:8000/api/runs/1/cancel

# Run summary
curl -H "Authorization: Bearer $TOKEN" http://localhost:8000/api/runs/1/summary
```

### API Response Format

All API responses use `{ "data": { ... } }` wrapper:

```json
{
  "data": {
    "id": 1,
    "name": "Quality Check Process",
    "status": "active",
    "steps_count": 4,
    "runs_count": 0
  }
}
```

---

## Manual Testing Checklist

Use this checklist when testing the application manually:

### Registration & Login
- [ ] Register a new company at `/register` (company name + admin details)
- [ ] Verify company appears in sidebar header
- [ ] Log out and log back in
- [ ] Try registering with an existing email (should fail with validation error)

### Multi-Tenant Isolation
- [ ] Register two separate companies
- [ ] Create a process in Company A
- [ ] Login as Company B admin -- should NOT see Company A's process
- [ ] Company B admin users page should NOT show Company A users

### Process Lifecycle
- [ ] Create a new process with steps, options, and transitions
- [ ] Activate the process
- [ ] Start a run and complete it step by step
- [ ] Verify loop-back works when selecting a loop option
- [ ] Verify max loops enforcement
- [ ] Pause and resume a run
- [ ] Cancel a run
- [ ] Duplicate a process
- [ ] Create a new version
- [ ] Archive a process

### Team & Assignments
- [ ] As admin, view team dashboard (all company members visible)
- [ ] Assign a process to a team member
- [ ] As employee, verify you cannot assign processes
- [ ] Check that assignments show correct status

### Company Settings
- [ ] As admin, update company name and description
- [ ] Verify the sidebar header updates with new company name

### Admin Panel
- [ ] View analytics dashboard (company-scoped)
- [ ] View audit log and filter by user/action
- [ ] Add a new user to the company
- [ ] Edit a user's role
- [ ] Delete a user (not yourself)
- [ ] Export audit log as CSV
- [ ] Manage permissions for non-admin users

### Webhooks
- [ ] Create a webhook with selected events
- [ ] Toggle webhook active/inactive
- [ ] Trigger a webhook event (e.g., complete a run)
- [ ] View webhook logs

### Localization
- [ ] Switch to German (DE) via locale switcher
- [ ] Verify all company-related strings are translated
- [ ] Switch back to English (EN)

### API
- [ ] Generate API token via Sanctum
- [ ] List processes via API (verify company-scoped)
- [ ] Start and complete a run via API
- [ ] Verify `/api/user` returns company info

---

## Writing New Tests

### Test Template (Feature Test)

```php
<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->company = Company::factory()->create();
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_example(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('dashboard'));

        $response->assertOk();
    }
}
```

### Important Rules

1. **Always create a `Company` first** and assign it to users via `company_id`.
2. **Users in the same test must share a company** if they need to see each other's data.
3. **Processes must include `company_id`** when created manually (not via factory).
4. **Use `actingAs()`** for web routes and `withHeader('Authorization', 'Bearer ' . $token)` for API routes.
5. **The `BelongsToCompany` trait** auto-assigns `company_id` from `auth()->user()` on model creation -- but only when auth is set (i.e., during an HTTP request, not in `setUp()`).
6. **`UserFactory` creates a company automatically** if no `company_id` is provided, but this means each user gets a *different* company by default -- always pass `company_id` explicitly when users need to share a company.

### Factories

```php
// Creates a company
$company = Company::factory()->create();
$company = Company::factory()->create(['name' => 'Custom Name']);

// Creates a user with an auto-generated company
$user = User::factory()->create();

// Creates a user in a specific company
$user = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
```
