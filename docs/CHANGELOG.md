# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.20.8] - 2025-09-17

### Added

- **Facility-Level Date Blocking:** Implemented the ability for administrators to block specific dates for individual facilities, preventing them from being booked. This includes a new database table (`BlockedFacilityAvailability`), a corresponding model, and controller methods for creating and deleting blocks.

### Changed

- **Unified Scheduling Interface:** Integrated facility-level date blocking into the unified admin management dashboard, providing a "Schedule" button for each facility.
- **Resort-Level Scheduling Refinement:** Refactored the existing resort-level date blocking to ensure consistency with the new facility-level feature. The resort schedule modal now dynamically fetches and manages blocks via AJAX, mirroring the functionality of the facility scheduler.
- **Booking Availability Logic:** Updated the `Booking` model's `isTimeSlotAvailable()` method to check against both `BlockedResortAvailability` and the new `BlockedFacilityAvailability` tables, ensuring comprehensive date conflict detection.

## [1.20.7] - 2025-09-16

### Fixed

- **Booking Form:** Resolved a bug on the "New Booking" page where the facility dropdown would fail to load after a resort was selected. The issue was traced to a missing action in the main router's whitelist, which has now been corrected.
- **Booking Form Pre-selection:** Fixed an issue where clicking "Book Now" from a facility's detail modal would not pre-select the resort and facility on the booking form. The link generation and the booking form's parameter handling have been updated to ensure a seamless transition.
- **Resort Details Modal:** Fixed a critical bug where the content (Details, Facilities, Feedback) of the "View Details" modal on the customer dashboard was not displaying. The issue was caused by a JavaScript conflict from two separate `DOMContentLoaded` listeners, which have now been consolidated into a single, unified script.

## [1.20.6] - 2025-09-16

### Fixed

- **Nested Modal Interaction:** Resolved a critical UI bug on the customer dashboard where closing the nested "Facility Details" modal would also incorrectly close the parent "Resort Details" modal. The interaction logic has been completely refactored to use a manual, programmatic approach that ensures a seamless "drill-down" and "return" user experience.

## [1.20.5] - 2025-09-16

### Added

- **Interactive Resort Details Modal:** Implemented a major UI enhancement for the customer dashboard. Resort cards now open a comprehensive modal with three tabs: "Resort" (showing a photo gallery and details), "Facilities" (listing all facilities at that resort), and "Feedback" (aggregating all reviews for the resort).
- **Nested Facility Details:** To provide a seamless user experience, a "View Details" button was added to each facility within the resort modal, allowing users to open a second, nested modal to see specific details and feedback for that individual facility.

### Changed

- **Customer Dashboard Workflow:** The primary action on resort cards was changed from a direct link to the booking page to launching the new details modal, encouraging exploration before booking.
- **Backend API:** Added new API endpoints to `UserController` (`getResortDetails`, `getResortFacilities`, `getResortFeedback`) to dynamically supply data to the new modals.
- **Feedback Model:** The `Feedback` model was enhanced with a `findByResortId()` method to support the aggregated feedback view in the new modal.

## [1.20.4] - 2025-09-16

### Changed

- **Admin UI/UX:** Streamlined the "Edit Resort" and "Edit Facility" modals by integrating the photo upload functionality directly into the main forms and removing the redundant "Upload" button. This simplifies the UI and improves the administrator's workflow.
- **Admin UI/UX:** Improved clarity by adding text labels ("Set as Main", "Delete") to the icon-only buttons in the photo management galleries for both resorts and facilities.

### Fixed

- **Admin Management UI:** Resolved a critical regression bug where the "Edit Resort" and "Edit Facility" modals would appear blank. The issue was caused by leftover JavaScript attempting to reference elements from the removed upload forms, which has now been corrected.

## [1.20.3] - 2025-09-16

### Added

- **Multi-Photo Upload for Resorts:** Implemented the ability to upload multiple photos for resorts. The first uploaded photo is automatically set as the main photo.
- **Resort Photo Gallery Management:** Added a photo gallery to the "Edit Resort" modal, allowing administrators to view all uploaded photos, set any photo as the main photo, and delete individual photos.
- **Multi-Photo Upload for Facilities:** Implemented the ability to upload multiple photos for facilities. The first uploaded photo is automatically set as the main photo.
- **Facility Photo Gallery Management:** Added a photo gallery to the "Edit Facility" modal, allowing administrators to view all uploaded photos, set any photo as the main photo, and delete individual photos.

### Changed

- **Resort Photo Management:** The "Add" and "Edit" resort modals were updated to support multiple file uploads for resort photos, replacing the single file input for the main photo.
- **Facility Photo Management:** The "Add" and "Edit" facility modals were updated to support multiple file uploads for facility photos.
- **AdminController Refactoring:** The `AdminController` was significantly refactored to handle the new multi-photo upload and management logic for both resorts and facilities, including dedicated methods for uploading, setting main photos, and deleting photos.
- **Resort and Facility Models:** Both `Resort.php` and `Facility.php` models were updated with a `setMainPhoto` method to support setting a specific image as the primary display photo.
- **Unified Management UI:** The `app/Views/admin/management/index.php` view was updated to integrate the new "Edit Facility" modal and its associated JavaScript for dynamic photo gallery loading and management.
- **Modal Sizing:** Increased the size of the "Add Resort" and "Add Facility" modals to `modal-lg` to better accommodate the new photo gallery features.

## [1.20.2] - 2025-09-16

### Changed

- **Resort Photo Management:** Replaced the text-based "Main Photo URL" input with a user-friendly file upload system in the "Add" and "Edit" resort modals, improving the admin workflow.
- **Code Consolidation:** All resort management logic was moved from the deprecated `ResortController` into the `AdminController`. The old controller and its router entry have been removed, cleaning up the codebase.

### Fixed

- **Admin Management UI:** Resolved a critical bug where the unified "Management" page would appear blank on a fresh database install. The page now displays a helpful "empty state" prompt to guide the admin.
- **Image Preview:** Fixed a bug in the "Edit Resort" modal where the current main photo was not displaying due to an incorrect image path.

## [1.20.1] - 2025-09-16

### Fixed

- **Admin Registration Form:** Corrected the form submission and login link URLs on the admin registration page (`register-admin.php`). The links were previously using relative paths, which prevented them from reaching the main controller. They now correctly point to `public/index.php` to ensure proper routing.

## [1.20.0] - 2025-09-16

### Added

- **Database Truncation Script:** Added a new development script (`scripts/dev/truncate_db_tables.php`) to clear all data from database tables, facilitating a factory reset for development and testing.
- **Unified Management Dashboard:** Created a new, centralized "Management" page for administrators, featuring an accordion UI to manage all resorts and their nested facilities from a single location.
- **Resort-Specific Dashboard Filtering:** Implemented a dropdown filter on both the Admin and Staff dashboards, allowing them to view bookings and summaries for all resorts or drill down to a specific one.
- **Resort-Wide Scheduling:** Admins can now block out entire dates for a specific resort (e.g., for maintenance or private events) via a new "Manage Schedule" modal in the unified dashboard.
- **Dynamic Booking Form:** The customer booking form now dynamically loads a resort's facilities using AJAX when a resort is selected, improving user experience.

### Changed

- **Customer Dashboard:** The main customer dashboard has been completely redesigned from a simple facility list into a modern, resort-centric view displaying a card for each resort with its main photo and description.
- **Admin Navigation:** Deprecated the separate "Manage Facilities" and "Manage Resorts" links in the admin header and replaced them with a single "Management" link.
- **Customer Preview:** The admin's "Preview Customer View" page was updated to be an exact mirror of the new resort-card-based customer dashboard.
- **Database & Models:**
  - The `Resorts` table was extended with `ShortDescription`, `FullDescription`, and `MainPhotoURL` columns.
  - The `Booking` model was updated to be resort-aware, enabling filtering and checking for resort-wide availability blocks.
  - Added new `ResortPhotos` and `BlockedResortAvailability` tables and their corresponding models to support the new features.

## [1.19.0] - 2025-09-16

### Added

- **Multi-Resort Architecture:** Implemented a major architectural enhancement to allow a single admin to manage multiple resort properties. This fulfills a core requirement from the initial project scope.
- **Resort Management Module:** Added a complete CRUD (Create, Read, Update, Delete) interface for administrators to manage resorts.
- **Customer-Facing UI Updates:** The customer dashboard and booking forms were updated to group facilities by their parent resort, improving clarity and user experience.

### Changed

- **Admin & Facility Integration:** The "Manage Facilities" module was refactored to be fully dependent on the new resort system, removing all hard-coded assumptions of a single resort.
- **Booking & Dashboard Logic:** The `UserController` and `BookingController` were updated to handle the new nested data structure (resorts containing facilities).

## [1.18.0] - 2025-09-16

### Changed

- **Admin Facility Management UI/UX:** Overhauled the "Manage Facilities" page to use a modal-based interface. The separate pages for "Add," "Edit," and "Manage Schedule" were converted into a single-page, modal-driven workflow.
- **Code Refactoring:** Converted the `edit.php` and `schedule.php` views into partials that are now loaded via AJAX into the modals. The `create.php` view was eliminated, with its form moved directly into the main modals file. This refactoring centralizes the UI components and improves the administrative workflow.

### Fixed

- **Modal Content Loading:** Resolved a critical bug where the "Edit" and "Manage Schedule" modals failed to load their content. The issue was caused by the premature deletion of the `edit.php` and `schedule.php` partial views, which have now been restored to correct the AJAX loading functionality.

## [1.17.0] - 2025-09-16

### Changed

- **Admin User Management UI/UX:** Overhauled the "Manage Users" page to provide a more modern, single-page application experience.
  - Replaced the separate pages for "Add User," "Edit User," and "View User Bookings" with a fully modal-based interface.
  - All user management actions can now be performed without leaving the main user list, significantly improving administrative workflow and efficiency.
- **Code Refactoring:** Decoupled the user management views from the controller by transitioning from server-side HTML rendering to a client-side JSON-based approach for populating edit forms. Removed the now-redundant `add_user.php` and `edit_user.php` view files, cleaning up the codebase.

## [1.16.0] - 2025-09-14

### Changed

- **Feedback System UI/UX:** Revamped the customer feedback process to improve user experience.
  - Replaced the separate "Leave Feedback" page with a dynamic, on-page modal that opens directly from the "My Bookings" list.
  - The "Leave Feedback" button is now disabled and replaced with a "Feedback Submitted" badge for bookings that already have feedback, preventing duplicate submissions.

## [1.15.3] - 2025-09-14

### Fixed

- **Admin Dashboard Display:** Corrected a critical error on the Admin Dashboard where the "Upcoming Bookings" and "Today's Bookings" tables would cause a fatal error (`Undefined property: stdClass::$StartTime`). This was a regression from the recent time-slot booking system overhaul. The view has been updated to use the new `TimeSlotType` and a helper function to display a human-readable time range.
- **View User Bookings Display:** Fixed a similar `Undefined property` error in `app/Views/admin/view_user_bookings.php` (line 35) by updating it to use the `TimeSlotType` and the `getTimeSlotDisplay()` helper function for accurate time slot representation.

### Changed

- **Time Slot Display:** Improved the display of booking time slots on the admin dashboard to include the duration for better clarity (e.g., "7:00 AM - 5:00 PM (12 hrs)").

## [1.15.2] - 2025-09-14

### Changed

- **Profile Page UI/UX:** Reworked the user profile page to improve usability and clarity.
  - The page is now read-only by default, preventing accidental edits.
  - An "Edit Profile" button now toggles the form into an editable state.
  - When editing, "Save Changes" and "Cancel" buttons are displayed, and the "Back to Dashboard" button is hidden to create a focused editing experience.

## [1.15.1] - 2025-09-14

### Fixed

- **Booking Availability Logic:** Fixed a critical bug in the time-slot booking system where the availability check (`isTimeSlotAvailable`) only checked the booking date, not the specific time slot. This incorrectly prevented non-conflicting bookings (e.g., a '12_hours' and an 'overnight' booking) from being made on the same day. The logic is now corrected to handle all time slot conflict scenarios accurately.

### Added

- **Backend Test Script:** Created a new development script (`scripts/dev/test_timeslot_booking_logic.php`) to programmatically test and verify the correctness of the time-slot availability logic.

## [1.15.0] - 2025-09-14

### Changed

- **Booking System Overhaul:** Reworked the entire booking time selection process.
  - Replaced the flexible `StartTime` and `EndTime` inputs with a predefined set of three time slots: `12 Hours (7 AM to 5 PM)`, `24 Hours (7 AM to 5 AM)`, and `Overnight (7 PM to 5 AM)`.
  - Updated the `Bookings` database table to remove the `StartTime` and `EndTime` columns and added a new `TimeSlotType` ENUM column.
  - Refactored the `Booking` model, `BookingController`, and associated views to handle the new time slot logic.

## [1.14.0] - 2025-09-14

### Added

- **Customer-Facing Feedback Display:** Customers can now view all historical feedback for a facility.
  - A new "Feedback" tab has been added to the "View Details" modal on the customer dashboard.
  - This tab displays a list of all reviews, including the customer's name, rating, and comments, making the feedback system more transparent and useful.
- **Admin/Staff Facility Preview:** Implemented a read-only "Preview Facilities" mode for administrative users.
  - A new navigation link allows Admins and Staff to view the facility listings exactly as a customer would.
  - The "View Details" modal in this mode is strictly informational, with the "Book Now" button removed to prevent accidental bookings.

### Fixed

- **Modal Tab State:** Fixed a bug where the facility details modal would remember the last active tab. It now correctly defaults to the "Details" tab every time it is opened.
- **Main Photo Display:** Corrected a regression that caused an incorrect image to be displayed as the main photo in the facility details modal. The system now correctly prioritizes and displays the designated main photo.

## [1.13.0] - 2025-09-03

### Added

- **Customer Feedback System:** Implemented a full-featured feedback system to enhance guest engagement.
  - Customers can now submit a rating (1-5 stars) and an optional comment for any booking that has been marked as "Completed".
  - A "Leave Feedback" button now dynamically appears on the "My Bookings" page for eligible bookings.
  - Administrators have access to a new "View Feedback" page, which displays a comprehensive list of all submitted feedback, including customer name, booking details, rating, and comments.

### Changed

- **Database Schema:** Added a new `Feedback` table to store ratings and comments, linked directly to the `Bookings` table.
- **Admin Navigation:** Added a "View Feedback" link to the main navigation bar for administrators, providing easy access to the new feedback management page.

## [1.12.0] - 2025-09-02

### Added

- **Facility Image and Description Management:** Implemented a major feature allowing administrators to manage facility details more comprehensively.
- Admins can now add and edit a short and full description for each facility.
- A complete photo gallery system was added, allowing admins to upload multiple photos, set a main photo, and delete images.
- **Customer Dashboard:** Created a new, customer-facing dashboard that displays all available facilities in a user-friendly card format, showing the main photo and short description.
- **Interactive Facility Modal:** Implemented a smooth, interactive modal on the customer dashboard. When a customer clicks "View Details" on a facility, a modal appears, displaying a full photo gallery carousel and the facility's full description.

### Changed

- **Database Schema:** Updated the `Facilities` table to include `ShortDescription`, `FullDescription`, and `MainPhotoURL` columns. Created a new `FacilityPhotos` table to support the photo gallery.
- **Admin UI:** The "Manage Facilities" interface was completely overhauled to support the new description fields and photo gallery management.
- **Customer Experience:** The initial landing page for logged-in customers is now the new facilities dashboard instead of the booking creation page.

## [1.11.0] - 2025-09-01

### Fixed

- **Critical Email Failure:** Resolved a major bug where the email notification system was failing silently. The root cause was identified as a corrupted, manually installed PHPMailer library.

### Changed

- **Dependency Management:** Integrated **Composer** into the project for robust package management. Replaced the faulty manual PHPMailer installation with the official version installed via Composer. This improves the stability and maintainability of the project's dependencies.
- **Code Consistency:** Refactored the `UserController` to exclusively use static method calls for the `User` model, improving architectural consistency.

## [1.10.0] - 2025-09-01

### Added

- **Security:** Created a `.gitignore` file to prevent sensitive configuration files, such as `config/mail.php`, from being committed to version control.
- **Configuration Template:** Added a `config/mail.sample.php` file to provide a clear template for administrators to copy and configure their email settings.

## [1.9.0] - 2025-09-01

### Added

- **Email Notifications:** Implemented a robust email notification system using PHPMailer for reliable delivery.
  - The system now sends automated emails for:
    - New user registration ("Welcome" email).
    - Booking confirmations.
    - Booking cancellations.
- **Mail Configuration:** Added a new `config/mail.php` file to securely store SMTP credentials, separating sensitive information from application logic.

### Changed

- **Upgraded Mailer:** Replaced the basic PHP `mail()` function with PHPMailer, configured to use SMTP authentication. This significantly improves email deliverability and reduces the likelihood of emails being marked as spam.

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
