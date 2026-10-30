# Student Management System (Laravel)

This is the Laravel web-app migration of the Java Swing student management system. The original Java project remains in the parent folder.

## Requirements

- PHP 8.3 or later with `pdo_mysql`, `mbstring`, `openssl`, and `fileinfo`
- Composer
- MySQL 8 or compatible server

## Run locally

From this directory:

1. Copy `.env.example` to `.env` if needed and configure a **new/empty** MySQL database. For example:

   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=student_management_laravel
   DB_USERNAME=root
   DB_PASSWORD=
   ```

   Create that database in MySQL before migrating. Do not point the app at the Java project's `adbms` database: the desktop project uses a different, inconsistent schema.

2. On Windows, if Composer hits path-length errors because the project is nested in a long directory, create a short junction from this directory and run the commands through it:

   ```powershell
   New-Item -ItemType Junction -Path C:\student-management-laravel -Target (Get-Location).Path
   Set-Location C:\student-management-laravel
   ```

3. Install dependencies and initialize the app:

   ```powershell
   composer install
   php artisan key:generate
   php artisan migrate --seed
   ```

4. Start the development server:

   ```powershell
   php artisan serve
   ```

5. Open <http://127.0.0.1:8000>. The seeded local administrator is `admin` / `admin123`; change its password after signing in.

## Ported workflows

- Role-based sign-in for admins, teachers, and students
- Admin student/teacher account creation and removal, plus course creation, assignment, and removal
- Student course browsing and enrollment
- Teacher course list and result entry/update (assignment /10, test /10, project /20, midterm /20, final /40)
- Student result viewing and profile/contact/password updates

The app creates its own Laravel schema and does not automatically import records from the old Java database.
