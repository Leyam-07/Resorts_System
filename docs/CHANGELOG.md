# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1/0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.43.8] - 2025-10-19

### Fixed

- **Resort Image Display:** Resolved a critical bug where resort images in the "Edit Resort" modal were not displaying correctly. The issue was caused by using relative image paths on the client-side without a proper base URL. The fix ensures all image paths are absolute, guaranteeing they load correctly regardless of the page URL or server port.

### Files Updated

- `app/Views/partials/header.php`
- `app/Views/admin/resorts/resort_modals.php`

## [1.43.7] - 2025-10-19

### Enhanced

- **Admin UI/UX:** Improved the visual organization of the "Manage Resorts" page by color-coding the action buttons. "Pricing Management" is now green, "Advanced Blocking" is yellow, "Edit Resort" is blue, and "Add Facility" is teal, making the interface more intuitive.

### Changed

- The "Add New Resort" button color was changed to teal to match the "Add Facility" button for consistency.

### Files Updated

- `app/Views/admin/management/index.php`

## [1.43.6] - 2025-10-19

### Enhanced

- **Resort Creation Workflow:** Transformed the "Add Resort" process into a modern, asynchronous workflow. After successfully adding a resort, a confirmation modal now appears, prompting the admin to proceed directly to the pricing management page for the new resort. This replaces the old, disruptive page-reload confirmation.
- **Consistent Deletion Feedback:** Standardized the feedback mechanism for deleting resorts across all admin interfaces. Deleting a resort from any management page now reliably displays a session-based success or error message, eliminating user confusion.

### Fixed

- **Missing Deletion Feedback:** Resolved a bug where no visual confirmation was shown after deleting a resort from the `resorts/index.php` page. The system now uses consistent session-based alerts for all deletion actions.

### Files Updated

- `app/Controllers/AdminController.php`
- `app/Controllers/ResortController.php`
- `app/Views/admin/resorts/index.php`
- `app/Views/admin/resorts/resort_modals.php`

## [1.43.5] - 2025-10-19

### Fixed

- **Accordion State Persistence:** Resolved a user experience issue on the "Manage Resorts" page where the accordion would always default to the first resort after any modification. The system now correctly remembers and reopens the last active accordion item after a page reload.

### Files Updated

- `app/Controllers/AdminController.php`
- `app/Views/admin/management/index.php`
- `app/Views/admin/management/edit_facility_modal.php`

## [1.43.4] - 2025-10-19

### Fixed

- **Application Error Page:** Resolved a critical issue where the error page (`public/error.php`) displayed a blank white screen instead of a proper error message. The `BASE_URL` constant was not defined, causing a fatal PHP error and preventing the stylesheet from loading. The file is now self-sufficient and renders correctly.

### Files Updated

- `public/error.php`

## [1.43.3] - 2025-10-19

### Fixed

- **Facility Deletion Functionality:** Resolved a critical bug where the "Delete" button for resort facilities on the admin management page was non-functional.
- **Fatal Error on Deletion:** Fixed a `Call to undefined method BookingFacilities::findByFacilityId()` error that occurred during facility deletion by implementing the missing dependency-checking method in the `BookingFacilities` model.

### Enhanced

- **Data Integrity:** Implemented a dependency check in the `AdminController` to prevent the deletion of facilities that are associated with existing bookings, ensuring data integrity.
- **UI/UX Consistency:** Standardized all user-facing feedback messages (e.g., "Facility added," "Resort deleted") for resort and facility management actions. The UI now provides a consistent, clean appearance by removing icons and standardizing the text.
- **Modal Consistency:** The "Delete Resort" modal was updated to match the style of the "Delete Facility" modal, now including a consistent warning note about dependencies.

### Files Updated

- `app/Controllers/AdminController.php`
- `app/Views/admin/management/index.php`
- `app/Views/admin/management/facility_modals.php`
- `app/Models/BookingFacilities.php`
- `app/Views/admin/resorts/resort_modals.php`

## [1.43.2] - 2025-10-19

### Removed

- **Deprecated Resort Capacity Feature:** Completely removed the resort-level capacity field from the system as per client requirements. This includes:
  - **Database Schema:** Dropped the `Capacity` column from the `Resorts` table via migration script `scripts/migrations/remove_capacity_from_resorts.php`.
  - **Admin UI:** Removed capacity input fields from both "Add Resort" and "Edit Resort" modals in `app/Views/admin/resorts/resort_modals.php`.
  - **Customer UI:** Eliminated capacity display from resort details modal in `app/Views/partials/footer.php`.
  - **Code Cleanup:** Purged all capacity-related logic from models, controllers, and views to fully deprecate the feature.
  - **Scripts:** Deleted deprecated script `scripts/check_resort_capacity.php`.
  - **Impact:** System now operates without enforcing resort capacity limits, allowing operators to manage usage through operational practices rather than technical restrictions.

### Files Updated

- `docs/Database-Schema.md` - Updated schema to remove Capacity column
- `app/Views/admin/resorts/resort_modals.php` - Removed capacity fields
- `app/Views/partials/footer.php` - Removed capacity display
- `scripts/check_resort_capacity.php` - Deleted deprecated script

## [1.43.1] - 2025-10-18

### Fixed

- **Staff Dashboard Display:** Resolved a critical bug where "Today's Bookings" and "Upcoming Bookings" on the Staff Dashboard were not displaying correctly, showing as empty even when bookings existed.
  - **Data Fetching:** Corrected the database query in `app/Models/Booking.php` to properly filter today's bookings by 'Confirmed' and 'Pending' statuses, ensuring relevant bookings are shown.
  - **Display Logic:** Fixed the view in to use the correct data properties for displaying time slots (`TimeSlotType`) and facility names (`FacilityNames`), resolving incorrect data rendering.

### Enhanced

- **Staff Dashboard UI/UX:** Significantly improved the styling and consistency of the Staff Dashboard based on user feedback and alignment with existing application styles.
  - **Consistent Styling:** Aligned the table layout, status badge colors, and facility display with the `my_bookings.php` view for a more professional and consistent user experience.
  - **Improved Readability:** Centered the "Status" column and implemented appropriate badges for facilities (`bg-info`) and booking statuses (`bg-success`, `bg-warning`), making the interface more intuitive.

### Files Updated

- `app/Models/Booking.php`
- `app/Views/admin/staff_dashboard.php`

## [1.43.0] - 2025-10-18

### Enhanced

- **Payment Modal Clarity:** Improved the "Submit Payment" modal on the "My Bookings" and "My Reservations" pages by including a list of selected facilities in the "Booking Summary" section.
  - **Facility Display:** The modal now clearly lists all facilities included in the booking, providing customers with a complete summary before they submit payment.
  - **Consistent Styling:** The facility badge styling in the modal now matches the rest of the application, with "Resort access only" appearing with a secondary background for better visual consistency.

### Files Updated

- `app/Views/booking/my_bookings.php`
- `app/Views/booking/my_reservations.php`

## [1.42.9] - 2025-10-18

### Fixed

- **Facility Selection on New Bookings:** Resolved a critical bug where selected additional facilities were not being saved with new reservations, and their cost was not included in the total amount.
  - **Root Cause:** The `ValidationHelper::validateBookingData()` method was missing a validation rule for `facility_ids`. This caused the validation system to strip the facility data from the request before it could be processed by the `BookingController`.
  - **Solution:** Added the `'facility_ids' => 'array'` rule to the validation array in `app/Helpers/ValidationHelper.php`, ensuring that the array of selected facilities is correctly processed and saved with the booking.

### Files Updated

- `app/Helpers/ValidationHelper.php`

## [1.42.8] - 2025-10-17

### Enhanced

- **Alert Dismissibility:** Added close icons to all alert messages throughout the application using Bootstrap 5 dismissible alert components.
  - **User Interface Alerts:** Updated 8 user-facing views (`register.php`, `booking/create.php`, `booking/my_reservations.php`, `booking/success.php`, `booking/payment_success.php`, `booking/confirmation.php`, `profile.php`, `login.php`) to include dismissible functionality for success and error messages.
  - **Admin Interface Verification:** Confirmed existing admin views (`advanced_blocking.php`, `payments/pending.php`, `management/index.php`) already had proper dismissible alerts implemented.
  - **Consistent Implementation:** All alerts now use the standard Bootstrap 5 structure with `alert-dismissible fade show` classes and `<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`
  - **Improved User Experience:** Users can now dismiss alert messages across the entire application, providing better control over their interface and reducing visual clutter.

## [1.42.7] - 2025-10-17

### Fixed

- **Duplicate Date Blocking:** Resolved a critical bug in the "Advanced Blocking System" where administrators could block the same date multiple times for the same resort or facility. The system now validates for existing blocks before creating a new one.

### Enhanced

- **Blocking System User Experience:** Implemented comprehensive feedback improvements for the date blocking and deblocking system.
  - **Clearer Feedback:** The system now provides explicit success or error messages for all blocking actions.
  - **Informative Bulk Operations:** Preset blocking actions (e.g., blocking a date range) now report a detailed summary, including the number of dates successfully blocked and the number of dates skipped because they were already blocked.
  - **Descriptive Deblocking:** Deblocking actions now confirm how many blocks were removed or inform the user if no blocks were found to remove.
  - **Dismissible Alerts:** All feedback messages now feature a close icon, allowing users to dismiss them.

## [1.42.6] - 2025-10-17

### Fixed

- **Advanced Blocking UI State:** Resolved two user experience issues in the "Advanced Blocking System" where the UI state was not preserved across page reloads.
  - **Tab Persistence:** The active tab (e.g., "Facility Blocking") is now correctly retained after any form submission, preventing the interface from resetting to the default tab.
  - **Content Display:** The "Current Blocked Dates" section now correctly displays the content corresponding to the active tab on page load, eliminating the need for users to switch tabs back and forth to see the correct information.

## [1.42.5] - 2025-10-16

### Fixed

- **Payment Submission Delay:** Resolved a critical performance issue where submitting a payment proof would cause the system to hang for two minutes or more. The delay was caused by sending emails synchronously, which blocked the user's request.

### Changed

- **Asynchronous Email System:** Refactored the entire notification system to send all emails asynchronously.
  - **Background Worker:** Created a centralized background worker script (`scripts/send_email_worker.php`) to handle all email sending, ensuring user-facing actions are no longer delayed by network latency from the mail server.
  - **Controller Integration:** Updated `BookingController`, `PaymentController`, and `UserController` to trigger the background worker for all email events (e.g., booking creation, payment verification, user registration) instead of sending them directly.
  - **Improved Responsiveness:** All user actions that trigger an email are now instantaneous, significantly improving the overall application performance and user experience.

## [1.42.4] - 2025-10-16

### Fixed

- **Holiday & Weekend Rate Stacking:** Resolved a critical pricing issue where holiday and weekend surcharges were incorrectly stacked on dates that were both a holiday and a weekend. The system now correctly prioritizes holiday rates.
  - **Backend Logic:** Updated `ResortTimeframePricing::calculatePrice()` to use an `if-else` structure, ensuring only the holiday surcharge is applied if a date is a holiday.
  - **API Response:** Corrected the `BookingController::getResortPricing()` method to reflect this priority, preventing the API from sending both surcharges to the frontend.
  - **Frontend Display:** Fixed the JavaScript in the booking form (`create.php`) to ensure only the "Holiday Rate" badge is displayed when applicable, removing the stacked "Weekend Rate" badge.

## [1.42.3] - 2025-10-16

### Security

- **Admin Deletion Safeguards:** Implemented critical security enhancements to the user management system to prevent administrators from deleting other admin accounts or their own account.
  - **Controller Logic:** Added validation in `AdminController` to check the role of the user being deleted and to check if the user is deleting themselves.
  - **User Feedback:** Implemented user-facing error messages on the "Manage Users" page to inform administrators why a deletion was denied.

## [1.42.2] - 2025-10-13

### Enhanced

- **Guest View UI/UX Refinements:** Implemented a series of visual and functional improvements to the "Guest View" to ensure a more consistent and polished user experience.
  - **"New Reservation" Page Overhaul:**
    - **Visual Parity:** The guest-facing "New Reservation" page now perfectly mimics the layout of the logged-in version, including all booking steps (Timeframe, Date, Facilities) in a disabled state.
    - **Styling Consistency:** Corrected the styling of resort cards, step-indicator circles, and dimmed the "Timeframe" and "Date" selection cards to match the interactive version's look and feel.
    - **Standardized Login Prompt:** Replaced the custom overlay prompt with the standard alert message ("Please login or register...") found on other guest pages for a consistent look.
  - **Dashboard Modal Usability:** Added a "Close" button to the footer of the "View Details" modals for both resorts and facilities on the guest dashboard, providing a clear and expected action for non-logged-in users.

## [1.42.1] - 2025-10-13

### Added

- **Comprehensive Guest View Implementation:** Introduced a "Guest View" for non-logged-in users, providing a non-interactive preview of the customer experience to encourage registration.
  - **Public Dashboard:** The main dashboard is now the default landing page, allowing guests to browse resorts and view details in modals without logging in.
  - **Guest-Specific Navigation:** Created a new guest header with a familiar navigation layout but with "Login" and "Register" calls-to-action.
  - **Non-Interactive Page Previews:**
    - The "New Reservation" page now displays a disabled, data-populated version of the booking form, demonstrating the booking process.
    - "My Bookings," "My Reservations," and "My Profile" pages now mimic the structure of the logged-in versions but display an empty state, prompting users to sign up.
  - **Conditional UI:** The main router (`public/index.php`) and controllers (`UserController`, `BookingController`) were updated to seamlessly serve the appropriate views based on login status.
  - **Modal Adjustments:** JavaScript for modals was updated to hide action buttons (e.g., "Book Now") for guest users.

## [1.42.0] - 2025-10-13

### Changed

- **Cancelled Booking Display on "My Reservations" Page:** Enhanced the user experience for customers viewing their cancelled reservations.
  - **Payment Button Removal:** The "Submit Payment" button has been completely removed for bookings with `Status` of 'Cancelled', as payment is not applicable for cancelled reservations.
  - **Action Label Enhancement:** Changed the generic text span for cancelled bookings from "Cancelled" to a more descriptive styled badge using "Reservation Cancelled" with Bootstrap's `bg-secondary` class for better visual consistency and professionalism.
  - **Payment Modal Logic:** Updated the condition for showing the payment modal button to exclude cancelled bookings, preventing accidental payment attempts for invalid reservations.

## [1.41.9] - 2025-10-13

### Changed

- **Customer Booking Management:** Overhauled the customer booking interface for better clarity and user experience.
  - **"My Bookings" Page:** This page has been split into two distinct sections:
    - **"My Reservations":** Displays pending and canceled bookings, with a navigation counter for pending reservations.
    - **"My Bookings":** Now exclusively shows confirmed and completed bookings, with a navigation counter for confirmed bookings.
  - **Renamed "New Booking":** The "New Booking" page and navigation link have been renamed to "New Reservation" for consistency.
- **Booking Cancellation:** The booking cancellation logic has been updated. Instead of deleting the booking record, the status is now set to "Cancelled," ensuring the record is preserved for transparency on the "My Reservations" page. The redirection after cancellation now correctly returns the user to the "My Reservations" page.

### Fixed

- **Payment Modal:** Fixed a critical bug where the payment modal on the "My Reservations" page was not loading correctly. The necessary JavaScript for handling the modal has been added to the `my_reservations.php` view.
- **Booking Confirmation Redirect:** Corrected the redirection link on the booking confirmation page to point to "My Reservations" instead of "My Bookings."

## [1.41.8] - 2025-10-12

### Changed

- **Booking Form UI/UX:** Overhauled the "Select Date" section on the New Booking page, replacing the previous input/button combination with a modern, fully clickable card interface. This aligns its design with the other card-based selections on the page for a more consistent and intuitive user experience.

### Fixed

- **JavaScript Stability:** Resolved a series of `TypeError: Cannot set properties of null` exceptions on the New Booking page. These errors were caused by scripts referencing element IDs that were removed during recent UI refactoring. All event listeners and element selectors have been updated to target the new card-based components.
- **Form Usability:** Corrected a UI flaw where the "Select Timeframe" and "Select Date" cards were interactive before a resort was chosen. These sections are now visually dimmed and disabled until their preceding steps are completed, properly guiding the user through the booking workflow.

### Technical

- **DOM Manipulation:** Refactored JavaScript to use class-based selectors (`.date-card`) instead of IDs for UI components that were changed. Replaced `.disabled` property manipulation with CSS class toggles (`opacity-50`, `pe-none`) for better visual state management.

### Files Updated

app/Views/booking/create.php

## [1.41.7] - 2025-10-12

### Fixed

- **Base Price Calculation Fix:** Resolved critical pricing display issue on the New Booking page where weekend and holiday pricing was showing as direct total price instead of displaying base price with separate surcharge amounts.
  - **Backend Pricing Logic:** Modified `BookingController::getResortPricing()` to return actual base price from database table instead of calculated total, and added new `appliedSurcharges` array with formatted surcharge details (type, amount, display text).
  - **Frontend Pricing Display:** Updated JavaScript `loadTimeframePricing()` function to handle new API response format and display surcharges with proper "+" indicators, color-coded types (warning for weekend, info for holiday), and formatted amounts.
  - **UI Enhancement:** Added surcharge breakdown section in timerframe card showing individual charges (e.g., "+ ₱200 Weekend Surcharge") and enhanced booking summary section with consistent pricing display.
  - **Pricing Clearance:** Added pricing data reset when date/timeframe changes to prevent cached data corruption and ensure accurate real-time updates.
  - **User Experience:** Display now correctly shows Base Price: ₱X.XX + Weekend Surcharge: + ₱Y.YY + Holiday Surcharge: + ₱Z.ZZ = Total instead of showing total as base price.

### Technical

- **API Response Enhancement:** Modified pricing endpoint to return structured surcharge data with type classification and formatted display strings.
- **Client-Side State Management:** Improved JavaScript pricing handling with concurrent-purchaser-style display updates and global pricing data caching.
- **Data Integrity:** Implemented pricing data clearing on user selections to prevent stale data displaying incorrect surcharges.

### Files Updated

app/Controllers/BookingController.php
app/Views/booking/create.php

## [1.41.6] - 2025-10-12

### Changed

- **Card-Based Timeframe Selection:** Converted the "Select Timeframe" section on the New Booking page from a dropdown menu to a modern card-based interface with three option cards: "12 Hours", "24 Hours", and "Overnight". This improves user experience and maintains consistency with other form sections.
  - Added check-in/check-out time indicators on each card:
    - 12 Hours: Check In 7:00 AM, Check Out 5:00 PM
    - 24 Hours: Check In 7:00 AM, Check Out 5:00 AM (next day)
    - Overnight: Check In 7:00 PM, Check Out 5:00 AM (next day)
  - Implemented visual selection feedback with hover effects and blue highlighting
  - Refactored JavaScript to use `selectedTimeframe` variable instead of dropdown element references
  - Maintained progressive step validation and calendar modal integration
  - Enhanced mobile responsiveness with proper card stacking

### Technical

- **JavaScript Code Quality:** Completely refactored timeframe selection logic to remove all `timeSlotSelect` variable references and implement state-based timeframe tracking
- **UI Consistency:** Aligned timeframe cards with existing resort and facility card selection interfaces, including hover animations and selection states
- **Form State Management:** Updated step indicators, validation logic, and API calls to work seamlessly with the new card-based selection system

### Files Updated

app/Views/booking/create.php

## [1.41.5] - 2025-10-12

### Fixed

- **Guest Functionality Cleanup Completion:** Resolved all remaining references to the deprecated `NumberOfGuests` field that was removed via database migration.
  - **View Template Corrections:** Fixed critical runtime errors in payment success and booking confirmation views caused by undefined `$booking->numberOfGuests` properties. Removed hardcoded guest displays that would crash after migration execution.
  - **Email Template Cleanup:** Eliminated `numberOfGuests` references from booking confirmation and cancellation email templates in `Notification.php` helper to prevent email sending failures.
  - **Controller Logic Updates:** Removed obsolete guest-related parameters from `AdvancedAvailabilityChecker` methods (`checkAvailabilityDetailed`, `checkFacilitiesAvailability`) that used capacity-based filtering logic no longer applicable.
  - **Method Signature Corrections:** Fixed incorrect parameter passing in `AdvancedAvailabilityChecker::generateOptimizationSuggestions()` method call to `suggestComplementaryFacilities()` that was still passing a deprecated `$numberOfGuests` parameter.
  - **Model Audit Trail Refinement:** Updated `BookingAuditTrail` logging to remove guest count formatting from booking creation summaries, ensuring consistent audit message structure.
  - **Form Validation Cleanup:** Eliminated `validateGuestCapacity()` function and guest-related validation comments from booking creation form, simplifying the reservation workflow.
  - **Documentation Synchronization:** Updated `Database-Schema.md` to remove abandoned `NumberOfGuests` column from database schema documentation, maintaining accuracy after migration.
  - **Email Notification Fixes:** Cleaned `BookingController` notification methods to remove undefined property access that caused booking confirmation emails to fail silently.

### Technical

- **Runtime Stability:** Eliminated undefined property errors that previously caused page crashes during booking confirmation and payment completion workflows.
- **Data Consistency:** Ensured complete removal of guest counting logic across all application layers (frontend, backend, email systems, audit trails).
- **System Reliability:** Resolved concurrent issues where booking payments, confirmations, and email notifications would fail due to outdated property references.
- **Codebase Health:** Performed comprehensive cleanup of legacy guest capacity validation and display logic, simplifying codebase maintenance.

### Files Updated

app/Views/booking/payment_success.php
app/Views/booking/confirmation.php
app/Controllers/BookingController.php
app/Helpers/Notification.php
app/Models/AdvancedAvailabilityChecker.php
app/Models/BookingAuditTrail.php
app/Views/booking/create.php
docs/Database-Schema.md

## [1.41.4] - 2025-10-12

### Removed

- **NumberOfGuests Field Removal:** Complete removal of guest counting field from the Integrated Digital Management System as per client requirements. System now operates without enforcing guest capacity limits, simplifying the booking flow for resorts serving 1-10 guests maximum.
  - **Database Migration:** Executed `scripts/migrations/remove_numberofguests_from_bookings.php` to drop NumberOfGuests column from Bookings table
  - **Backend Refactoring:** Removed all guest-related properties, validation, and method parameters from:
    - Booking.php model (guest property, SQL references, method signatures)
    - BookingLifecycleManager.php (guest assignments in data flow)
    - BookingController.php (removed guest parameters and validation)
    - ValidationHelper.php (guest validation rules)
  - **Frontend Cleanup:** Updated booking creation interface to remove guest input fields:
    - Removed guest number input from booking/create.php
    - Simplified 6-step booking flow to 5 steps (removed guest selection step)
    - Updated step indicators to reflect streamlined process
    - Removed all JavaScript guest validation logic and form handling
  - **Admin Dashboard Updates:** Removed guest columns from all booking management tables:
    - dashboard.php (admin main dashboard booking tables)
    - staff_dashboard.php (staff operational view)
    - view_user_bookings.php (user-specific booking display)
  - **Impact:** System maintains simplified booking flow without capacity enforcement, allowing resorts to manage guest limits through operational practices rather than technical restrictions

### Changed

- **Booking Flow Simplification:** Streamlined customer booking experience by removing mandatory guest count requirement, reducing form friction and enabling faster reservations
- **System Architecture:** Eliminated guest capacity validation logic throughout the application stack, providing resort operators greater operational flexibility

### Technical

- **Database Schema:** Permanent removal of guest counting capability from underlying data structure
- **Application Logic:** Complete refactoring of booking creation and management workflows to exclude guest capacity considerations
- **User Experience:** Simplified booking interface with reduced required fields and fewer decision points

### Files Updated

scripts/migrations/remove_numberofguests_from_bookings.php
app/Models/Booking.php
app/Models/BookingLifecycleManager.php
app/Controllers/BookingController.php
app/Helpers/ValidationHelper.php
app/Views/booking/create.php
app/Views/admin/dashboard.php
app/Views/admin/staff_dashboard.php
app/Views/admin/view_user_bookings.php

## [1.41.3] - 2025-10-12

### Changed

- **Staff Side RBAC Modifications:** Refined role-based access control for Staff users to align with business requirements.
  - **Removed** Staff access to "Preview Customer View" feature completely - removed navigation link and enforced Admin-only access via URL
  - **Added** Staff access to "View Feedback" page with identical functionality to Admin side (read-only resort-filterable feedback display)
  - Updated Staff navigation to include "Dashboard" and "View Feedback" links only

### Added

- **Staff Feedback Access:** Staff users can now access and review customer feedback across all resorts using the same interface as Admin, supporting operation oversight without management privileges

### Files Updated

app/Views/partials/header.php
app/Controllers/AdminController.php
app/Controllers/FeedbackController.php

## [1.41.2] - 2025-10-08

### Fixed

- **Facility Blocking Display Fix:** Resolved critical user experience issue where blocked facilities were not visibly indicated as unavailable during booking process. Customers could still select blocked facilities, only discovering they were unavailable after form submission failure.
  - **Backend Blocking Detection:** Added `isFacilityBlockedOnDate($facilityId, $date)` method to `BlockedFacilityAvailability` model for reliable blocking status checks.
  - **API Enhancement:** Modified `BookingController::getFacilitiesByResort()` to accept optional `date` parameter and include blocking status (`isBlocked`) in facility data responses.
  - **Dynamic UI Updates:** Updated JavaScript logic to reload facilities when date/timeframe changes, ensuring real-time blocking status display.
  - **Visual Blocking Indicators:** Enhanced `renderFacilities()` function with comprehensive blocked facility styling:
    - Reduced opacity (0.7) and grayscale filtering for blocked facility images
    - Disabled checkboxes preventing selection of blocked facilities
    - Added "(Blocked)" text labels below blocked facility names
    - Implemented prominent "Unavailable" overlay badges with enhanced styling (increased padding, font size, shadow)
  - **Pointer Events Management:** Disabled card click handlers for blocked facilities to prevent selection attempts.
  - **URL Parameter Prevention:** Enhanced pre-selection logic to skip blocked facilities when accessed via URL parameters.

### Enhanced

- **Facility Blocking UX:** Completely transformed blocked facility presentation with immediate visual feedback, preventing customer confusion and form submission failures.
- **Real-time Availability Display:** Facilities now update dynamically as customers change dates, instantly reflecting current blocking status.
- **Accessibility Improvements:** Larger, more prominent "Unavailable" badges with improved contrast and readability for better user comprehension.

### Technical

- **Database Query Optimization:** Introduced dedicated blocking status queries to support real-time availability checking without performance impact.
- **Client-Side State Management:** Enhanced JavaScript state tracking to handle blocked facility interactions seamlessly across date/timeframe changes.
- **Error Prevention:** Proactive blocking display eliminates need for post-submission error recovery and page reloads.

### Files Updated

app/Models/BlockedFacilityAvailability.php

- Added `isFacilityBlockedOnDate()` method for blocking status detection

app/Controllers/BookingController.php

- Enhanced `getFacilitiesByResort()` with date parameter and blocking status inclusion

app/Views/booking/create.php

- Updated JavaScript for dynamic facility reloading and blocking status rendering
- Enhanced CSS styling for blocked facility visual indicators (.facility-blocked, .blocked-overlay)
- Improved accessibility with larger, more readable "Unavailable" badges

## [1.41.1] - 2025-10-08

### Added

- **Enhanced Payment Submission Confirmation Email:** Implemented customer information section in payment submission confirmation emails for improved user verification and communication clarity.
  - **Customer Information Section:** Added a dedicated "Customer Information" section to the payment submission confirmation email, displaying the customer's full name and phone number (excluding email for privacy).
  - **Improved Email Structure:** Positioned customer information at the top of the email for better visual hierarchy and immediate customer verification of their details.
  - **Enhanced User Experience:** Customers now receive comprehensive payment submission acknowledgments with their verified contact information for record-keeping purposes.
  - **Privacy Compliance:** Email addresses excluded from customer information section while maintaining necessary contact details for booking confirmation.

### Files Updated

app/Helpers/Notification.php

- Modified `sendPaymentSubmissionConfirmation()` method to include customer information section

## [1.41.0] - 2025-10-08

### Changed

- **Booking Confirmation Page Layout:** Revised the Booking Confirmation page layout to be similar with Payment Success.
  - Customer Information is now on its own section (separate full-width row).
  - Booking Details and Payment Information are on the same row (two-column layout).
  - Added centered header with check-circle icon for consistent visual hierarchy.
  - Enhanced formatting with div.mb-2 wrappers and bullet-point facilities list.
  - Added Booking ID display and improved remaining balance color coding (warning for pending, success for paid).
  - Included payment method, reference, and status fields where available for complete summary transparency.

## [1.40.9] - 2025-10-08

### Added

- **Invoice Compact Layout Optimization:** Implemented significant layout improvements to the invoice PDF for single-page printing and better readability.
  - **Reduced Margins and Spacing:** Optimized body margins from 20px to 15px, table padding from 8px to 5px, and section spacing throughout for compact single-page design.
  - **Enhanced Font Hierarchy:** Introduced progressive font sizing (13px base, 18px h1, 15px h2) for clear visual structure while maintaining readability.
  - **Table Cell Padding Optimization:** Reduced table cell padding from 8px to 5px and implemented consistent font sizing (12px content, 11px headers) for efficient data presentation.
  - **Improved Line Height:** Set optimal 1.4 line height for better text flow without excessive vertical spacing.
  - **Professional Column Layout:** Placed customer information and invoice details in side-by-side columns using CSS table layout for perfect alignment and balanced visual weight.
  - **Enhanced Readability:** Increased font weights where appropriate while maintaining compact design for comfortable reading at small sizes.
  - **Portrait Page Optimization:** Ensured all content fits within A4 portrait page dimensions with proper margins and spacing relationships.

### Fixed

- **Invoice Layout Alignment:** Resolved label alignment issues by implementing a table-cell structure for customer and invoice information sections, ensuring all labels align perfectly with their corresponding values.

### Technical

- **CSS Layout Refinement:** Completely revamped invoice CSS with modular, maintainable styling that prioritizes single-page printing while preserving professional appearance.
- **Content Prioritization:** Balanced information density with readability by strategically reducing spacing in non-critical areas while maintaining clarity in data sections.
- **Cross-Device Compatibility:** Ensured PDF rendering consistency across different viewers and print settings through careful CSS optimization.

## [1.40.8] - 2025-10-08

### Added

- **Invoice Generation System:** Implemented a comprehensive invoice system on the Payment Success page featuring professional PDF downloads with complete booking and payment details.
  - **Invoice Display Card:** Added an "Invoice" card to the Payment Success page with clear awareness messaging and PDF generation button.
  - **Professional PDF Invoice:** Created detailed HTML invoice template using DomPDF library with resort branding, service breakdown, payment history, and formatted layouts.
  - **PDF Generator Integration:** Integrated DomPDF library for HTML-to-PDF conversion with proper styling and professional appearance.
  - **Payment Reference Tracking:** Added database migration (`scripts/migrations/add_reference_to_payments.php`) to support payment reference numbers in invoices and improved Payment model to store references.
  - **Database Schema Enhancement:** Extended Payments table with Reference column for payment identification and invoice display.
  - **Currency Standardization:** Replaced Unicode peso symbol (₱) with text-based "PHP" currency notation for reliable PDF rendering across all systems.

### Fixed

- **PHP Error Resolution:** Resolved multiple critical PHP errors in the invoice generation system:
  - Fixed undefined property `$Rate` → `$FacilityRate` in BookingFacilities queries
  - Fixed undefined property `$CreatedAt` → `$PaymentDate` in Payment history display
  - Fixed undefined property `$Reference` → Added proper database column and model updates
  - Corrected array key usage from `'Rate'` to `'facilityPrice'` for consistent data retrieval
- **PDF Character Encoding:** Resolved Unicode symbol rendering issues that caused peso symbol to display as question marks in PDF documents.
- **Database Query Alignment:** Synchronized SQL query aliases with PHP property access for reliable data retrieval in invoice generation.

### Enhanced

- **Invoice Professionalism:** Professional invoice layout with resort branding, detailed service breakdown (base fees + facilities), payment history with amounts, and consistent formatting for business documentation.
- **Data Integrity:** Improved BookingFacilities model query accuracy and Payment model storage capabilities with reference tracking.
- **Payment System Integration:** Enhanced payment processing to store reference numbers for complete transaction documentation and invoice generation.
- **UI Integration:** Seamlessly integrated invoice functionality into payment success workflow with prominent awareness messaging and easy PDF access.

### Technical

- **DomPDF Implementation:** Added Html2Pdf library integration for reliable PDF generation from HTML templates.
- **Database Migration:** Created system for adding new payment reference column with existing data compatibility.
- **Currency Handling:** Implemented text-based currency notation for cross-platform PDF rendering consistency.
- **Error Resolution:** Comprehensive debugging and property synchronization across invoice generation system.

### Files Updated

app/Views/booking/payment_success.php

- Added Invoice display card with download button

app/Controllers/BookingController.php

- Added generateInvoice() method and getInvoiceHTML() for PDF generation
- Fixed undefined property references and currency display
- Enhanced data retrieval for facility rates and payment history

app/Models/Payment.php

- Added reference property and storage capability
- Updated create() method for Reference column insertion

app/Models/BookingFacilities.php

- Fixed query to include FacilityRate for invoice calculations

docs/Database-Schema.md

- Added Reference column to Payments table schema

scripts/migrations/add_reference_to_payments.php

- New database migration script for adding Reference column

composer.json

- Added dompdf library dependency for PDF generation

## [1.40.8] - 2025-10-08

### Added

- **Customer Information Display:** Enhanced booking confirmation page with comprehensive customer details for improved user experience and booking verification.
  - **Customer Information Section:** Added a dedicated "Customer Information" section on the booking confirmation page displaying full name and contact number at the top of the booking details.
  - **Restructured Layout:** Reorganized the left column of the confirmation card into separate "Customer Information" section (at top) and "Booking Details" section (below), separated by a visual divider for better information hierarchy.
  - **Email Enhancement:** Updated booking confirmation email to include customer name and contact number information, ensuring consistency between web display and email notifications.
  - **Controller Integration:** Modified `BookingController::showPaymentForm()` to retrieve and pass customer data ($customer) to the confirmation view.

### Enhanced

- **Booking Confirmation Experience:** Restructured the confirmation page layout to match the email structure with customer information prominently displayed at the top, improving user verification and reducing support queries about booking ownership.

### Files Updated

app/Views/booking/confirmation.php

- Added customer information section with name and contact number
- Restructured layout with visual separation between customer info and booking details

app/Controllers/BookingController.php

- Modified showPaymentForm() method to include customer data retrieval

app/Helpers/Notification.php

- Updated sendBookingConfirmation() method to include customer information in email

## [1.40.7] - 2025-10-08

### Added

- **Incomplete Resort Pricing Validation:** Implemented comprehensive validation for resorts with incomplete pricing setup on the New Bookings page.
  - **Pricing Completeness Check:** Added `hasCompletePricing($resortId)` method to `ResortTimeframePricing` model that validates all three timeframe types ('12_hours', '24_hours', 'overnight') have BasePrice > 0.
  - **User Warning System:** When customers select a resort without complete pricing, they see a prominent warning notice with admin contact information (phone and email).
  - **Resort Dimming & Deactivation:** Incomplete resorts are automatically dimmed (opacity-50) and no longer selectable, preventing further booking progression.
  - **Form Field Protection:** All booking form fields are disabled when an incomplete resort is selected, forcing customers to contact resort administrators for arrangements.
  - **Notice Dismissal:** Warning notice includes a close button for dismissal, but the underlying resort restrictions remain enforced.
  - **Controller Integration:** Enhanced `BookingController::showBookingForm()` to pass pricing completeness data and admin contact information to the view.
  - **JavaScript State Management:** Added comprehensive client-side logic for resort validation, notice display, form field management, and automatic deselection/dimming of incomplete resorts.

### Changed

- **New Bookings User Experience:** Modified the resort selection flow to validate pricing completeness before allowing booking progression, improving system reliability and user guidance.

## [1.40.6] - 2025-10-07

### Enhanced

- **Advanced Blocking System UI Improvements:** Significantly enhanced the user experience for the Advanced Blocking interface with polished holiday selection and responsive date display.
  - **Fixed Date Layout Issue:** Corrected a layout problem where date input fields would remain stacked vertically when switching from "Philippine Holidays" to "Weekends Only" or "Full Block" presets, ensuring proper horizontal layout for date range inputs.
  - **Prominent Holiday Checkboxes:** Redesigned holiday selection checkboxes with enhanced visual prominence including light gray backgrounds, blue borders, larger touch targets, bold text labels, and professional styling to improve selection clarity.
  - **Compact Design:** Reduced padding and margins on holiday checkbox containers, along with smaller checkbox scaling (from 1.3x to 1.1x) to create a more compact, efficient layout that shows more options without scrolling.
  - **Clickable Container Areas:** Implemented JavaScript functionality allowing users to click anywhere within the entire holiday checkbox container to toggle selection, significantly improving usability and touch-friendliness for both desktop and mobile users.
  - **Applied Improvements:** Enhanced styling and functionality applied consistently to both Resort Blocking and Facility Blocking sections for uniform user experience.

### Files Updated

app/Views/admin/advanced_blocking.php

- Enhanced holiday checkbox styling and JavaScript functionality
- Fixed date layout display logic for preset selection
- Improved spacing and visual hierarchy of blocking options

## [1.40.5] - 2025-10-07

### Enhanced

- **Customer My Bookings Experience:** Comprehensive improvements to the customer booking history interface for better visual clarity and information transparency.
  - **Color Differentiation for Status Indicators:** Implemented distinct badge colors for different booking states to reduce visual confusion and improve user experience.
    - "Feedback Submitted" badges now use blue (`bg-primary`) background to distinguish from other statuses
    - "Booking confirmed" badges use cyan (`bg-info`) background for clear status differentiation
  - **Complete Booking History Transparency:** Modified booking display logic to show all booking statuses including cancelled bookings, providing customers full visibility into their booking activity history.
  - **Creation Date Visibility:** Added dedicated "Creation Date" column to show when each booking was originally created, including both date and time formatting for complete booking timeline context.
  - **Controller Logic Update:** Updated `BookingController::showMyBookings()` to remove booking status filtering, enabling complete booking history display for transparency.

### Changed

- **Badge Semantic Coloring:** Changed from uniform secondary background to semantically meaningful colors (blue for feedback, cyan for confirmation) matching Bootstrap conventions.
- **Booking Display Logic:** Removed exclusion filtering for cancelled bookings to provide complete booking history transparency.
- **Table Column Structure:** Added creation timestamp column to provide booking chronology and timeline context.

### Files Updated

app/Controllers/BookingController.php

- Modified showMyBookings() to include all booking statuses

app/Views/booking/my_bookings.php

- Added Creation Date column with formatted timestamps
- Updated badge color classes for improved visual distinction

## [1.40.4] - 2025-10-06

### Added

- **Comprehensive Admin Count Display System:** Implemented a complete count visualization system across admin dashboards, individual pages, and navigation menus.
  - **Dashboard Quick Actions Count Badges:** Added resort-filtered count badges to Payment Verification button (red, showing pending payments) and Unified Booking & Payment button (blue, showing active bookings not completed).
  - **Individual Page Header Counts:** Implemented count displays in page headers for Payment Verification ("Pending Payments (X)") and Unified Booking Management ("Unified Booking & Payment Management (X)"), reflecting current resort filter selections.
  - **Navigation Menu Count Badges:** Added navigation dropdown badges showing total counts across all resorts for Unified Management (blue, active bookings) and Payment Verification (red, pending payments).
  - **Enhanced Count Logic:** Added `getActiveBookingsCountForAdmin()` method to Booking model for admin-specific count queries excluding completed bookings.
  - **UI Optimizations:** Made count badges compact (font-size: 12px, reduced padding) to accommodate large numbers without crowding navigation elements.
  - **Backend Integration:** Enhanced AdminController to pass resort-filtered counts to dashboard view and individual page controllers.

### Enhanced

- **Admin Dashboard Context-Awareness:** Quick action buttons now dynamically display counts based on selected resort filter, eliminating confusion between resort-specific vs. all-resort contexts.
- **Navigation Efficiency:** Navigation dropdowns provide immediate visibility of system-wide pending tasks without needing to open individual pages.
- **User Experience Consistency:** All count displays use consistent styling (badge appearance, color coding) matching dashboard design patterns.

### Technical

- **Count Accuracy:** Unified Management counts exclude completed bookings, Payment Verification shows all pending payments, ensuring meaningful actionable numbers.
- **Performance Optimization:** Compact badge styling prevents UI overflow while maintaining readability for large numbers (3+ digits).
- **Code Architecture:** Maintained separation of concerns with resort-filtered logic on dashboard vs. all-resort logic in navigation.

### Changed

- **Payment Model Enhancement:** No changes made to Payment model methods - existing `getPendingPaymentCount()` method already supported optional resort filtering.
- **Unified Booking Management:** Updated page header to display count beside title when resort filter is active.

### Files Updated

app/Models/Booking.php

- Added getActiveBookingsCountForAdmin() method

app/Controllers/AdminController.php

- Enhanced dashboard() and unifiedBookingManagement() with count passing

app/Views/admin/dashboard.php

- Added count badges to Quick Management Actions

app/Views/admin/payments/pending.php

- Added count in header title

app/Views/admin/unified_booking_management.php

- Added count in header title

app/Views/partials/header.php

- Added navigation dropdown badges
- Included Payment model require
- Optimized badge CSS for compact display

Payment.php model methods leveraged (no changes needed)

## [1.40.3] - 2025-10-06

### Enhanced

- **Admin Feedback Management System:** Completely revamped the admin feedback interface (`admin/feedback`) to provide comprehensive feedback oversight for both resort experiences and facility-specific reviews.
  - **Unified Feedback Display:** Redesigned the interface to show both resort-level feedback and facility-specific feedback in separate, clearly labeled sections for better administrator insight.
  - **Resort-Based Filtering:** Added a resort filter dropdown that allows admins to view feedback for all resorts or filter by specific resorts, matching the functionality available in the Admin Dashboard and other admin pages.
  - **Enhanced User Experience:** Improved the layout by placing the page title at the top, followed by the resort filter, with better vertical spacing. Replaced numeric ratings with star representations for more intuitive visual feedback.
  - **Database Query Optimization:** Updated the `Feedback` model with efficient queries using proper JOINs (`LEFT JOIN Facilities`) to include resort-only bookings that were previously missing from the feedback list.
  - **Separated Feedback Types:** Introduced `findAllFacilityFeedbacks()` method to handle facility-specific feedbacks independently from resort feedbacks, supporting the new two-section display approach.

### Changed

- **Feedback System Architecture:** Refactored the feedback retrieval logic to accommodate the dual nature of feedback (resort + facilities) with independent querying and resort-aware filtering throughout the model layer.

### Fixed

- **Missing Resort-Only Feedbacks:** Resolved the core issue where feedback for bookings without specific facilities was not displayed due to inappropriate INNER JOIN usage. The system now correctly shows all feedback types.

### Technical

- **Model Enhancements:** Updated `Feedback::findAll()` and added `Feedback::findAllFacilityFeedbacks()` to support resort filtering and proper handling of facility relationships.
- **Controller Integration:** Modified `FeedbackController::listAllFeedback()` to process both feedback types and handle resort parameter validation.
- **View Redesign:** Completely overhauled `app/Views/admin/feedback/index.php` with responsive layout, filtering controls, and star-rating displays for better user experience.

## [1.40.2] - 2025-10-06

### Added

- **Resort-Based Filtering for Pending Payments:** Implemented resort-specific filtering on the Pending Payments admin page, similar to the existing functionality in the Admin Dashboard.
  - **Controller Modifications:** Updated `PaymentController::showPendingPayments()` to accept `resort_id` GET parameter, fetch all resorts, and pass resort filter to `Payment::getPendingPayments($resortId)`.
  - **Session-Based Filter Persistence:** Added session storage of resort filter (`$_SESSION['pending_resort_filter']`) to maintain selection after verify/reject payment actions.
  - **Smart Redirects:** Modified `verifyPayment()` and `rejectPayment()` methods to preserve resort filter in redirect URLs after processing payments.
  - **UI Enhancement:** Added resort filter dropdown to `pending.php` view with "All Resorts" default option, matching Admin Dashboard styling and behavior.
  - **Improved User Workflow:** Admins can now focus on pending payments for specific resorts while maintaining filter state throughout verification workflow.

### Changed

- **Payment Controller Architecture:** Enhanced `PaymentController` with resort filter session management and intelligent redirect preservation.

## [1.40.1] - 2025-10-06

### Fixed

- **Payment Audit Trail Consolidation:** Resolved fragmented audit trail entries for payment-related activities that were creating confusing, partial-information records in the Unified Booking & Payment Management page.
  - **Payment Submission Consolidation:** Fixed issue where customer payment submissions created two separate audit entries instead of one comprehensive record. Unified the separate `PaymentSubmitted` and `PaymentSubmission` entries into a single, meaningful "Customer submitted payment" entry with complete details (amount, method, reference).
  - **Payment Verification Consolidation:** Fixed admin payment verification creating multiple fragmented entries. Combined separate `PaymentVerified`, balance updates, and status changes into one consolidated entry showing verification details, status changes, and balance adjustments in a single audit record.
  - **Code Changes:**
    - Enhanced `app/Models/Payment.php` `verifyPayment()` method to create consolidated audit entries with all verification effects (payment status, booking status, balance changes).
    - Modified `app/Controllers/BookingController.php` to remove redundant `logPaymentUpdate()` calls for payment submissions, letting Payment model handle comprehensive logging.
  - **Audit Trail Improvements:** Payment-related audit entries now use consistent formatting with the rest of the system, eliminating confusing partial entries and providing complete, readable records of payment activities.
  - **Testing:** Created comprehensive test scripts to validate consolidation behavior and prevent regressions.

### Changed

- **Audit Trail Formatting:** Standardized payment audit trail entries to use consistent field names and comprehensive descriptions, aligning with the initial booking creation pattern that consolidates multiple details into single meaningful entries.

## [1.40.0] - 2025-10-06

### Added

- **Active Booking Count Badge in Customer Navigation:** Implemented real-time display of active bookings count in the customer navigation header.
  - **Backend Implementation:** Added `getActiveBookingsCount($customerId)` method to `Booking` model that excludes 'Completed' and 'Cancelled' bookings from count.
  - **Controller Integration:** Modified `BookingController::showMyBookings()` to filter out completed/cancelled bookings from the active bookings display.
  - **Frontend Navigation:** Updated `app/Views/partials/header.php` for customers to display active booking count as a styled badge next to "My Bookings" link.
  - **UI Enhancement:** Added custom CSS styling for `.booking-count-badge` class with larger font size (16px) while maintaining compact badge dimensions and professional appearance.
  - **Role-Based Display:** Count badge appears only for customers with active bookings, showing formatted count (e.g., "3") with light background matching navigation theme.

### Changed

## [1.39.9] - 2025-10-05

### Changed

- **Payment Method System Consistency Refactor:** Transformed the payment method system to ensure complete consistency across all admin interfaces. All dropdowns and selection inputs now show resort-specific payment methods (e.g., "GCash - 0912..."), replacing free-text fields and fixing previously empty dropdowns.
  - **Database Schema Update:** Migrated the `MethodType` column in the `ResortPaymentMethods` table from a restrictive `ENUM` to a flexible `VARCHAR(100)`, allowing administrators to define custom payment method names.
  - **Backend Model Enhancement:** Updated `ResortPaymentMethods.php` to support the new flexible `MethodType` and added `getFormattedPaymentMethods()` to supply data in the "Name - Details" format.
  - **Admin UI Overhaul:**
    - **Pricing Management (`pricing_management.php`):** Correctly displays the list of configured, resort-specific payment methods.
    - **Payment Management (`payments/manage.php`):** Fixed the previously empty dropdown to now load and display the correct, resort-specific payment options.
    - **Unified Booking Modal (`unified_booking_management.php`):** Implemented a dynamic, AJAX-powered dropdown that loads resort-specific payment methods when an admin manages a booking. Includes a "Cash" fallback if no online methods are configured.
  - **API Endpoint:** Enhanced `BookingController::getPaymentMethods()` to return formatted JSON for dynamic frontend dropdowns.
  - **Affected files:**
    - [`scripts/migrations/change_method_type_to_varchar.php`](scripts/migrations/change_method_type_to_varchar.php)
    - [`app/Models/ResortPaymentMethods.php`](app/Models/ResortPaymentMethods.php)
    - [`app/Views/admin/pricing_management.php`](app/Views/admin/pricing_management.php)
    - [`app/Views/admin/payments/manage.php`](app/Views/admin/payments/manage.php)
    - [`app/Views/admin/unified_booking_management.php`](app/Views/admin/unified_booking_management.php)
    - [`app/Controllers/BookingController.php`](app/Controllers/BookingController.php)

## [1.39.8] - 2025-10-05

### Changed

- **Comprehensive Facility Scheduling Refactoring:** Consolidated and enhanced the facility blocking system, achieving full feature parity with resort-level blocking within a unified "Advanced Blocking System" interface. This refactoring deprecated redundant scheduling UIs and introduced advanced blocking capabilities for individual facilities.
  - **Deprecation of Old Scheduling UI:**
    - Removed "Manage Schedule" buttons (resort and facility level) from `app/Views/admin/management/index.php`.
    - Eliminated associated modals (`#scheduleResortModal`, `#scheduleFacilityModal`) and their JavaScript functions.
  - **Enhanced Advanced Blocking System (`app/Views/admin/advanced_blocking.php`):**
    - Implemented a tabbed interface for "Resort Blocking" and "Facility Blocking."
    - Added "Manual Facility Date Blocking" section.
    - Introduced "Preset Facility Blocking" with options for Weekends, Philippine Holidays, and Full Block.
    - Updated facility selection for presets to use checkboxes for multi-select.
    - Added "Facility Deblocking" options for date range removal and "Deblock All" for a selected facility.
    - Included "Preset Information" and "Important Notes" side panels for facility blocking, mirroring the resort blocking tab.
    - Corrected JavaScript placement and logic for the new UI elements, including dynamic display of holiday checkboxes and proper form handling.
  - **Updated Controller Logic (`app/Controllers/AdminController.php`):**
    - Enhanced `blockFacilityAvailability()` to handle submissions from the advanced blocking page.
    - Implemented new methods: `applyFacilityPresetBlocking()`, `deblockFacilityAll()`, and `deblockFacilityByDateRange()` to manage facility presets and bulk deblocking.
  - **Extended Model (`app/Models/BlockedFacilityAvailability.php`):**
    - Added `deleteAllForFacility($facilityId)` and `deleteByDateRangeAndFacility($facilityId, $startDate, $endDate)` methods to support bulk operations.
  - **Affected files:**
    - [`app/Views/admin/management/index.php`](app/Views/admin/management/index.php)
    - [`app/Views/admin/advanced_blocking.php`](app/Views/admin/advanced_blocking.php)
    - [`app/Controllers/AdminController.php`](app/Controllers/AdminController.php)
    - [`app/Models/BlockedFacilityAvailability.php`](app/Models/BlockedFacilityAvailability.php)

## [1.39.7] - 2025-10-04

### Added

- **Holiday Rate Implementation & Admin Integration:** Implemented a comprehensive "Holiday Rate" feature across the booking system, including backend logic, API updates, frontend UI, and admin panel synchronization.
  - **Centralized Holiday Logic:**
    - Created a new helper class, [`app/Helpers/HolidayHelper.php`](app/Helpers/HolidayHelper.php), to centralize the list of Philippine holidays and provide a static method (`isHoliday()`) for date checking.
    - Added a `getHolidays()` method to `HolidayHelper` to retrieve the full list of holidays with their names for dynamic UI generation.
  - **Backend Pricing Logic:**
    - Modified `ResortTimeframePricing::calculatePrice()` in [`app/Models/ResortTimeframePricing.php`](app/Models/ResortTimeframePricing.php:114) to use `HolidayHelper::isHoliday()` and apply the `holidaySurcharge` when applicable.
  - **API Endpoints Updates:**
    - Updated `BookingController::getResortPricing()` in [`app/Controllers/BookingController.php`](app/Controllers/BookingController.php:202) to include an `isHoliday` flag in its JSON response.
    - Enhanced `BookingController::getCalendarAvailability()` in [`app/Controllers/BookingController.php`](app/Controllers/BookingController.php:380) to identify and mark holidays with a `'holiday'` status for the calendar modal.
  - **Customer-Facing UI Enhancements:**
    - Modified [`app/Views/booking/create.php`](app/Views/booking/create.php) to display a new **"Holiday Rate" badge** next to the base price when a holiday is selected.
    - Updated the calendar modal in [`app/Views/booking/create.php`](app/Views/booking/create.php) to visually highlight holidays with a distinct light blue background and "Holiday" status text.
    - Adjusted the selected date label in [`app/Views/booking/create.php`](app/Views/booking/create.php) to append **"(Holiday rates may apply)"** for selected holiday dates.
  - **Admin Panel Synchronization:**
    - Refactored `AdminController::advancedBlocking()` in [`app/Controllers/AdminController.php`](app/Controllers/AdminController.php:1205) to fetch the holiday list dynamically from `HolidayHelper::getHolidays()` and pass it to the view.
    - Updated the preset blocking logic in `AdminController::applyPresetBlocking()` in [`app/Controllers/AdminController.php`](app/Controllers/AdminController.php:1231) to validate selected holidays against the `HolidayHelper`'s list.
    - Modified the `app/Views/admin/advanced_blocking.php` view to dynamically generate holiday checkboxes for preset blocking, replacing the previous hardcoded list, and updated the holiday information text.
  - **Affected files:**
    - [`app/Helpers/HolidayHelper.php`](app/Helpers/HolidayHelper.php) (New file & modified)
    - [`app/Models/ResortTimeframePricing.php`](app/Models/ResortTimeframePricing.php)
    - [`app/Controllers/BookingController.php`](app/Controllers/BookingController.php)
    - [`app/Views/booking/create.php`](app/Views/booking/create.php)
    - [`app/Controllers/AdminController.php`](app/Controllers/AdminController.php)
    - [`app/Views/admin/advanced_blocking.php`](app/Views/admin/advanced_blocking.php)

## [1.39.6] - 2025-10-04

### Added

- **Progressive Disclosure for Payment Modal:** Implemented a progressive disclosure pattern for the payment modal on the "My Bookings" page, improving user experience by revealing payment form fields only after a method is selected.
  - **Frontend:**
    - Modified [`app/Views/booking/my_bookings.php`](app/Views/booking/my_bookings.php) to initially hide payment form fields (`#paymentFormFields`).
    - Updated JavaScript to render payment methods as selectable radio buttons and show `#paymentFormFields` when a selection is made.
    - Added a hidden input field (`#selectedPaymentMethod`) within the form to correctly capture the chosen payment method's value.
  - **Backend:**
    - Modified [`app/Models/Payment.php`](app/Models/Payment.php) `createFromBookingPayment()` to accept a `$paymentMethod` parameter.
    - Updated [`app/Controllers/BookingController.php`](app/Controllers/BookingController.php) `submitPayment()` to retrieve and pass the selected `payment_method` to the model.
  - **Payment Method Display on Success Page:** The selected payment method is now displayed on the payment success page for confirmation.
    - Updated [`app/Controllers/BookingController.php`](app/Controllers/BookingController.php) `paymentSuccess()` to fetch the latest payment record.
    - Modified [`app/Views/booking/payment_success.php`](app/Views/booking/payment_success.php) to display the `PaymentMethod` from the latest payment.
  - **Affected files:**
    - [`app/Views/booking/my_bookings.php`](app/Views/booking/my_bookings.php)
    - [`app/Models/Payment.php`](app/Models/Payment.php)
    - [`app/Controllers/BookingController.php`](app/Controllers/BookingController.php)
    - [`app/Views/booking/payment_success.php`](app/Views/booking/payment_success.php)

### Fixed

- **Payment Method Submission Error:** Resolved an issue where the payment method was not being submitted with the form, leading to a "All fields are required, including payment method" error.
  - **Root Cause:** The payment method radio buttons were technically outside the `<form>` element, preventing their values from being submitted.
  - **Solution:** Implemented a hidden input field (`<input type="hidden" name="payment_method" id="selectedPaymentMethod">`) within the form in [`app/Views/booking/my_bookings.php`](app/Views/booking/my_bookings.php). JavaScript now updates this hidden field's value whenever a payment method radio button is selected, ensuring the data is correctly posted.
  - **Affected files:**
    - [`app/Views/booking/my_bookings.php`](app/Views/booking/my_bookings.php)

## [1.39.5] - 2025-10-04

### Fixed

- **Feedback Modal Display Issues:** Resolved comprehensive bugs in the resort and facility detail modals where feedback was not properly reflected and displayed.
  - **Facility Feedback Data Source Error:** Fixed critical issue where facility feedback modals were incorrectly displaying main resort feedback instead of facility-specific feedback.
    - **Root Cause:** `findByFacilityId()` method queried `Feedback` table (resort feedback) instead of `FacilityFeedback` table.
    - **Solution:** Updated method to query `FacilityFeedback` table with proper joins to display actual facility-specific ratings and comments.
  - **Resort Feedback Query Logic Error:** Corrected incomplete data retrieval for resort feedback display in modal tabs.
    - **Root Cause:** Query used `b.FacilityID` join instead of `b.ResortID` filtering, plus NULL FacilityID handling for bookings.
    - **Solution:** Changed to `LEFT JOIN Facilities` with proper `WHERE b.ResortID = :resortId` and `COALESCE` handling for NULL facility references.
  - **Modal Feedback Text Cleanup:** Removed redundant "reviewing General Resort Experience" text from resort feedback cards for cleaner UI presentation.
    - **implementation:** Simplified card display to show only customer name, rating stars, comment, and date.
  - **Affected files:**
    - [`app/Models/Feedback.php`](app/Models/Feedback.php) - Fixed `findByFacilityId()` and `findByResortId()` query logic.

### Enhanced

- **Modal Tab Feedback Counts:** Added real-time feedback count indicators to resort and facility modal tab titles for improved user insights.
  - **Implementation:** JavaScript dynamically updates tab text (e.g., "Feedback (5)") after AJAX data fetch, with fallback to "Feedback" on errors.
  - **Features:** Shows "(0)" for no feedback, handles loading and error states gracefully.
  - **Affected files:**
    - [`app/Views/partials/footer.php`](app/Views/partials/footer.php) - Enhanced modal JavaScript with count display logic.

## [1.39.4] - 2025-10-04

### Fixed

- **Customer Feedback System Bug Fixes:** Resolved critical issues with the newly implemented facility feedback functionality that prevented proper loading and submission.
  - **Facility Feedback Loading Failure:** Fixed JavaScript error "Could not load facilities for feedback" in the feedback modal.
    - **Root Cause:** `getFacilitiesForBooking` API endpoint was not included in the booking controller allowed actions, causing routing denial.
    - **Solution:** Added `'getFacilitiesForBooking'` to the `$allowedActions` array in `public/index.php`.
  - **Feedback Submission Database Lock Timeout:** Resolved critical performance issue where feedback submissions hung for 2 minutes and failed with "Failed to submit feedback. Please try again."
    - **Root Cause:** Single large transaction spanning `Feedback` and `FacilityFeedback` tables caused MySQL `innodb_lock_wait_timeout` (50 seconds) due to heavy lock contention.
    - **Solution:** Refactored `Feedback::createWithFacilities()` to commit main feedback first, then create facility feedbacks individually without wrapping transaction to prevent lock timeouts.
    - **Added Comprehensive Error Logging:** Implemented detailed logging in `FeedbackController`, `Feedback` model, and `FacilityFeedback` model using `\ErrorHandler::log()` to capture submission events and debug future issues in `logs/application.log`.
  - **Affected files:**
    - [`public/index.php`](public/index.php) - Added API endpoint to routing whitelist.
    - [`app/Controllers/FeedbackController.php`](app/Controllers/FeedbackController.php) - Enhanced input validation and error logging.
    - [`app/Models/Feedback.php`](app/Models/Feedback.php) - Fixed transaction logic and added logging.
    - [`app/Models/FacilityFeedback.php`](app/Models/FacilityFeedback.php) - Added validation and logging.

## [1.39.3] - 2025-10-04

### Fixed

- **Payment Proof File Upload Reliability:** Completely refactored the client-side payment proof upload validation to be more robust and eliminate issues where files were not accepted on the first attempt.
  - **Removed Unreliable Retry Logic:** The previous implementation that attempted to retry reading file metadata has been replaced.
  - **Definitive Image Validation:** The new `handleModalFileSelection()` function now uses a combination of `FileReader` to read the file and the `Image` object to programmatically load it. This guarantees that the file is a valid and non-corrupted image before the preview is shown and the form is enabled.
  - **Improved User Feedback:** Added clearer, more immediate feedback to the user during the validation process, including "Validating image..." and "Image accepted" messages.
  - **Foolproof Validation:** This new method is not dependent on browser timing for metadata and provides a definitive check, making the upload process foolproof.
  - **Affected files:**
    - [`app/Views/booking/my_bookings.php`](app/Views/booking/my_bookings.php) - Overhauled the `handleModalFileSelection` JavaScript function.

## [1.39.2] - 2025-10-04

### Fixed

- **Payment Submission Performance:** Resolved a critical 2-minute delay during payment submission by optimizing the booking lifecycle management.
  - **Root Cause:** The `BookingLifecycleManager::processAllBookings()` method, a system-wide maintenance task, was incorrectly triggered by individual customer payment submissions, causing significant synchronous processing overhead.
  - **Solution:** Replaced the inefficient `processAllBookings()` call with a new, targeted method `BookingLifecycleManager::processBookingAfterPayment($bookingId)`. This ensures that only the relevant booking is processed immediately after payment, drastically reducing the response time to milliseconds.
  - **Remaining Logic:** The `processAllBookings()` method remains available for scheduled background tasks (cron jobs) to handle system-wide booking status updates without impacting real-time user interactions.
  - **Affected files:**
    - [`app/Models/BookingLifecycleManager.php`](app/Models/BookingLifecycleManager.php) - Added `processBookingAfterPayment()` and `getSingleBookingForProcessing()` methods.
    - [`app/Controllers/BookingController.php`](app/Controllers/BookingController.php) - Replaced `processAllBookings()` with `processBookingAfterPayment($bookingId)`.

## [1.39.1] - 2025-10-04

### Changed

- **View File Name Revision:** Renamed `app/Views/booking/payment.php` to `confirmation.php` to better reflect its purpose as a booking confirmation page displaying booking summary and payment status information rather than a payment form.
  - Updated all references in controllers, test scripts, and documentation links.
  - Changed file perspective from payment-centric to confirmation-centric, improving code clarity and semantic accuracy.
  - **Affected files:**
    - `app/Views/booking/confirmation.php` (renamed from `payment.php`)
    - `app/Controllers/BookingController.php` - Updated include path
    - `scripts/dev/test_phase4_enhanced_ui.php` - Updated test file references and descriptions

## [1.39.0] - 2025-10-04

### Changed

- **Submission Payment Page Simplification:** Streamlined the Submit Payment page to only display Booking Summary information, removing all payment method displays, payment scheduling sections, and payment submission forms. The page now serves as a confirmation screen after booking creation, guiding customers to use the modal payment system from the My Bookings page instead of direct form submission.
  - **Removed Sections:** Eliminated Available Payment Methods card, Phase 6 Payment Schedule section, and Payment Submission Form
  - **Updated UI:** Changed page title to "Booking Confirmation" and page description for better clarity
  - **Updated Visuals:** Changed header icons to check-circle to reflect confirmation rather than payment processing
  - **Navigation:** Replaced complex payment processing with simple "Back to My Bookings" navigation
  - **Affected files:** [`app/Views/booking/payment.php`](app/Views/booking/payment.php) - Complete UI and logic overhaul for simplified booking confirmation

## [1.38.0] - 2025-10-04

### Added

- **Enhanced Customer Feedback System:** Implemented comprehensive feedback functionality allowing customers to submit reviews for both the booked resort and its optional facilities through a single, dynamic modal.
  - **Solution:**
    - Created a new database migration (`scripts/migrations/create_facility_feedback_table.php`) to add a `FacilityFeedback` table, linking facility-specific feedback to the main booking feedback.
    - Developed a new `app/Models/FacilityFeedback.php` model to manage interactions with the new table.
    - Extended `app/Controllers/BookingController.php` with a `getFacilitiesForBooking()` API endpoint to dynamically fetch facilities for a given booking.
    - Modified `app/Views/booking/my_bookings.php` to include a dynamic modal that displays separate feedback forms for the resort and each associated facility.
    - Updated the `app/Models/Feedback.php` model with a `createWithFacilities()` method to handle the transactional saving of both resort and facility feedback.
    - Refactored `app/Controllers/FeedbackController.php` to process the combined feedback data and utilize the new transactional save method.
  - **Affected files:**
    - [`scripts/migrations/create_facility_feedback_table.php`](scripts/migrations/create_facility_feedback_table.php) - New database migration.
    - [`app/Models/FacilityFeedback.php`](app/Models/FacilityFeedback.php) - New model for facility feedback.
    - [`app/Controllers/BookingController.php`](app/Controllers/BookingController.php) - Added API endpoint.
    - [`app/Models/BookingFacilities.php`](app/Models/BookingFacilities.php) - Added `getFacilitiesForBooking` method.
    - [`app/Views/booking/my_bookings.php`](app/Views/booking/my_bookings.php) - Updated UI for dynamic feedback modal.
    - [`app/Models/Feedback.php`](app/Models/Feedback.php) - Added transactional save method.
    - [`app/Controllers/FeedbackController.php`](app/Controllers/FeedbackController.php) - Updated to handle new feedback structure.

## [1.37.0] - 2025-10-03

### Added

- **Advanced Deblocking System:** Implemented a comprehensive deblocking feature set in the "Advanced Blocking" system, allowing administrators to efficiently remove existing date blocks.
  - **Solution:**
    - Added a new "Deblocking Options" card to the UI in [`app/Views/admin/advanced_blocking.php`](app/Views/admin/advanced_blocking.php), featuring a form for deblocking by date range and a "Deblock All" button.
    - Implemented two new controller methods, `deblockAll()` and `deblockByDateRange()`, in [`app/Controllers/AdminController.php`](app/Controllers/AdminController.php) to handle the backend logic for these actions.
    - Added two corresponding data-layer methods, `deleteAllForResort()` and `deleteByDateRange()`, to the [`app/Models/BlockedResortAvailability.php`](app/Models/BlockedResortAvailability.php) model to execute the database deletions.
    - Included an `onsubmit` JavaScript confirmation dialog for the "Deblock All" feature to prevent accidental data loss.
  - **Affected files:**
    - [`app/Views/admin/advanced_blocking.php`](app/Views/admin/advanced_blocking.php) - UI enhancements.
    - [`app/Controllers/AdminController.php`](app/Controllers/AdminController.php) - Backend logic implementation.
    - [`app/Models/BlockedResortAvailability.php`](app/Models/BlockedResortAvailability.php) - Database method implementation.

## [1.36.9] - 2025-10-03

### Changed

- **Holiday Blocking UI/UX:** Refactored the "Philippine Holidays" preset in the "Advanced Blocking" system to improve usability and address logical flaws.
  - **Solution:**
    - Replaced the `Start Date` and `End Date` inputs with a series of checkboxes for specific holidays when the "Philippine Holidays" preset is selected in [`app/Views/admin/advanced_blocking.php`](app/Views/admin/advanced_blocking.php).
    - Implemented JavaScript to dynamically toggle the visibility of the date-range picker and the new holiday checkbox container based on the selected preset.
    - Updated the `applyPresetBlocking()` method in [`app/Controllers/AdminController.php`](app/Controllers/AdminController.php) to process the array of selected holidays, blocking them for the current year.
    - Removed the redundant and logically flawed `isPhilippineHoliday()` and `getPhilippineHolidays()` methods.
  - **Affected files:**
    - [`app/Views/admin/advanced_blocking.php`](app/Views/admin/advanced_blocking.php) - UI and JavaScript changes.
    - [`app/Controllers/AdminController.php`](app/Controllers/AdminController.php) - Backend logic update and code cleanup.

## [1.36.8] - 2025-10-03

### Changed

- **Mandatory Blocking Reason:** The 'Reason' field for all date-blocking functionalities has been made mandatory, removing its optional status to ensure better administrative accountability.
  - **Solution:**
    - Modified [`app/Views/admin/advanced_blocking.php`](app/Views/admin/advanced_blocking.php) to remove the "(Optional)" label and add the `required` HTML attribute to the reason input fields for both "Preset Blocking" and "Manual Date Blocking". The JavaScript display logic was also updated to no longer show "No reason specified".
    - Updated [`app/Views/admin/management/index.php`](app/Views/admin/management/index.php) to similarly remove the "(Optional)" label and add the `required` HTML attribute to the reason fields within the resort and facility blocking modals. The JavaScript display logic was adjusted to directly show the reason.
    - Enhanced the `AdminController.php` by adding server-side validation to the `applyPresetBlocking()`, `blockResortAvailability()`, and `blockFacilityAvailability()` methods. These methods now verify that a non-empty `reason` is provided before processing any blocking request, redirecting with an error message if the validation fails.
  - **Affected files:**
    - [`app/Views/admin/advanced_blocking.php`](app/Views/admin/advanced_blocking.php) - UI updates for reason field.
    - [`app/Views/admin/management/index.php`](app/Views/admin/management/index.php) - UI updates for reason fields in modals.
    - [`app/Controllers/AdminController.php`](app/Controllers/AdminController.php) - Server-side validation for blocking actions.

## [1.36.7] - 2025-10-03

### Enhanced

- **Resort Selection Visuals on New Booking Page:** Transformed the resort selection process from a dropdown menu to a visually engaging, card-based interface on the new booking page, similar to the facility selection.
  - **Solution:**
    - Modified [`app/Controllers/BookingController.php`](app/Controllers/BookingController.php) to dynamically assign relevant Font Awesome icons to resorts based on their names and prepend the `BASE_URL` to resort image URLs for consistent access. A new helper method `getIconForResort` was added.
    - Updated [`app/Views/booking/create.php`](app/Views/booking/create.php) to display resort cards with their main photo, a descriptive icon, and a short description, replacing the previous dropdown menu. New CSS styling was added for visual presentation, including hover effects and a `selected` state for chosen cards.
    - Adjusted the JavaScript in [`app/Views/booking/create.php`](app/Views/booking/create.php) to handle the `change` event for the new resort radio buttons, update the UI with the selected resort, and ensure that all subsequent form logic (loading facilities, pricing, summary, and validation) correctly uses the chosen resort.
  - **Affected files:**
    - [`app/Controllers/BookingController.php`](app/Controllers/BookingController.php) - Added icon assignment and base URL prepending for resort images.
    - [`app/Views/booking/create.php`](app/Views/booking/create.php) - Implemented UI changes for resort cards and new CSS rules, and updated JavaScript for resort selection handling.

### Fixed

- **Booking Summary & Calendar Modal Issues after Resort UI Update:** Resolved issues where the booking summary did not update immediately upon resort selection, and the "Browse Available Dates" button was non-functional after the resort selection UI was changed.
  - **Root Cause:** The JavaScript code was still referencing the old `<select>` element for resort selection, leading to incorrect values for the `resortId` and preventing dependent functions from triggering.
  - **Solution:** Modified the JavaScript in [`app/Views/booking/create.php`](app/Views/booking/create.php) to correctly retrieve the `resortId` from the new radio button inputs, ensuring the `handleResortChange`, `openCalendarModal`, and `handleDateOrTimeframeChange` functions use the correct selected resort ID.
  - **Affected files:**
    - [`app/Views/booking/create.php`](app/Views/booking/create.php) - Updated JavaScript to correctly handle resort selection from radio buttons for summary updates and calendar functionality.

## [1.36.6] - 2025-10-03

### Enhanced

- **Number of Guests Input Field UI/UX:** Improved the usability and feedback of the "Number of Guests" input field on the New Booking page.
  - **Always Visible Arrows:** Overrode default Bootstrap styling to ensure the up and down arrows (spinners) on the number input field are always visible, enhancing discoverability and ease of use.
  - **Capacity Exceeded Feedback:** Implemented a subtle "shake" animation on the input field and its warning message when a user attempts to increase the number of guests beyond the resort's maximum capacity. This provides immediate visual feedback that the limit has been reached.
  - **Affected files:**
    - [`app/Views/booking/create.php`](app/Views/booking/create.php) - Added custom CSS for spinner visibility and JavaScript for shake animation and capacity handling.

## [1.36.5] - 2025-10-03

### Fixed

- **One Booking Per Day Rule:** Resolved an issue where customers could book multiple timeframes for the same resort on the same day. The system now enforces a "one booking per resort per day" rule, regardless of the chosen timeframe.
  - **Root Cause:** The availability checks (`isResortTimeframeAvailable` and `isTimeSlotAvailable` in `Booking.php`, and `analyzeTimeframeConflicts` in `AdvancedAvailabilityChecker.php`) were designed to check for conflicts only within specific timeframes, allowing multiple bookings on the same day if timeframes didn't overlap. The calendar display in `BookingController.php` also reflected this incorrect logic.
  - **Solution:**
    - Modified [`app/Models/AdvancedAvailabilityChecker.php`](app/Models/AdvancedAvailabilityChecker.php) to update the `analyzeTimeframeConflicts` method. It now checks for _any_ existing booking on the given date for a resort, instead of only conflicting timeframes, ensuring the frontend accurately reflects true daily availability.
    - Updated [`app/Models/Booking.php`](app/Models/Booking.php) by modifying the `isResortTimeframeAvailable` and `isTimeSlotAvailable` methods. These methods now ignore the `timeSlotType` parameter for the primary availability check and instead query for _any_ existing booking for the specified resort on the given `bookingDate`, enforcing the one-booking-per-day rule at the model level.
    - Adjusted [`app/Controllers/BookingController.php`](app/Controllers/BookingController.php) to update the `getBookingCountForDate` method. This method now counts all bookings for a given date for a resort, regardless of the timeframe, ensuring the calendar display correctly marks a day as "Booked" if any booking exists.
    - Enhanced [`app/Views/booking/create.php`](app/Views/booking/create.php) by adding a new JavaScript `checkAvailability()` function. This function performs a real-time AJAX call to the backend's `checkAvailability` endpoint, and if the date is unavailable (due to an existing booking), it disables the "Complete Booking" button and displays an appropriate error message, improving user experience.
  - **Affected files:**
    - [`app/Models/AdvancedAvailabilityChecker.php`](app/Models/AdvancedAvailabilityChecker.php) - Updated availability logic.
    - [`app/Models/Booking.php`](app/Models/Booking.php) - Modified core booking availability checks.
    - [`app/Controllers/BookingController.php`](app/Controllers/BookingController.php) - Adjusted calendar date status logic.
    - [`app/Views/booking/create.php`](app/Views/booking/create.php) - Added client-side availability check and button disabling.

## [1.36.4] - 2025-10-03

### Enhanced

- **Expanded Booking Summary on New Booking Page:** Enhanced the booking summary section on the customer-facing "New Booking" page to include more comprehensive details for better user clarity.
  - **Solution:**
    - Modified [`app/Views/booking/create.php`](app/Views/booking/create.php) to display the selected Resort, Timeframe, Date, and Number of Guests in a dedicated "Booking Details" section.
    - Updated the JavaScript in [`app/Views/booking/create.php`](app/Views/booking/create.php) to dynamically populate these new summary fields as the user makes their selections.
    - Added a Font Awesome `fa-building` icon next to each selected facility in the pricing breakdown for improved visual presentation.
  - **Affected files:**
    - [`app/Views/booking/create.php`](app/Views/booking/create.php) - Updated HTML structure and JavaScript logic for the expanded booking summary.

## [1.36.3] - 2025-10-03

### Enhanced

- **Facility Selection Visuals on New Booking Page:** Greatly improved the visual appeal and user experience of the facility selection section on the new booking page by adding icons, images, and descriptions.
  - **Solution:**
    - Modified [`app/Controllers/BookingController.php`](app/Controllers/BookingController.php) to dynamically assign relevant Font Awesome icons to facilities based on their names and prepend the `BASE_URL` to facility image URLs for consistent access.
    - Updated [`app/Views/booking/create.php`](app/Views/booking/create.php) to display facility cards with their main photo, a descriptive icon, short description, and price.
    - Added new CSS styling within `app/Views/booking/create.php` to enhance the visual presentation of facility cards, including hover effects and consistent image display.
  - **Affected files:**
    - [`app/Controllers/BookingController.php`](app/Controllers/BookingController.php) - Added icon assignment and base URL prepending for facility images.
    - [`app/Views/booking/create.php`](app/Views/booking/create.php) - Implemented UI changes for facility cards and new CSS rules.

## [1.36.2] - 2025-10-03

### Fixed

- **Booking Cancellation Logic:** Resolved an issue where customers could cancel bookings after submitting payment or for completed bookings.
  - **Root Cause:** The UI incorrectly displayed the cancel button, and the backend lacked validation for booking status and payment status during cancellation.
  - **Solution:**
    - Modified `app/Views/booking/my_bookings.php` to hide the "Cancel" button for bookings with `Status` other than 'Pending' or when `RemainingBalance` is less than `TotalAmount`.
    - Implemented server-side validation in `app/Controllers/BookingController.php` to prevent cancellation if the booking `Status` is not 'Pending' or if a partial payment has been made (`remainingBalance < totalAmount`).
  - **Affected files:**
    - [`app/Views/booking/my_bookings.php`](app/Views/booking/my_bookings.php) - UI logic for displaying the cancel button.
    - [`app/Controllers/BookingController.php`](app/Controllers/BookingController.php) - Server-side validation for cancellation.

## [1.36.1] - 2025-10-02

### Fixed

- **Admin "Edit Resort" Modal Capacity Display:** Resolved an issue where the "Capacity" field in the "Edit Resort" modal displayed as blank instead of showing the existing resort capacity. The JavaScript logic was updated to correctly fetch and populate this field.
  - **Root Cause:** Incomplete JavaScript in `app/Views/admin/resorts/index.php` that did not populate the `capacity` field.
  - **Solution:**
    - Moved and enhanced JavaScript logic to `app/Views/admin/resorts/resort_modals.php` to dynamically fetch and populate all resort details, including `capacity`.
    - Removed redundant JavaScript from `app/Views/admin/resorts/index.php`.
    - Whitelisted `getResortJson` action in `app/Controllers/ResortController.php` to ensure proper API access.
  - **Affected files:**
    - [`app/Views/admin/resorts/resort_modals.php`](app/Views/admin/resorts/resort_modals.php) - Added comprehensive JavaScript for modal population.
    - [`app/Views/admin/resorts/index.php`](app/Views/admin/resorts/index.php) - Removed deprecated JavaScript.
    - [`app/Controllers/ResortController.php`](app/Controllers/ResortController.php) - Updated access control for `getResortJson`.

## [1.36.0] - 2025-10-02

### Fixed

- **New Bookings Page Resort Capacity Display Issue:** Resolved a critical bug where the resort capacity always displayed as "0" instead of the actual capacity value, causing guest validation errors. The API endpoint `getResortDetails` was not routable due to missing whitelisted action in the routing system. Added comprehensive debugging and fallback capacity handling.
  - **Root Cause:** `getResortDetails` method excluded from allowed actions in `public/index.php` booking controller whitelist, preventing AJAX API calls
  - **Solution:**
    - Added `'getResortDetails'` to booking controller allowed actions in routing whitelist
    - Enhanced `loadResortDetails()` JavaScript function with console debugging and fallback capacity display
    - Added dynamic capacity help text that updates with actual resort capacity limit after API call
    - Affected files:
      - [`public/index.php`](public/index.php) - Added API endpoint to routing whitelist
      - [`app/Views/booking/create.php`](app/Views/booking/create.php) - Enhanced AJAX calls and added dynamic capacity notes

### Changed

- **Guest Input Field Default Value:** Modified the initial number of guests field value from "1" to empty string to require manual user input and prevent premature form completion. Updated step validation logic to correctly handle empty guest input, ensuring logical booking progression.
  - **Impact:** Users must now explicitly enter guest count instead of defaulting to 1, improving intentional booking creation
  - **Validation:** Step 4 (Guests) only marks complete when field contains valid number > 0, aligning with form progression logic
  - **Affected files:**
    - [`app/Views/booking/create.php`](app/Views/booking/create.php) - Changed default value and enhanced validation

### Enhanced

- **Booking Form User Experience:** Added real-time resort capacity feedback and improved guest validation warnings. Help text now dynamically displays specific capacity limits (e.g., "Ensure the number doesn't exceed resort capacity - 25 maximum.") when resort is selected, providing clearer guidance to users.
  - **JavaScript Enhancements:** Added capacity note updates in loadResortDetails function with console logging for debugging
  - **Error Resilience:** Implemented fallback capacity handling to prevent complete booking form breakdown if API calls fail

## [1.35.1] - 2025-10-02

### Fixed

- **Facility Management Form Validation:** Resolved the "Resort selection is required" error when adding new facilities and similar validation issues when editing facilities. The form field `name` attributes in the facility management modals were updated to consistently use `snake_case` (`resort_id`, `short_description`, `description`) to match backend validation expectations.
  - Affected files:
    - [`app/Views/admin/management/facility_modals.php`](app/Views/admin/management/facility_modals.php)
    - [`app/Views/admin/management/edit_facility_modal.php`](app/Views/admin/management/edit_facility_modal.php)

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
