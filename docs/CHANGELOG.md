# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.35.0] - 2025-10-02

### Added

- **Resort Capacity Management:** Implemented a new `Capacity` field in the "Add New Resort" and "Edit Resort" modals, allowing administrators to define a guest limit for each resort.
- **Resort Validation:** Created a new `validateResortData()` function in `ValidationHelper.php` to provide robust, server-side validation for all resort-related data, including the new capacity field.

### Changed

- **Admin Controller:** The `storeResort()` and `updateResort()` methods in `AdminController.php` were refactored to integrate the new `validateResortData()` function, ensuring all resort data is validated before being saved to the database.

## [1.34.0] - 2025-10-02

### Changed

- **Capacity Logic Refactoring:** Overhauled the system to align with the new resort-based capacity model by removing all deprecated facility-level capacity logic. This improves architectural consistency and simplifies the booking process.

### Removed

- **Deprecated Facility Capacity:** Removed the `Capacity` column from the `Facilities` database table and all associated logic from the backend and frontend.
  - **Backend:** Cleared capacity-related code from the `Facility` model, `AdminController`, and `ValidationHelper`.
  - **Admin UI:** Removed all capacity fields and displays from facility management modals, lists, and pricing pages.
  - **Customer UI:** Stripped out facility capacity displays and validation from the booking form, as guest limits are now exclusively handled at the resort level.

## [1.33.2] - 2025-10-01

### Fixed

- **Registration System Issues:** Resolved critical issues with customer and admin registration functionality.
  - **Undefined Variable Bug:** Fixed fatal error in `UserController::register()` where `$username` variable was undefined when sending welcome emails
  - **Validation Error Display:** Added proper display of validation errors from session to registration forms, fixing issue where password complexity errors weren't shown to users
  - **Form Repopulation:** Implemented automatic repopulation of form fields when validation fails, preventing users from losing entered data
  - **Hidden Role Fields:** Added required role fields to registration forms to satisfy backend validation requirements
  - **Password Complexity:** Relaxed password requirements from requiring uppercase, lowercase, and numbers to only requiring minimum 8 characters, as initially requested

### Added

- **Enhanced Registration Forms:** Registration forms now provide better user feedback with validation error messages and field repopulation

## [1.33.1] - 2025-10-01

### Fixed

- **Payment Modal Contact Buttons:** Resolved issue where "Call Resort" and "Email Resort" buttons in the payment modal were non-functional (empty hrefs) when no payment methods were configured for a resort.
  - **Admin Contact Integration:** Updated system to use admin contact information (phone and email) for all resort contact buttons, ensuring consistent support contact across all resorts.
  - **Dynamic Button Population:** Modified `BookingController::showMyBookings()` to retrieve and pass admin contact data to the view.
  - **JavaScript Contact Handling:** Updated modal display logic to dynamically populate contact buttons with admin phone and email using template literals.
  - **Button Naming Improvement:** Changed button text for better clarity, as the button uses the phone number for contact regardless of calling.
  - **Database Independence:** Solution uses existing admin user contact fields, avoiding need for additional resort-specific contact database fields.

## [1.33.0] - 2025-09-27

### Fixed

- **Offline Bootstrap & FontAwesome Accessibility:** Resolved critical offline functionality issue where Bootstrap CSS and FontAwesome icons failed to load. The problem occurred because view files used absolute paths (`/assets/`) that assumed the project root was the web root, but the application is deployed under a subdirectory (`/ResortsSystem/`).
  - **Dynamic Asset Path Resolution:** Updated asset URLs throughout view files to use `<?= BASE_URL ?>/assets/css/bootstrap.min.css` etc., ensuring correct resolution regardless of deployment path
  - **Affected View Files:** Updated `header.php`, `login.php`, `register.php`, `register-admin.php`, `public/error.php`, `app/Views/errors/403.php` to use dynamic BASE_URL paths
  - **Bootstrap JavaScript Loading Fix:** Moved Bootstrap JS bundle from footer to header to ensure dropdowns initialize before DOM manipulation
  - **Path Consistency:** All CDN assets (Bootstrap 5.3.0 CSS/JS, FontAwesome 6.4.0) now load successfully offline with local assets
  - **BASE_URL Dynamic Construction:** Utilizes path stripping logic to generate absolute URLs from web root to project directory

### Changed

- **Asset Loading Strategy:** Transitioned from footer-loaded JavaScript (which caused dropdown initialization issues) to head-loaded JavaScript for reliable component initialization
- **Cross-Environment Compatibility:** Application now functions identically in online and offline environments without asset loading failures

### Technical

- **Deployment Path Independence:** Implemented BASE_URL-based path resolution to support subdirectory deployments
- **Bootstrap Component Reliability:** Ensured dropdown menus and other Bootstrap components work consistently across all pages
- **Offline-First Approach:** Eliminated dependency on external CDNs, enabling full offline functionality

## [1.32.0] - 2025-09-27

### Fixed

- **Payment Modal File Upload Reliability:** Resolved a critical user experience issue where customers had to select payment proof images multiple times before the upload would accept. The problem was caused by the browser not immediately populating `file.type` and `file.size` properties when a file is first selected, causing validation to fail incorrectly on initial attempts.
  - **Retry Mechanism Implementation:** Added intelligent retry logic to the `handleModalFileSelection()` function with up to 3 attempts and 100ms delays to allow browser time to populate file metadata
  - **Extension Fallback Validation:** Implemented immediate validation using file extension (JPG, PNG, GIF, WebP) as a reliable fallback when full metadata isn't available
  - **Error Resilience:** Added comprehensive error handling for FileReader failures and improved user feedback throughout the upload process
  - **Enhanced User Experience:** Eliminated the frustrating requirement for users to select the same file multiple times, providing smooth first-attempt uploads
  - **Cross-browser Compatibility:** Improved compatibility across different browsers where file metadata loading varies in timing

### Technical

- **File API Optimization:** Enhanced client-side file handling to work around known browser limitations in File API metadata loading
- **User Experience Improvement:** Significantly reduced user friction in payment proof submissions by eliminating retry requirements

## [1.31.0] - 2025-09-27

### Fixed

- **Payment Verification Infinite Loading:** Resolved critical infinite loading issue where clicking "Verify & Approve" on pending payments would hang for 2-3 minutes. The system now processes payments instantly and redirects correctly.
  - **Database Lock Deadlocks:** Fixed database deadlock caused by multiple concurrent PDO connections. Unified all models to use centrally managed Database singleton (`Database::getInstance()`), eliminating lock wait timeouts.
  - **Model Database Connections:** Updated Payment, BookingAuditTrail, and other models to use shared database connection instead of independent PDO instances.
- **Property Name Inconsistencies:** Corrected systematic undefined property errors where database columns (PascalCase) and PHP object properties (camelCase) were mismatched.
  - **Payment Model:** Fixed `booking->TotalAmount` references to `booking->totalAmount`
  - **BookingLifecycleManager:** Fixed `$booking->Status` to `$booking->status` and `$booking->BookingDate` to `$booking->bookingDate`
- **Email Notification System:** Enhanced reliability by adding non-blocking email sending with proper error handling.
  - **SMTP Timeouts:** Added 5-second timeout to PHPMailer to prevent indefinite hanging on SMTP failures
  - **Error Handling:** Wrapped email calls in try-catch blocks with logging instead of blocking payment processing
  - **Async Behavior:** Email failures no longer interrupt payment verification workflow

### Added

- **Payment Debugging Infrastructure:** Created comprehensive debugging scripts for payment verification troubleshooting.
  - `scripts/debug_payment_verification.php` - Direct CLI testing of Payment::verifyPayment()
  - `scripts/debug_payments_status.php` - Payment status investigation
  - `scripts/debug_pending_payments.php` - Pending payments enumeration

### Technical

- **Database Architecture Improvement:** Eliminated multiple concurrent database connections that caused transaction deadlocks and lock wait timeouts.
- **Application Stability:** Significantly improved stability by preventing database connection conflicts and email system hangs.
- **Error Recovery:** Enhanced system resilience with non-blocking email notifications and proper error logging.

### Changed

- **Database Connection Management:** Migrated from per-model database connections to globally managed singleton pattern for consistent transaction handling.

---

## [Unreleased]

## [1.28.1] - 2025-09-27

### Fixed

- **Payment Submission Property Access Errors:** Resolved critical undefined property errors in the payment submission workflow that caused redirects to error pages instead of successful payment processing.
  - **PaymentSchedule.scheduleId Access:** Fixed undefined property `$nextPayment->scheduleId` in `BookingController::submitPayment()` by correcting property name from camelCase to PascalCase (`$nextPayment->ScheduleID`)
  - **PaymentSchedule.amount Access:** Fixed undefined property `$nextPayment->amount` access by correcting to PascalCase (`$nextPayment->Amount`)
  - **Payment.ScheduleID Property:** Added missing `$ScheduleID` property to Payment model to match database schema
  - **BookingLifecycleManager.Status Access:** Fixed undefined property `$booking->Status` in LifecycleManager by correcting camelCase to lowercase (`$booking->status`)
  - **Property Name Consistency:** Standardized property naming throughout payment and booking models to use PascalCase for database column matches vs camelCase for object properties
- **Database Field Mapping Issues:** Corrected systematic inconsistency between database columns (PascalCase: `ScheduleID`, `Amount`) and PHP object properties that caused undefined property warnings throughout the payment system

### Technical

- **Property Access Standardization:** Updated all PaymentSchedule and Booking property access throughout controllers and models to use correct case-sensitive property names
- **Database Schema Alignment:** Ensured all PHP property access matches actual database column names for PaymentSchedule, Payment, and Booking models
- **Error Flow Restoration:** Restored proper payment submission flow that now correctly redirects to payment success pages instead of error pages

## [1.28.0] - 2025-09-27

### Added

- **On-Site Booking Management System:** Implemented comprehensive administrator functionality for real-time booking modifications through the Unified Booking Management interface.
  - **Enhanced Manage Booking Modal:** Redesigned modal in `unified_booking_management.php` to support on-site booking modifications including dynamic facilities management, on-site payment recording, and audit trail integration.
  - **Dynamic Facilities Management:** Added AJAX-powered facilities population with pre-selected existing bookings, real-time pricing calculations, and checkbox-based add/remove functionality.
  - **On-Site Payment Recording:** Implemented payment amount, method, and status fields for admin-recorded payments bypassing normal proof upload requirements.
  - **Backend Controller Integration:** Added `getBookingDetailsForManagement()` and `adminUpdateBooking()` endpoints in `AdminController.php` for comprehensive booking data retrieval and transactional updates.
  - **Audit Trail Integration:** Comprehensive logging of all administrative booking modifications including status changes, facility additions/removals, total amount recalculations, on-site payments, and balance updates with user attribution and change tracking.
  - **Resort Controller Restoration:** Added missing resort controller routing in `public/index.php` and fixed facilities loading endpoint CORS and property access issues.

### Changed

- **JavaScript Property Access:** Corrected facility JavaScript code to use camelCase properties (`facility.name`, `facility.rate`, `facility.facilityId`) instead of PascalCase, aligning with PHP object structure.
- **Booking Model Enhancement:** Extended `adminUpdateBooking()` method in `Booking.php` with full audit trail logging, transactional safety, and comprehensive validation for multi-operation booking updates.
- **Facility.php Consistency:** Updated to use centralized Database helper for consistent connection management across all models.

### Fixed

- **Facilities Loading Bug:** Resolved critical "Failed to load facilities" JavaScript error caused by missing resort controller routing and incorrect property case handling in AJAX responses.
- **Modal Data Population:** Fixed booking facilities pre-selection in management modal by correcting BookedFacilities property access and comparison logic.
- **Audit Trail Attribution:** Ensured all audit entries properly attribute changes to the admin user performing modifications.

### Security

- **Controller Access Control:** Verified role-based access controls for all new admin booking modification endpoints, ensuring only administrators can perform on-site booking changes.

## [1.27.3] - 2025-09-27

### Fixed

- **Admin UI Fatal Errors & Performance:** Resolved a series of critical issues that made the "Unified Booking & Payment Management" page unusable.
  - **Fatal Errors:** Fixed multiple `Undefined property` PHP errors in `app/Models/BookingLifecycleManager.php` caused by inconsistent property name casing (`Status` vs. `status`).
  - **N+1 Query Problem:** Eliminated a major performance bottleneck by refactoring `Booking::getBookingsWithPaymentDetails()` to use `JOIN`s, fetching all required data in a single query instead of one query per booking.
- **Payment Schedule Logic:** Corrected a flaw in `app/Models/PaymentSchedule.php` that incorrectly created 2-part payment schedules for bookings paid in full. The system now correctly generates a single installment record for full payments.
- **Audit Trail Clarity:** Refactored `app/Models/BookingAuditTrail.php` to log a single, consolidated "CREATE" event for new bookings instead of a separate entry for each field, making the audit trail cleaner and more accurate.

### Changed

- **Business Logic:** The application now creates payment schedules _after_ the first payment is submitted in `app/Controllers/BookingController.php`, ensuring the schedule accurately reflects the payment type (full or partial).
- **Admin UI:** The "Phase 6 Info" column was renamed to "Audit & Payment Sched" for better clarity in `app/Views/admin/unified_booking_management.php`.

## [1.27.2] - 2025-09-27

### Fixed

- **Calendar UI/UX Overhaul:** Resolved multiple issues with the booking calendar to improve user experience, readability, and mobile adaptability.

  - **Logic Bug:** Fixed an issue where dates with pending bookings were not visually distinct. The backend logic in `BookingController.php` was updated to correctly assign a "Booked" status.
  - **Readability:** Enhanced the calendar UI in `app/Views/booking/create.php` to display both the date and a clear status text (e.g., "Booked", "Available"), and added a new "Booked" status to the legend.
  - **Critical Loading Error:** Resolved a JavaScript bug that caused the calendar to fail to load after initial fixes were applied.
  - **Mobile Adaptability:** Made the calendar UI more compact and mobile-friendly with CSS adjustments for a better experience on smaller screens.

## [1.27.1] - 2025-09-26

### Fixed

- **Payment Method Validation UX:** Resolved a critical user experience issue where customers could upload payment proofs and submit payments even when resorts had no configured payment methods. The warning message would disappear after uploading an image, allowing invalid payment submissions.
  - **Root Cause:** Payment methods were only checked for display purposes but not for form functionality control
  - **Solution:** Enhanced controller and view logic to completely disable payment functionality when no payment methods exist
  - **Changes:**
    - Added `$hasPaymentMethods` flag to `BookingController::showPaymentForm()` for form state control
    - Enhanced `app/Views/booking/my_bookings.php` modal to disable all form fields and submit button when no payment methods exist
    - Added direct contact options (phone/email links) for resort communication
    - Implemented form field disabling with `pointer-events: none` for visual feedback
  - **User Experience:** Clear messaging prevents confusion, form is completely disabled, contact information provided for alternative communication

## [1.27.0] - 2025-09-26

### Fixed

- **Payment Success Page Facilities Display:** Corrected a critical bug where the payment success page displayed empty facilities list after payment submission. Fixed property access mismatch in `app/Views/booking/payment_success.php` where `$facility->Name` was incorrectly used instead of `$facility->FacilityName` to match the SQL alias from the database query.
- **Notification System Method Error:** Resolved fatal error during payment submission where `BookingFacilities::getFacilitiesForBooking()` was called instead of the correct method name `BookingFacilities::findByBookingId()`. Also fixed property access from `$f->Name` to `$f->FacilityName` in notification email templates to properly display facility names in admin emails.

### Technical

- **Database Query Alignment:** Ensured consistency between SQL query aliases in `BookingFacilities::findByBookingId()` and property access throughout the application codebase.

## [1.26.9] - 2025-09-26

### Fixed

- **Payment Method Modal UX Enhancement:** Eliminated brief "An error occurred" flash during payment method submissions in management modal.
  - **Root Cause:** AJAX requests received redirect responses instead of JSON, causing parsing errors before success
  - **Solution:** Enhanced controller AJAX detection and JSON response handling
  - **Changes:**
    - Added `HTTP_X_REQUESTED_WITH` header detection in `AdminController::addPaymentMethod()`
    - Implemented JSON responses for AJAX requests while maintaining backward compatibility
    - Simplified JavaScript error handling with direct `.json()` parsing
    - Added `X-Requested-With: XMLHttpRequest` header to AJAX fetch requests
  - **User Experience:** Completely smooth payment method addition without modal closures or error flashes

## [1.26.8] - 2025-09-26

### Fixed

- **Blank Page on Payment Method Submission:** Resolved a critical bug where submitting the "Add Payment Method" form in the management modal would result in a blank page instead of proper feedback. Users experienced a blank page because traditional form submission with modal redirects was causing navigation issues.
  - **Root Cause:** Modal context interference with standard form POST/redirection flow
  - **Solution:** Implemented AJAX-based form submission that handles the request asynchronously while keeping the modal open
  - **Changes:**
    - Replaced traditional form submission with XMLHttpRequest in `app/Views/admin/management/index.php`
    - Added real-time feedback with success/error messages displayed directly within the modal
    - Implemented automatic payment methods list refresh upon successful addition
    - Form now clears with loading states and proper error handling
    - Prevents modal dismissal during submission process

### Enhanced

- **Payment Method Management UX:** Significantly improved user experience for resort payment method administration
  - Immediate visual feedback without page refreshes or modal closures
  - Graceful error handling with user-friendly messages
  - Automatic form reset and list updates on successful submissions
  - Loading indicators for better perceived performance

## [1.26.7] - 2025-09-25

### Fixed

- **Missing Facilities on Customer Payment Summary Page:** Corrected an issue where selected facilities were not displayed on the payment summary page (`app/Views/booking/payment.php`) due to a variable name mismatch, updating `$facility->Name` to `$facility->FacilityName`.
- **Missing Admin Interface for Managing Resort Payment Methods:** Resolved the problem where the admin interface for managing resort-specific payment methods was not accessible. This involved:
  - Refactoring `app/Models/ResortPaymentMethods.php` to use the centralized `Database` helper.
  - Relocating payment method management logic (`getPaymentMethodsJson()`, `addPaymentMethod()`, `deletePaymentMethod()`) from `app/Controllers/ResortController.php` to `app/Controllers/AdminController.php`.
  - Updating the main application router (`public/index.php`) to correctly direct requests to the `AdminController` for these actions.
  - Adding a "Manage Payments" button to the correct admin resort management view (`app/Views/admin/management/index.php`).
  - Integrating the "Manage Payment Methods" modal and its associated JavaScript into `app/Views/admin/management/facility_modals.php` and `app/Views/admin/management/index.php` respectively.

### Changed

- **Payment Method Management Logic Relocation:** Moved payment method CRUD operations from `ResortController` to `AdminController` for better architectural alignment within the admin panel.
- **Admin UI for Resort Management:** The "Manage Resorts" interface in `app/Views/admin/management/index.php` now includes a dedicated button for managing payment methods, complete with a functional modal.
- **Router Configuration:** Removed the redundant routing block for the `resort` controller in `public/index.php` as its payment management responsibilities were shifted.

## [1.26.6] - 2025-09-25

### Fixed

- **Booking Form Hang & Database Deadlock:** Resolved a critical issue where the booking form would hang indefinitely on submission. This was caused by multiple database connections leading to deadlocks. Implemented a centralized database connection manager (`app/Helpers/Database.php`) and refactored models and controllers to use a single shared PDO instance.
- **Silent Booking Validation Failure:** Corrected an issue where the booking form would reset without displaying validation errors by aligning form input `name` attributes with backend validation expectations and improving error message processing in `app/Controllers/BookingController.php`.
- **Notification Email Error:** Fixed a fatal error (`Undefined property: Booking::$startTime`) in `app/Helpers/Notification.php` by updating email templates to use `Booking::getTimeSlotDisplay($booking->timeSlotType)`.
- **Error Page Redirect Path:** Corrected the redirection path in `app/Helpers/ErrorHandler.php` to use the `BASE_URL` constant, ensuring correct redirects to `public/error.php`.
- **Undefined Method Call on Payment Page:** Resolved a fatal error (`Call to undefined method BookingFacilities::getFacilitiesForBooking()`) in `app/Controllers/BookingController.php` by updating it to call `BookingFacilities::findByBookingId()`.

### Added

- **Centralized Database Connection:** Introduced `app/Helpers/Database.php` for a singleton PDO instance.
- **Graceful Error Page:** Created `public/error.php` for user-friendly error display.

### Changed

- **Database Connection Management:** Transitioned core models and controllers to use the new centralized `Database` helper.
- **Error Handling Behavior:** Modified `ErrorHandler.php` to use `BASE_URL` for robust error page redirection.

### Technical

- **System Stability:** Enhanced booking system stability by resolving deadlocks and centralizing database access.
- **Workflow Integrity:** Ensured seamless booking-to-payment flow by fixing critical errors across components.

## [1.26.5] - 2025-09-25

## [1.26.5] - 2025-09-25

### Fixed

- **Critical Booking Form Hang:** Resolved a critical issue where the booking form would hang indefinitely on submission, ultimately failing to create a booking or provide user feedback.
  - **Root Cause:** Identified a database deadlock caused by multiple, unmanaged database connections being created by different models within a single booking transaction.
  - **Architectural Fix:** Implemented a centralized database connection manager (`app/Helpers/Database.php`) to ensure all models share a single PDO instance.
  - **Model Refactoring:** Updated `app/Models/Booking.php`, `app/Models/BookingFacilities.php`, `app/Models/PaymentSchedule.php`, and `app/Models/BookingAuditTrail.php` to use the new shared database connection.
  - **Transaction Integrity:** Ensured that all operations within the booking creation process (booking record, facility additions, payment schedule, audit trail) now occur within a single, consistent database transaction.
- **Silent Booking Validation Failure:** Corrected an issue where the booking form would reset without displaying validation errors.
  - **Form Input Name Mismatch:** Aligned form input `name` attributes (`resort_id`, `booking_date`, `timeframe`, `number_of_guests`, `facility_ids[]`) in `app/Views/booking/create.php` to match backend validation expectations.
  - **Improved Error Display:** Enhanced error message processing in `app/Controllers/BookingController.php` to correctly aggregate and display validation errors to the user.

### Added

- **Centralized Database Connection:** Introduced `app/Helpers/Database.php` to provide a singleton PDO database connection for the entire application, improving performance and preventing connection-related issues.

### Changed

- **Database Connection Management:** Transitioned all core models (`Booking`, `BookingFacilities`, `PaymentSchedule`, `BookingAuditTrail`) from independent database connection instantiation to using a single, shared connection managed by the `Database` helper.
- **Booking Form Validation Feedback:** Improved user experience by ensuring that all validation errors are now clearly displayed on the booking form.

### Technical

- **Architectural Stability:** Significantly enhanced the stability and reliability of the booking system by centralizing database connection management and resolving critical deadlock conditions.
- **Code Consistency:** Standardized database access patterns across key models, improving code maintainability and adherence to best practices.

## [1.26.4] - 2025-09-19

### Fixed

- **Booking Form Step Progression:** Resolved critical JavaScript logic issues where selecting a resort would incorrectly make all steps 1-4 turn green instead of sequential progression.
  - **Duplicate Function Removal:** Eliminated conflicting `handleDateOrTimeframeChange()` function (lines 892-909) that was causing improper step advancement
  - **Sequential Validation Logic:** Implemented proper step-by-step validation where each step only advances when prerequisites are met
  - **Guest Input Requirement:** Removed prefilled "1" value from guest number field, requiring manual user input for proper step progression
  - **Facilities Independence:** Made facility selection independent of guest validation - facilities step only turns green when actually selected
  - **Summary Logic:** Fixed summary step to turn green when displayed (after date selection) rather than depending on facility selection
- **Customer Dashboard Modal Flow:** Resolved facility pre-selection issue where clicking "Book This Facility" from facility modal wouldn't pre-select the facility in the booking form.
  - **URL Parameter Handling:** Enhanced booking form to automatically detect and pre-select facilities when `facility_id` parameter is provided
  - **Automatic Form Population:** Added JavaScript logic to check facility checkbox and trigger pricing updates when navigating from facility modal
  - **Seamless User Flow:** Complete modal-to-booking workflow now maintains facility selection context throughout the process

### Enhanced

- **Customer Navigation:** Added Dashboard link to customer navigation header, providing consistency with Admin and Staff navigation structure
- **Step Progression Logic:** Enhanced `updateStepProgress()` function with independent validation for guest and facility steps
- **User Experience:** Improved booking form flow with accurate visual feedback that matches actual completion status
- **Modal Button Clarity:** Improved booking button naming for better user understanding and action clarity
  - **Resort Modal Button:** "Book a Facility at this Resort" → "Book Resort Experience"
  - **Facility Modal Button:** "Book Now" → "Book This Facility"

### Changed

- **Form Validation:** Updated step indicators to provide precise feedback - facilities remain grey when not selected, guests step only advances with manual input
- **JavaScript Architecture:** Consolidated step management logic into centralized `markStepCompleted()` function for better maintainability
- **Navigation Structure:** Customer header now includes Dashboard → New Booking → My Bookings → Profile → Logout navigation flow
- **Modal Integration:** Enhanced facility pre-selection mechanism with automatic checkbox selection and event triggering for pricing updates

### Technical

- **Step Management System:** Complete overhaul of step progression JavaScript ensuring accurate 1→2→3→4→5→6 sequential flow
- **Form State Management:** Enhanced tracking of form completion status with independent validation for required vs. optional steps
- **Customer UX:** Booking form now provides intuitive, logical progression requiring actual user interaction at each step
- **Modal-to-Form Integration:** Seamless data flow from customer dashboard modals to booking form with automatic facility pre-selection

## [1.26.3] - 2025-09-19

### Fixed

- **Calendar Availability System:** Resolved critical issues with the enhanced calendar modal that was preventing users from browsing available dates.
  - **Routing Error Resolution:** Fixed "Error loading calendar data" by adding missing `getCalendarAvailability` API endpoint to the router's allowed actions whitelist in `public/index.php`
  - **Weekend Detection Bug:** Corrected JavaScript timezone parsing issues causing Monday dates to incorrectly display as weekend (yellow) instead of available (green)
  - **Calendar Rendering Logic:** Implemented UTC date handling in calendar grid rendering to eliminate timezone-related date calculation errors
- **Native Calendar Icon:** Hidden distracting HTML5 date picker calendar icon using CSS webkit and Firefox-specific rules for cleaner interface

### Enhanced

- **Booking Form Flow Optimization:** Rearranged booking form to logical progression: Resort → Timeframe → Date → Guests → Facilities → Summary
  - Users must now select both resort and timeframe before browsing available dates (logical prerequisite)
  - Calendar button only enables after both required selections are made
  - All JavaScript step indicators and validation logic updated to match new flow
- **Calendar User Experience:** Enhanced calendar interface with improved clarity and professional appearance
  - Button renamed from "View Calendar" to "Browse Available Dates" for clearer purpose
  - Changed to primary blue styling for better visual prominence
  - Updated help text to better explain both date selection options (direct input vs enhanced calendar)

### Changed

- **API Endpoint Access:** Extended booking controller routing to include all Phase 4-6 API endpoints: `getResortPricing`, `calculateBookingPrice`, `checkAvailability`, `getCalendarAvailability`, `showPaymentForm`, `submitPayment`, `paymentSuccess`, `getPaymentMethods`, `getAdvancedAvailabilityReport`, `getAvailabilitySuggestions`
- **Date Selection Interface:** Streamlined to two clean options - direct date input (without native calendar icon) or enhanced availability calendar with real-time data

### Technical

- **Calendar System Status:** Fully operational with accurate weekend detection and real-time availability display
- **Form Validation:** All step progression logic updated for new Resort → Timeframe → Date flow
- **Cross-browser Compatibility:** Enhanced CSS rules ensure consistent calendar icon hiding across all major browsers

## [1.26.2] - 2025-09-19

### Added

- **Phase 6: Enhanced Error Handling & Validation:** Implemented a robust, centralized error handling and validation system across the application.
  - **Centralized Error Handling:**
    - Created `ErrorHandler.php` to catch all PHP errors, exceptions, and fatal shutdowns.
    - Integrated `ErrorHandler` into `public/index.php` for application-wide coverage.
    - Ensures consistent error logging and user-friendly error messages instead of raw PHP errors.
  - **Comprehensive Validation Helper:**
    - Developed `ValidationHelper.php` with reusable methods for various data types.
    - Includes validation for booking data, payment submissions, user registration, pricing, facility data, and availability blocking.
    - Supports custom validation rules (e.g., date ranges, password complexity, file uploads).
  - **Controller Integration:**
    - Replaced manual validation logic in `BookingController`, `UserController`, and `AdminController` with `ValidationHelper`.
    - Improved code readability, maintainability, and security by centralizing validation rules.
    - Provides detailed error feedback to users through session messages.

### Changed

- **Validation Workflow:** Transitioned from scattered, manual input validation to a centralized, reusable `ValidationHelper` class.
- **Error Reporting:** Upgraded from default PHP error reporting to a custom `ErrorHandler` for better control and user experience.
- **Controller Logic:** Streamlined controller methods by offloading complex validation rules to the `ValidationHelper`.

### Enhanced

- **Application Security:** Robust input validation minimizes vulnerabilities like SQL injection and cross-site scripting.
- **User Experience:** Clearer, more consistent error messages provide better guidance to users during form submissions.
- **Code Quality:** Centralized validation and error handling improve code maintainability, reduce redundancy, and promote consistency.
- **Developer Efficiency:** Reusable validation logic accelerates development and simplifies future updates.

## [1.26.1] - 2025-09-19

### Implemented

- **Phase 6 Integration Complete:** Successfully completed Phase 6 implementation with full system integration and customer-facing features.
  - **Database Migrations Executed:**
    - Successfully deployed `PaymentSchedules` table for installment payment tracking
    - Successfully deployed `BookingAuditTrail` table for comprehensive change tracking
    - All foreign key relationships and indexes established and operational
  - **Payment Schedule Customer Experience:**
    - Enhanced customer payment form with complete payment schedule display and installment breakdown
    - Added schedule summary showing paid/remaining amounts and overdue payment indicators
    - Implemented "Next Installment" quick payment button for exact installment payments
    - Created interactive payment schedule table with real-time status tracking
  - **Admin Interface Phase 6 Features:**
    - Completely transformed unified booking management with Phase 6 information column
    - Added payment schedule summaries with installment counts and overdue indicators
    - Implemented audit trail change counts and lifecycle manager recommendations
    - Created Phase 6 dropdown menu with audit trail viewing and payment schedule management
    - Added modal interfaces for comprehensive Phase 6 feature access and management
  - **System Integration & Architecture:**
    - Full Phase 6 model integration throughout booking and payment controllers
    - Automated audit trail logging in booking creation and payment submission flows
    - Payment schedule data loading and display in customer payment interfaces
    - Enhanced booking lifecycle management with intelligent recommendation system

### Enhanced

- **Customer Payment Experience:** Payment form now displays complete installment information with next payment due dates and smart payment suggestions
- **Administrative Oversight:** Admin interface provides comprehensive Phase 6 feature access with audit trails, payment schedules, and lifecycle recommendations
- **System Intelligence:** Full integration of booking lifecycle manager with automated status recommendations and change tracking
- **Data Transparency:** Complete audit trail system operational with user attribution and comprehensive change logging

### Technical

- **Phase 6 Implementation Status:** 85% Complete (up from 75% in previous session)
- **System Integration:** All Phase 6 models fully integrated into booking and payment flows
- **Database Status:** All Phase 6 tables operational with foreign key constraints and indexes
- **User Experience:** Enhanced customer and admin interfaces with Phase 6 feature access and management

## [1.26.0] - 2025-09-19

### Added

- **Phase 6: Advanced Features & Refinements:** Implemented sophisticated booking lifecycle management, audit trails, automated status transitions, and enhanced payment validation system.
  - **Comprehensive Audit Trail System:**
    - Created `BookingAuditTrail` table and model for complete tracking of all booking modifications
    - Implemented user attribution with IP address and browser logging for full accountability
    - Added specialized tracking methods: `logBookingCreation()`, `logStatusChange()`, `logPaymentUpdate()`, `logBookingDeletion()`
    - Built advanced querying system with search capabilities, statistics, and audit history retrieval
    - Added automated retention policy and human-readable change descriptions
  - **Automated Booking Lifecycle Management:**
    - Created `BookingLifecycleManager` model with intelligent status transition rules
    - Implemented business logic: auto-confirm when paid, cancel overdue bookings, complete after service
    - Added batch processing system for automated status updates via cron job integration
    - Built validation system for manual status changes with allowed transition rules
    - Created analytics and reporting: lifecycle summaries, attention-required bookings, status recommendations
  - **Enhanced Payment Management System:**
    - Significantly upgraded `Payment` model with smart balance calculation and validation
    - Added advanced payment validation: minimum ₱50, maximum ₱50,000, overpayment prevention
    - Implemented comprehensive payment reporting: summaries, overdue tracking, enhanced processing
    - Integrated all payment operations with audit trail for complete transparency
    - Created intelligent booking status determination based on payment balance
  - **Payment Schedule Infrastructure:**
    - Added `PaymentSchedules` table for installment payment tracking and management
    - Created foundation for due date tracking and automated overdue detection
    - Built integration points with existing Payment and Booking models
  - **Database Enhancements:**
    - Created 2 new migration scripts for Phase 6 database tables
    - Added proper indexing and foreign key relationships for optimal performance
    - Designed scalable architecture supporting enterprise-level audit and lifecycle management

### Changed

- **Payment Processing:** Enhanced from basic payment tracking to comprehensive validation system with business rule enforcement
- **Booking Management:** Transformed from manual status management to intelligent automated lifecycle with audit trails
- **System Transparency:** Elevated from basic logging to complete audit trail with user attribution and change tracking
- **Administrative Control:** Upgraded from reactive management to proactive system with recommendations and automated processing

### Enhanced

- **Data Integrity:** Complete audit trail ensures all booking changes are tracked and attributable
- **Operational Efficiency:** Automated lifecycle management reduces manual administrative overhead
- **Business Compliance:** Advanced payment validation prevents common errors and enforces business rules
- **System Intelligence:** Booking lifecycle manager provides AI-like recommendations and automated decision making

## [1.25.1] - 2025-09-19

### Fixed

- **Critical SQL Error Resolution:** Fixed a fatal database error in the admin "Unified Booking & Payment" interface where ambiguous column references in complex JOIN queries caused the page to crash with "SQLSTATE[23000]: Integrity constraint violation: 1052 Column 'BookingID' in field list is ambiguous". Resolved by properly prefixing table aliases in the `Booking::getBookingsWithPaymentDetails()` method.

- **Bootstrap Navigation Dropdowns:** Resolved non-functional admin navigation dropdown menus where clicking "Booking & Payments", "Pricing & Blocking", and "System" headers did nothing. Fixed by correcting Bootstrap JavaScript loading order, adding explicit dropdown initialization, and implementing backup event listeners for proper dropdown functionality.

- **UI/UX Consistency:** Standardized currency and terminology across all admin interfaces for consistent user experience:
  - **Currency Standardization:** Updated all admin interfaces from US dollar ($) to Philippine peso (₱) throughout facility management forms, pricing displays, and administrative controls.
  - **Terminology Updates:** Replaced outdated "Rate" terminology with "Price" to reflect the new fixed-pricing model (vs. previous hourly-based system).
  - **Context Improvements:** Updated form labels from "Rate per Slot" to "Price per Booking" and "Rate (per hour)" to "Price (₱)" to align with the resort-centric booking system.

### Enhanced

- **Admin Interface Reliability:** All Phase 5 admin management features now fully operational with error-free database queries and proper navigation functionality.
- **System-wide Consistency:** Complete alignment of currency display and pricing terminology across customer-facing and administrative interfaces, improving user comprehension and professional appearance.
- **Developer Experience:** Enhanced maintainability with properly structured SQL queries and consistent Bootstrap component implementation.

## [1.25.0] - 2025-09-19

### Added

- **Phase 5: Admin Management System Enhancement:** Completed comprehensive admin interface transformation providing unified booking/payment management, dynamic pricing controls, and advanced blocking capabilities.
  - **Unified Booking & Payment Management Interface:**
    - Created comprehensive `unified_booking_management.php` view with real-time filtering by resort and booking status
    - Implemented card-based layout with payment status indicators and action buttons for quick management
    - Added on-site payment recording capability bypassing normal proof upload requirements
    - Integrated booking modification tools for adding facilities and updating payment information
  - **Dynamic Pricing Management System:**
    - Built `pricing_management.php` interface for resort-specific timeframe pricing controls (12hrs/24hrs/overnight)
    - Implemented weekend and holiday surcharge management with real-time price calculation
    - Created facility fixed pricing adjustment interface with batch update capabilities
    - Added pricing summary dashboard with visual pricing breakdowns and comparison tools
  - **Advanced Blocking System:**
    - Developed `advanced_blocking.php` interface with preset blocking options for operational efficiency
    - Implemented Philippine holiday blocking system (8 major holidays: New Year's Day, People Power Anniversary, Maundy Thursday, Good Friday, Araw ng Kagitingan, Labor Day, Independence Day, Christmas Day)
    - Added weekend blocking presets with date range selection and custom reason input
    - Created bulk blocking operations with confirmation dialogs and visual calendar feedback
  - **Enhanced Admin Navigation & User Experience:**
    - Reorganized admin navigation into logical dropdown groups: "Booking & Payments", "Pricing & Blocking", "System"
    - Added FontAwesome icons throughout admin interface for improved visual hierarchy and user guidance
    - Implemented mobile-responsive design across all new admin interfaces
    - Created quick action buttons and batch operations for efficient admin workflow
  - **Backend Administrative Methods:**
    - Added `unifiedBookingManagement()` for comprehensive booking and payment oversight interface
    - Implemented `updateBookingPayment()` for on-site payment processing without proof requirements
    - Created `pricingManagement()` and `updateResortPricing()` for dynamic pricing control and timeframe pricing updates
    - Added `updateFacilityPricing()` for facility fixed pricing management across multiple facilities
    - Developed `advancedBlocking()` and `applyPresetBlocking()` for sophisticated date blocking with preset options
    - Implemented `isPhilippineHoliday()` and `getPhilippineHolidays()` for automated holiday detection and blocking
  - **Testing Infrastructure:**
    - Created comprehensive test script `test_phase5_admin_management.php` for validating all Phase 5 features
    - Implemented system integration tests for resort timeframe pricing, unified booking management, and advanced blocking
    - Added verification tools for payment notification system and facility pricing management

### Changed

- **Admin Workflow:** Transformed from scattered management pages to unified, centralized control interfaces with logical feature grouping
- **Pricing Architecture:** Enhanced from basic facility rates to sophisticated resort-centric pricing with conditional surcharges and dynamic calculations
- **Blocking System:** Evolved from manual date entry to intelligent preset system with holiday calendar integration and bulk operations
- **User Experience:** Upgraded admin interfaces from basic forms to modern, responsive dashboards with real-time feedback and batch operations

### Enhanced

- **Administrative Efficiency:** Streamlined booking and payment management into single unified interface reducing administrative overhead
- **Pricing Flexibility:** Implemented comprehensive pricing control system supporting complex business rules and seasonal adjustments
- **Operational Control:** Advanced blocking system enables sophisticated availability management with preset options and holiday awareness
- **System Integration:** Seamless workflow between booking management, payment processing, pricing control, and availability blocking

## [1.24.0] - 2025-09-19

### Added

- **Phase 4: Enhanced User Interface & Experience:** Completed comprehensive UI/UX transformation creating a modern, intuitive booking experience with advanced interactive elements.
  - **Progressive Step Indicators:**
    - Implemented 6-step visual progress tracking system (Resort → Date → Timeframe → Guests → Facilities → Summary)
    - Added dynamic step completion indicators with active/completed state management
    - Smart progression logic that advances users through booking flow automatically
  - **Enhanced Calendar Modal:**
    - Created interactive calendar interface with real-time availability display
    - Implemented color-coded date system (green=available, yellow=weekend, red=unavailable, gray=blocked)
    - Added drag-and-drop date selection with visual feedback and availability validation
    - Built comprehensive month navigation with dynamic data loading
  - **Advanced Booking Form Enhancements:**
    - Enhanced facility selection with card-based interface and capacity validation indicators
    - Added real-time pricing calculator with live updates based on selections
    - Implemented guest capacity validation with visual warnings for facility limits
    - Created dynamic facility loading with pricing display and availability checking
  - **Enhanced Payment Form Interface:**
    - Implemented drag-and-drop file upload system with visual feedback and image preview
    - Added smart payment options (Pay Full Amount, Pay 50%) with instant amount filling
    - Created enhanced payment reference input with validation and formatting
    - Built comprehensive payment method display cards with hover effects and status indicators
  - **System-Wide Icon Integration:**
    - Added FontAwesome 6.4.0 CDN integration for comprehensive icon support
    - Enhanced navigation menu with contextual icons for all user roles
    - Improved visual hierarchy across booking, payment, and admin interfaces
    - Added Bootstrap 5 JavaScript bundle for modal and interactive functionality
  - **New API Endpoints:**
    - Created `getCalendarAvailability()` endpoint for real-time calendar data with availability status
    - Enhanced booking controller with availability checking and date validation methods
    - Added helper methods for weekend detection, booking conflicts, and facility availability

### Changed

- **User Experience Flow:** Transformed booking process from linear form to guided, progressive experience with clear visual feedback at each step
- **Visual Design Language:** Upgraded from basic Bootstrap styling to modern interface with animations, hover effects, and interactive elements
- **Mobile Responsiveness:** Enhanced mobile experience with touch-friendly interfaces, responsive calendar, and optimized form layouts
- **Form Interactions:** Replaced static forms with dynamic, real-time validation and instant feedback systems

### Enhanced

- **JavaScript Architecture:** Implemented comprehensive client-side functionality including state management, event handling, and dynamic UI updates
- **CSS Styling:** Added modern animations, transitions, loading states, and responsive design improvements throughout the system
- **Error Handling:** Improved user feedback with toast notifications, inline validation, and graceful error recovery
- **File Upload System:** Enhanced with drag-and-drop support, image preview, file validation, and progress indicators

## [1.23.0] - 2025-09-18

### Added

- **Phase 3: Payment Integration & Process Flow:** Completed seamless booking-to-payment integration with comprehensive payment management and verification system.
  - **Integrated Booking-to-Payment Flow:**
    - Modified `BookingController::createBooking()` to redirect directly to payment submission after booking creation
    - Eliminated separate payment management - customers now submit payment immediately after booking
    - Implemented seamless transition from booking confirmation to payment interface
  - **Customer Payment Submission System:**
    - Created comprehensive payment interface (`payment.php`) with detailed booking summary and pricing breakdown
    - Implemented secure payment proof upload with image preview and file validation
    - Added payment reference number capture and amount specification (full/partial payments)
    - Built payment success page (`payment_success.php`) with clear next steps and status tracking
  - **Payment Proof Management:**
    - Implemented secure file upload system for payment screenshots with type and size validation
    - Created dedicated storage directory (`public/uploads/payment_proofs/`) with unique filename generation
    - Added real-time image preview functionality and comprehensive error handling
  - **Enhanced Payment Model:**
    - Added `createFromBookingPayment()` method for customer submission integration
    - Implemented `getPendingPayments()`, `verifyPayment()`, and `rejectPayment()` methods for admin workflow
    - Created `getTotalPaidAmount()` for accurate balance calculations and payment history tracking
  - **Admin Payment Verification Interface:**
    - Built comprehensive payment review interface (`pending.php`) with card-based layout
    - Implemented payment proof image viewing with modal display and full-size preview
    - Added one-click verification and rejection with automatic booking status updates
    - Created real-time auto-refresh system for pending payment monitoring
  - **Advanced Notification System:**
    - Implemented `sendPaymentSubmissionNotification()` to alert admins of new payment submissions
    - Added `sendPaymentVerificationConfirmation()` for customer payment status updates
    - Enhanced notification system with detailed booking, customer, and payment information
  - **Customer Experience Enhancements:**
    - Updated `my_bookings.php` with payment status indicators and remaining balance display
    - Added payment action buttons (Submit/Complete Payment) with intelligent state management
    - Implemented dynamic payment flow guidance based on booking and payment status
    - Created responsive design optimized for mobile payment submission

### Changed

- **Booking Workflow:** Transformed from booking-then-payment to integrated booking-to-payment flow eliminating payment step skipping
- **Payment Processing:** Centralized all payment operations through booking workflow with automatic Payment record creation
- **Admin Workflow:** Unified payment verification with booking status management in single interface
- **Customer Journey:** Streamlined payment submission with immediate post-booking payment requirement and clear progress tracking

### Enhanced

- **User Model:** Added `findByRole()` and `getAdminUsers()` methods for role-based notification targeting
- **Payment Controller:** Extended with admin verification methods (`showPendingPayments()`, `verifyPayment()`, `rejectPayment()`)
- **Notification Helper:** Expanded with comprehensive payment-related email templates and automated admin alerts
- **Booking Interface:** Integrated payment status tracking with remaining balance indicators and action button state management

## [1.22.0] - 2025-09-18

### Added

- **Phase 2: Core Booking Logic Transformation:** Completed the transformation of the booking system from facility-centric to resort-centric approach with integrated pricing and payment handling.
  - **Enhanced BookingController:**
    - `createBooking()` method now uses resort-centric logic with `Booking::createResortBooking()`
    - New API endpoints: `getResortPricing()`, `calculateBookingPrice()`, `checkAvailability()`
    - Enhanced `getFacilitiesByResort()` with pricing display information
    - Transaction-based booking creation with comprehensive validation
  - **Complete Booking Form Redesign:**
    - New 6-step booking flow: Resort → Date → Timeframe → Guests → Optional Facilities → Summary
    - Dynamic facility loading and real-time price calculation via AJAX
    - Multiple facility selection with checkbox interface and capacity validation
    - Weekend pricing indicators and live total price updates
    - Smart form validation enabling submit only when required fields are complete
  - **Enhanced Booking Model Methods:**
    - Updated `findByCustomerId()`, `findTodaysBookings()`, `findUpcomingBookings()` to support multiple facilities per booking
    - Simplified `getMonthlyIncome()` to use direct `ResortID` filtering
    - Enhanced `getBookingHistory()` with resort-centric data aggregation using `GROUP_CONCAT` for facility names
  - **Improved Customer Booking Display:**
    - Redesigned `my_bookings.php` with resort-focused table structure
    - Enhanced columns: combined Date & Time, multiple facilities as badges, total price with balance indicator
    - Color-coded status badges and improved responsive design
    - Better action buttons with icons and mobile optimization

### Changed

- **Booking System Architecture:** Transformed from facility-first to resort-first approach where facilities are optional add-ons
- **User Interface:** Booking form now features intuitive step-by-step flow with real-time feedback and dynamic pricing
- **Data Display:** All booking displays now properly handle multiple facilities per booking using junction table relationships
- **API Architecture:** Added RESTful endpoints for dynamic pricing calculation and availability checking

### Fixed

- **Admin Interface Compatibility:** Updated admin dashboard and user booking views to support Phase 2 resort-centric booking changes
  - Enhanced admin dashboard tables to display resort names, multiple facilities as badges, and total pricing information
  - Updated user booking management interface with resort-focused layout and payment tracking
  - Added backward compatibility for mixed booking data (legacy facility-centric and new resort-centric)
  - Improved responsive design and status visualization across all admin booking displays

## [1.21.0] - 2025-09-18

### Added

- **Phase 1: Database Schema Evolution:** Completed the first phase of transforming the system from facility-centric to resort-centric booking with integrated pricing and payment handling.
  - **New Tables:**
    - `ResortTimeframePricing`: Stores resort-specific pricing for different timeframes (12 hours, 24 hours, overnight) with weekend and holiday surcharge support.
    - `BookingFacilities`: Junction table enabling multiple facility selection per booking, supporting the new booking model.
    - `ResortPaymentMethods`: Configuration table for resort-specific payment methods (Gcash, Bank Transfer, Cash).
  - **Enhanced Bookings Table:** Added `ResortID`, `TotalAmount`, `PaymentProofURL`, `PaymentReference`, and `RemainingBalance` columns for comprehensive payment tracking.
  - **New Model Classes:**
    - `ResortTimeframePricing.php`: Handles dynamic pricing calculations with weekend detection and surcharge application.
    - `BookingFacilities.php`: Manages multiple facilities per booking with transaction support.
    - `ResortPaymentMethods.php`: Payment method configuration with automatic default setup.
  - **Enhanced Existing Models:**
    - `Booking.php`: Added resort-centric booking methods (`createResortBooking()`, `isResortTimeframeAvailable()`, `updatePaymentInfo()`, `calculateBookingTotal()`).
    - `Facility.php`: Added pricing calculation methods for facility add-ons (`getFixedPrice()`, `getFacilitiesForBooking()`).

### Changed

- **Database Architecture:** Transformed the core booking system to support resort-first booking flow with facilities as optional add-ons.
- **Pricing Model:** Implemented timeframe-based pricing with conditional surcharges replacing the previous hourly rate system.
- **Payment Integration:** Enhanced payment tracking with proof upload capabilities and reference number management.

## [1.20.9] - 2025-09-17

### Fixed

- **Admin Scheduling Modal:** Resolved a critical bug where the "Existing Blocks" section of the resort scheduling modal would get stuck on a "Loading..." message. The issue was traced to a fatal PHP error ("Class 'BlockedResortAvailability' not found") in the `AdminController`, which has been fixed by adding the required model file.
- **JavaScript Error Handling:** Hardened the frontend JavaScript for the scheduling modals by adding `.catch()` blocks to `fetch()` requests. This ensures that any future backend errors will be gracefully handled and reported in the browser console instead of causing the UI to fail silently.

### Changed

- **Performance Optimization:** Significantly improved the load time of the main "Management" page by fixing a major "N+1 query problem." Replaced an inefficient loop that made individual database calls for each resort's facilities with a single, optimized query (`Resort::findAllWithFacilities()`) that fetches all necessary data at once.

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
