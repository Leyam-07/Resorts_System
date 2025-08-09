# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 2025-08-09

### Added

- **User Management System:**
  - Implemented a full user registration, login, and logout flow.
  - Created `User` model for database interactions with password hashing.
  - Developed `UserController` to manage user-related requests.
  - Built `register.php` and `login.php` views with Bootstrap.
- **Core Application Structure:**
  - Established a full MVC directory structure (`app/Models`, `app/Views`, `app/Controllers`).
  - Created a database initialization script (`scripts/init_db.php`).
  - Added a central database configuration file (`config/database.php`).
- **Dashboard and RBAC:**
  - Created a main entry point (`public/index.php`) for the application.
  - Implemented basic Role-Based Access Control (RBAC) to display different content for `Admin`, `Staff`, and `Customer` roles.
  - Added a separate registration page for creating the initial `Admin` user.
  - Built an admin-only user management interface to view and add new users.

### Fixed

- Resolved an issue where user sessions were not properly cleared on logout, causing role permissions to persist incorrectly across different user logins.
- Removed redundant `session_start()` calls from controllers to prevent PHP notices.

## [1.0.0] - 2025-08-09

### Added

- Created foundational project structure and documentation.
- Established core AI guidance with `.roo/rules/`:
  - `1.Context.md`
  - `2.Architecture.md`
  - `3.Coding-Standards.md`
  - `4.User-Interface.md`
  - `5.Project-Artifacts.md`
- Created `docs/` directory with key project artifacts:
  - `ROADMAP.md`
  - `CHANGELOG.md`
  - `Database-Schema.md`
  - `Deployment-Guide.md`
