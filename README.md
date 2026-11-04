# People HR — Laravel 11 + Vue 3

Modules: Employees · Attendance · Leave · Payroll · Recruitment (job openings + applicants) · Performance.
Roles: `admin` / `hr` manage everything; `employee` sees only their own records, clocks in/out, and requests leave.

## Install (into a fresh Laravel 11 project)

```bash
composer create-project laravel/laravel people-hr && cd people-hr
php artisan install:api            # installs Sanctum + routes/api.php
```

1. Copy everything from this folder over the new project (`app/`, `database/`, `resources/`, `routes/`, `package.json`, `vite.config.js`). Say yes to overwrite `User.php`, `routes/api.php`, `routes/web.php`.
2. Configure your database in `.env` (SQLite works out of the box), then:

```bash
php artisan migrate
php artisan db:seed --class=HrSeeder   # admin@example.com / password
npm install
npm run dev                            # in one terminal
php artisan serve                      # in another → http://localhost:8000
```

## Giving someone employee access
Create a user (role `employee`) and set `employees.user_id` to that user's id (Employees → Edit has no user picker yet; do it in tinker or add a field).

## Things to adjust
- `PayrollController` constants: tax rate, working days per month, overtime multiplier — replace with your country's rules.
- `AttendanceController::clock`: 09:15 late cutoff.
- Not included yet: file uploads (resumes/payslip PDFs), email notifications, audit log, tests.
