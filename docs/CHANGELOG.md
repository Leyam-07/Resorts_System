# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.8.3] - 2025-08-19

### Added

- **Staff Dashboard:** Implemented a dedicated Staff Dashboard displaying "Today's Bookings" and "Upcoming Bookings" (including pending and confirmed bookings).
- **Admin Dashboard Enhancements:** Added an "Upcoming Bookings" section to the Admin Dashboard with payment management links, providing comprehensive oversight of future reservations.

### Changed

- **Dashboard Routing:** Reworked the main application router (`public/index.php`) and `AdminController` to correctly route Admin and Staff users to their respective, feature-rich dashboards upon login.
- **"Upcoming Bookings" Logic:** Refined the `Booking` model's `findUpcomingBookings()` method to accurately include both pending and confirmed bookings from today onwards for operational visibility, and later adjusted to strictly future bookings as per user feedback.

## [1.8.2] - 2025-08-19

### Changed

- **Facility Display:** The "Manage Facilities" list in the admin dashboard now sorts facilities by their `FacilityID` by default, instead of alphabetically by name.
- **UI/UX:** Moved the "My Bookings" link from the customer's profile page to the main navigation bar for better accessibility and consistency.

## [1.8.1] - 2025-08-19

### Added

- **Developer Tools:** Created a new test script (`scripts/dev/test_financial_reports.php`) to unit test the `getMonthlyIncome()` and `getBookingHistory()` methods, ensuring the accuracy of financial and historical reporting.

## [1.8.0] - 2025-08-19

### Added

- **Financial Reporting:** Enhanced the Admin Dashboard with a new card displaying the total income for the current month, providing a quick financial overview.
- **Booking History:** Added a "Recent Booking History" table to the Admin Dashboard, showing a list of the 10 most recent past bookings and their status (`Completed`, `Cancelled`).

## [1.7.1] - 2025-08-18

### Security

- **Critical Access Control:** Fixed a major vulnerability where admin-only views could be accessed directly via their URL, bypassing all controller logic. All views now check for a security constant (`APP_LOADED`) to ensure they are loaded through the central router.
- **Role-Based Access Control:** Hardened the `AdminController` to ensure that any non-admin user attempting to access its methods is immediately met with a `403 Forbidden` error, preventing unauthorized actions.
- **Data Handling:** Removed a remaining `htmlspecialchars_decode()` call from the `AdminController` to prevent potential double-encoding issues and ensure user-provided notes are stored raw in the database, aligning with security best practices.

### Fixed

- **Booking Logic:** Corrected a critical bug that allowed users to book facilities on dates and times that were explicitly blocked for maintenance. The booking system now correctly checks for conflicts with both existing bookings and blocked time slots.
- **Capacity Validation:** Fixed a bug where the system did not enforce the guest capacity limit for a facility. Bookings are now validated to ensure the number of guests does not exceed the facility's maximum capacity.

### Changed

- **Facility Scheduling:** The "Block Time" feature for facilities now supports selecting a date range, allowing administrators to block multiple consecutive days in a single action.
- **UI/UX:** For improved usability and consistency, the primary action buttons (e.g., "Save," "Cancel," "Back") on the facility management pages have been moved to the bottom-left corner of the card footer.

## [1.7.0] - 2025-08-18

### Added

- **Facility Scheduling and Management:** Implemented a full CRUD (Create, Read, Update, Delete) interface for managing resort facilities.
  - Administrators can now add, edit, and delete facilities from the admin dashboard.
  - Added a "Manage Schedule" feature, allowing admins to block out specific time slots for a facility (e.g., for maintenance), preventing them from being booked.

### Changed

- **Admin Navigation:** Added a "Manage Facilities" link to the main navigation bar for administrators, providing quick access to the new management pages.

## [1.6.1] - 2025-08-17

### Added

- **Automatic Booking Confirmation:** The system now automatically updates a booking's status to "Confirmed" when a payment is marked as "Paid".
- **Manual Booking Status Update:** Added a form to the payment management page, allowing administrators to manually override and set a booking's status (e.g., to 'Pending', 'Confirmed', 'Cancelled').
- **Enhanced Dashboard View:** The admin dashboard now displays a separate, color-coded "Payment Status" column ('Unpaid', 'Partial', 'Paid') for each booking, providing better at-a-glance financial clarity.

### Changed

- **UI:** Changed the currency symbol on the payment management page from `$` to `₱` to reflect the local currency.

### Fixed

- **Routing:** Fixed a critical bug where multiple links and form submissions on the admin dashboard and payment management page were redirecting to the XAMPP homepage instead of the correct application route.
- **Data Display:** Corrected multiple PHP warnings on the payment management page caused by a mismatch between the data structure provided by the controller and the properties being accessed by the view.

## [1.6.0] - 2025-08-17

### Added

- **Payment Management:** Implemented a comprehensive payment tracking system for administrators.
  - Created the `Payments` table, `Payment` model, and `PaymentController`.
  - Added a "Manage Payments" view in the admin dashboard, allowing admins to add payment records, view payment history for a booking, and update payment statuses (e.g., Paid, Unpaid, Partial).
  - Integrated payment management routes into the main application router.

## [1.5.1] - 2025-08-17

### Fixed

- **Security:** Fixed a critical security vulnerability where non-administrator roles and logged-out users could directly access the `admin/dashboard.php` view via its URL. The view now contains a check to ensure only authenticated admins can access it.
- **UI:** Corrected the Admin Dashboard's "Today's Bookings" table to include the "Booking ID" column, which was previously missing.

## [1.5.0] - 2025-08-17

### Added

- **Admin Dashboard:** Implemented the initial version of the Admin Dashboard.
  - The dashboard now serves as the default landing page for administrators upon login.
  - It features a "Today's Bookings" view, providing a real-time summary of daily reservations.
  - A direct "Dashboard" link has been added to the main navigation bar for easy access.

### Fixed

- **Booking Validation:** Fixed a bug in the date validation logic that incorrectly prevented users from making bookings on the current day. The system now correctly compares only the date part, ignoring the time, allowing for same-day reservations.

## [1.4.1] - 2025-08-14

### Fixed

- **Security:** Fixed a critical vulnerability that allowed users to bypass controller logic and access sensitive view files directly via their URL. All admin views are now protected.
- **Security:** Corrected a flaw where non-admin and logged-out users could still access admin pages, resulting in errors and potential data exposure. The `AdminController` now robustly redirects all unauthorized users.
- **Bug:** Fixed a critical data-handling bug where special characters in user notes were being double-encoded with each save, causing them to display incorrectly (e.g., `"` becoming `"`, then `&quot;`). The fix was applied universally by replacing the deprecated `FILTER_SANITIZE_STRING` with modern filters across all controllers (`User`, `Admin`, `Booking`) to ensure user input is stored raw and escaped only on output.

### Changed

- **UI/UX:** Improved the admin user list by disabling the "Delete" button for the currently logged-in admin, preventing accidental self-deletion.
- **UI/UX:** Enhanced the user profile page by hiding the "My Bookings" button for non-customer roles (`Admin`, `Staff`).
- **UI/UX:** For UI consistency, the "View Bookings" button is now disabled (dimmed) for non-customer roles instead of being hidden.
- **UI/UX:** Replaced the plain-text "Forbidden" error with a professional, user-friendly 403 error page.
- **UI/UX:** Added a "Confirm Password" field and made the form scrollable on the admin "Add User" page for better usability on smaller screens.

## [1.4.0] - 2025-08-14

### Added

- **Customer Information Management:**
  - Admins can now view a list of all user bookings from the user management page.
  - Customers can now view their own booking history via a new "My Bookings" button on their profile page.
- **Admin User Notes:**
  - Admins can now add, edit, and view notes for each user.
  - The `Users` table has been updated with a `Notes` column.

### Changed

- The `User` model was updated to support the new `Notes` field.
- The `AdminController` was updated to handle CRUD operations for user notes.

## [1.3.0] - 2025-08-10

### Added

- **Booking Management:** Implemented a "My Bookings" page for customers to view their reservation history.
- **Booking Cancellation:** Added functionality for customers to cancel their own bookings.
- **Password Confirmation:** Added a "Confirm Password" field to both customer and admin registration forms to reduce user error.
- **Profile Security:** Added a "Confirm New Password" field to the user profile page to prevent password update errors.

### Changed

- **UI Unification:** Refactored the entire application to use a single, consistent navigation header (`partials/header.php`). This unified the look and feel of the main dashboard, booking pages, profile page, and admin user management page.
- **Admin Navigation:** Moved the "Manage Users" link from a button on the dashboard to a permanent link in the navigation bar for easier access.
- **Profile UI:** Made the profile form fields scrollable to ensure action buttons are always visible on smaller screens.

### Fixed

- **Security:** Fixed a critical access control vulnerability where `Staff` users could view and submit the booking creation form. The system now correctly restricts this functionality to `Customer` roles only.
- **Routing:** Corrected a routing error that caused an "Action not found" message when trying to access the new "My Bookings" and "Cancel Booking" pages.
- **Error Handling:** Improved the cancellation error message to distinguish between a "Booking not found" error and an authorization failure.

## [1.2.3] - 2025-08-10

### Added

- **User Feedback & Navigation:** Implemented significant improvements to the user experience for the booking engine.
  - Created a unified page layout (`partials/header.php`, `partials/footer.php`) to ensure a consistent look and feel across all pages.
  - Added a styled success page (`booking/success.php`) to provide clear confirmation after a booking is made.
  - Implemented a session-based flash message system to display user-friendly error messages directly on the form.
  - Added a "Back to Dashboard" button on the booking form for easier navigation.

### Fixed

- **Security:** Fixed a critical vulnerability that allowed logged-out users to access the booking creation page.
- **PHP Warnings:** Removed a redundant `session_start()` call in the `BookingController` that was causing PHP warnings.

## [1.2.2] - 2025-08-10

### Added

- **Model & Controller Testing:** Implemented comprehensive backend tests for the booking engine.
  - Added full CRUD (Create, Read, Update, Delete) methods to the `Booking` and `Facility` models.
  - Added robust data validation to the `BookingController` to prevent invalid data submissions (e.g., non-existent facilities, past dates).
- **Development Scripts:** Created a suite of temporary test scripts (`test_facility_model.php`, `test_booking_model.php`, etc.) to programmatically verify model and controller logic. These have been moved to `scripts/dev/` for future reference.

### Changed

- **Model Refactoring:** Refactored the `User` model to use a static `getDB()` connection method, aligning its architecture with the `Booking` and `Facility` models for improved consistency and easier testing.

### Fixed

- **Model-Schema Mismatch:** Corrected the `Facility` model by removing a non-existent `createdAt` property that was causing PHP warnings.
- **Test Script Resilience:** Updated test scripts to dynamically look up user and facility IDs instead of relying on hardcoded values, making the tests more reliable.

## [1.2.1] - 2025-08-10

### Added

- **Database Seeding:** Created a new script (`scripts/seed_db.php`) to populate the database with sample resorts and facilities for easier testing and development.

### Fixed

- **User Registration Flow:** Corrected multiple "404 Not Found" errors that occurred during the user registration process.
  - The main router (`public/index.php`) now correctly handles requests to display and process registration forms.
  - Fixed incorrect form `action` attributes in the registration views.
  - Corrected `header()` redirect paths in the `UserController` to use router-friendly URLs instead of direct file paths.
- **Database Script Idempotency:** Updated the `Database-Schema.md` to include `IF NOT EXISTS` in all `CREATE TABLE` statements, allowing the `init_db.php` script to be run multiple times without causing fatal errors.

### Changed

- Aligned the `Facility.php` model with the database schema by renaming `$pricePerHour` to `$rate` and removing an unused `$description` property.

## [1.2.0] - 2025-08-10

### Added

- **Booking Engine:**
  - Created `Booking` and `Facility` models.
  - Implemented `BookingController` to manage booking-related actions.
  - Added a view for customers to create bookings.
  - Integrated booking routes into the main application router.

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

  - Added user profile management, allowing users to update their details and password.
  - Implemented admin functionality to edit and delete user accounts from the dashboard.

### Improved

- Refactored application routing to be handled consistently by a central router (`public/index.php`), improving maintainability and fixing navigation bugs.
- Enhanced user registration and login forms with visual feedback for various scenarios (e.g., duplicate username/email, invalid credentials, successful registration/logout).

### Fixed

- Fixed a bug where session data (e.g., username) was not updated after a profile change, requiring a re-login to see changes.
- Corrected navigation links that were causing "Not Found" errors by aligning them with the new centralized routing system.
- Resolved fatal PDOException on attempting to register duplicate usernames by implementing pre-insertion checks in `User` model.
- Corrected `AdminController` user creation logic to handle and propagate specific error messages for user registration.
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
