# Manual Testing Checklist & Future Refinements

This document provides a comprehensive checklist for manually testing system features and serves as a repository for future testing suggestions.

---

## Phase 1: User Management (Version 1.1.0)

This section covers testing for the core user management functionalities: login, registration, role-based access, and profile management.

### ✅ Core Test Plan (Completed)

- [x] **Registration:**
  - [x] Register a new Customer account.
  - [x] Register a new Admin account.
  - [x] Register a new Staff account.
- [x] **Login:**
  - [x] Log in with a Customer account.
  - [x] Log in with an Admin account.
  - [x] Log in with a Staff account.
- [x] **Profile Management:**
  - [x] Verify Customer can update their own profile information.
  - [x] Verify Admin can update any user's profile information.
  - [x] Verify users can change their own passwords.
- [x] **Account Deletion:**
  - [x] Verify Customer can initiate account deletion.
  - [x] Verify Admin can delete any user's account.

### 🧪 Negative Testing (Completed)

- [x] **Registration Failures:**
  - [x] Attempt to register with an existing email (should fail with "User already exists").
  - [x] Attempt to register with empty/invalid fields (should show validation errors).
- [x] **Login Failures:**
  - [x] Attempt login with a valid username and incorrect password.
  - [x] Attempt login with a non-existent username.
  - (Result for both should be a generic "Invalid credentials" error).

### 🔒 Authorization & Access Control (Completed)

- [x] **Cross-Role Access Denial:**
  - [x] Logged-in Customer cannot access Admin URLs (e.g., `/admin/users`).
  - [x] Logged-in Staff cannot access Admin URLs.
- [x] **Unauthenticated Access:**
  - [x] Logged-out user attempting to access a protected page is redirected to login.
- [x] **Session Security:**
  - [x] After logout, browser 'back' button does not reveal authenticated content.

---

## Phase 1: Booking Engine (Version 1.3.0)

This section covers testing for the booking engine and related features.

### ✅ Core Test Plan (Completed)

- [x] **Booking Creation (Happy Path):**
  - [x] Logged-in Customer can access the booking form.
  - [x] Form successfully loads available facilities.
  - [x] Submitting valid data creates a booking record in the database.
  - [x] User is redirected to a styled success page.
- [x] **Validation & Error Handling:**
  - [x] Submitting an empty form displays validation errors.
  - [x] Form fields are repopulated with previous input on validation failure.
  - [x] Attempting to book a past date is rejected.
  - [x] Attempting to book an already reserved slot is rejected with an error message.
- [x] **Security & Access Control:**
  - [x] Logged-out users are redirected to login when trying to access the booking page.
  - [x] Staff users are redirected and cannot access the booking creation page.
  - [x] A user can only cancel their own bookings.
- [x] **Booking Cancellation:**
  - [x] A "My Bookings" page exists for customers to see their reservations.
  - [x] A "Cancel" button is present for upcoming bookings.
  - [x] Clicking "Cancel" successfully deletes the booking and updates the view.

---

## Phase 1: Customer Information Management (Version 1.4.1)

This section covers testing for features related to viewing customer history and managing administrative notes.

### ✅ Core Test Plan (Completed)

- [x] **Customer Booking History:**
  - [x] Customer can view their own booking history from their profile.
  - [x] A message is shown if the customer has no bookings.
- [x] **Admin Booking History View:**
  - [x] Admin can view the booking history for any specific user.
  - [x] Page correctly displays which user's history is being viewed.
  - [x] A message is shown if the selected user has no bookings.
- [x] **Admin Notes:**
  - [x] Admin can add notes when creating a new user.
  - [x] Admin can add, update, and remove notes from an existing user.
  - [x] Notes correctly handle and display special characters.
- [x] **Security & Access Control:**
  - [x] Only Admins can view other users' booking history.
  - [x] Only Admins can view or edit notes.
  - [x] Customers/Staff cannot access admin-specific user pages.
  - [x] Admin cannot delete their own account.
  - [x] Direct file access to sensitive views is blocked.

### 🎯 Future Suggestions & Refinements Checklist

#### 1. Booking History Enhancements

- [ ] **Pagination:**
  - [ ] Implement pagination on the "My Bookings" page for customers to handle long booking histories.
  - [ ] Implement pagination on the admin's "View User Bookings" page.
- [ ] **Filtering and Sorting:**
  - [ ] Add controls for both customers and admins to filter booking history by a date range.
  - [ ] Add controls to sort the history (e.g., newest first, oldest first).
- [ ] **Data Export:**
  - [ ] Add a feature for customers to export their own booking history (e.g., to CSV or PDF).
  - [ ] Add a feature for admins to export a specific user's booking history.

#### 2. Admin Notes Improvements

- [ ] **Notes Audit Trail:**
  - [ ] Create a `NoteHistory` table to log changes to the `Notes` field.
  - [ ] Record which admin made the change and when it occurred.
  - [ ] Provide a way for admins to view the history of changes to a user's notes.
- [ ] **Search and Filtering:**
  - [ ] On the admin's user list, add a search filter to find users based on keywords within their notes.

#### 3. Deeper Authorization Tests

- [ ] **Staff "View-Only" Enforcement:**
  - [ ] Log in as Staff and confirm that UI elements for viewing user bookings are not visible.
  - [ ] Attempt to bypass the UI by sending a direct `GET` request to an endpoint that views user bookings (e.g., `view_user_bookings.php?user_id=X`). The server should return a `403 Forbidden` error.
- [ ] **Admin Self-Management Safeguards:**
  - [ ] Verify an Admin cannot change their own role when editing their own profile.

---

## Phase 1: Admin Dashboard (Version 1.5.1)

This section covers testing for the admin dashboard.

### ✅ Core Test Plan (Completed)

- [x] **Access Control:** Verified that only authorized users (Admins) can access the dashboard.
- [x] **Data Verification:** Ensured the dashboard displays the correct data for the current day.
- [x] **UI & Navigation:** Confirmed the user interface is correct and navigation links work as expected.
- [x] **Security Patch:** Blocked direct file access to the dashboard view for non-admin and unauthenticated users.

### 🎯 Future Suggestions & Refinements Checklist

#### 1. Architectural Security Hardening

- [ ] **Implement a Front Controller:**
  - [ ] Configure the web server (e.g., via `.htaccess`) to route all requests through a single entry point (`public/index.php`).
  - [ ] Move all authentication and authorization checks from individual View files into the `AdminController`. This centralizes security logic and prevents direct URL access to sensitive views, adhering more strictly to the MVC pattern.

---

## Phase 2: Financial and Facility Management (Version 1.6.1)

This section covers testing and future refinements for payment tracking, status updates, and financial oversight.

### ✅ Core Test Plan (Completed)

- [x] **Database Integrity:** Verified the `Payments` table structure and relationships.
- [x] **Backend Logic:** Tested the `Payment` model and `PaymentController` for creating, reading, and updating payment records.
- [x] **Frontend UI:** Verified the admin payment management view, including the display of existing payments and the functionality of the "Add New Payment" form.
- [x] **End-to-End Workflow:** Successfully simulated a full payment cycle from booking creation to payment reconciliation.
- [x] **Feature Enhancements:**
  - [x] Automated booking status updates to "Confirmed" when a payment is marked "Paid".
  - [x] Added a color-coded "Payment Status" to the admin dashboard for clarity.
  - [x] Implemented a manual override for admins to change booking status directly from the payments page.
  - [x] Localized currency symbol from `$` to `₱`.

### ✅ Core Test Plan (Completed)

- [x] **Facility CRUD:** Verified creation, viewing, editing, and deletion of facilities.
- [x] **Time Slot Blocking:** Confirmed ability to block single time slots and date ranges for maintenance.
- [x] **Blocked Slot Enforcement:** Ensured blocked slots are unavailable for customer bookings.
- [x] **Capacity Enforcement:** Validated that booking requests exceeding facility capacity are rejected.
- [x] **Security Hardening:** Implemented access control to prevent non-admins from accessing facility management pages.
- [x] **UI/UX Consistency:** Ensured consistent navigation buttons and styling across facility management views.
- [x] **Double-Encoding Fix:** Confirmed resolution of special character double-encoding issues in facility notes/descriptions.

### 🎯 Future Suggestions & Refinements Checklist

#### 1. Facility Scheduling & Availability

- [ ] **Visual Scheduling Calendar:**
  - [ ] Implement a full-page, interactive calendar for Admins to manage facility schedules.
  - [ ] Allow Admins to easily view availability, existing bookings, and blocked times at a glance.
  - [ ] Enable direct interaction with the calendar to block time slots.
- [ ] **Recurring Blocked Schedules:**
  - [ ] Add functionality for Admins to set up recurring blocked times (e.g., weekly maintenance, daily closures).
  - [ ] Provide options for daily, weekly, or custom recurrence patterns.
- [ ] **Public-Facing Availability View:**
  - [ ] Create a read-only, user-friendly calendar view for Customers to check a facility's general availability before initiating the booking process.
  - [ ] This view should clearly indicate available and unavailable (booked/blocked) slots without revealing sensitive details.
- [ ] **Display Block Reason:**
  - [ ] On the Admin's schedule view, display the specific reason provided when a time slot was blocked (e.g., "Maintenance," "Private Event").

#### 2. Payment Calculation & Validation

- [ ] **Auto-Calculate Balance:**
  - [ ] On the payment management page, automatically calculate and display the booking's total cost and the remaining balance based on payments already made.
- [ ] **Overpayment Warnings:**
  - [ ] Add validation to warn the admin if the sum of all payments for a booking exceeds the total booking cost.

#### 2. Refund Tracking

- [ ] **Add Refund Status:**
  - [ ] Add a `Refunded` status to the `Payments` table's `Status` ENUM.
  - [ ] Implement a UI for admins to log refunds against a specific payment, especially for canceled bookings.

#### 4. Enhanced Invoice Generation

- [ ] **Printable Invoices:**
  - [ ] Implement a feature to generate a detailed, printable HTML invoice for any booking, including a breakdown of payments.
- [ ] **Invoice History:**
  - [ ] Allow admins to view all past invoices or receipts generated for a specific booking.

---

## Phase 2: Reporting (Version 1.8.1)

This section covers testing for the financial reporting features on the Admin Dashboard.

### ✅ Core Test Plan (Completed)

- [x] **Unit Testing:** Verified that the `Booking` model methods (`getMonthlyIncome`, `getBookingHistory`) return accurate data.
- [x] **Integration Testing:** Confirmed that the `AdminController` correctly fetches data from the model and passes it to the dashboard view.
- [x] **Functional Testing:** Logged in as an Admin and visually confirmed that the "Monthly Income" and "Booking History" sections display correct data.
- [x] **UI/UX Testing:** Checked the dashboard's responsiveness and visual consistency for the new reporting components on multiple screen sizes.

### 🎯 Future Suggestions & Refinements Checklist

#### 1. Financial Reporting Enhancements

- [ ] **Date Range Filtering:**
  - [ ] Enhance the "Monthly Income Summary" to allow admins to generate reports for custom date ranges.
- [ ] **Export Reports:**
  - [ ] Add a feature to export financial summaries to a CSV or PDF file.

---

## 🎯 General Future Suggestions & Refinements Checklist

This section contains suggestions for more in-depth testing to improve robustness and user experience across the application.

### 1. Password Management Lifecycle

- [ ] **Forgot Password Flow:**
  - [ ] Verify a "Forgot Password" link exists on the login page.
  - [ ] Verify a user can request a password reset.
  - [ ] Verify the system sends a password reset email to the registered address.
  - [ ] Verify the password reset link works correctly.
  - [ ] Verify the reset link can only be used once and/or expires.
  - [ ] Verify the user can successfully set a new password.
- [ ] **Password Change Security:**
  - [ ] Verify the "Change Password" form in the user profile requires the user to enter their _current_ password.

### 2. Account Deletion Workflow

- [ ] **Deletion Pending State:**
  - [ ] After a Customer requests deletion, log in as an Admin to check for a pending approval queue or notification.
  - [ ] Determine the account status of a user who has requested deletion but has not been approved yet. Can they still log in?

### 3. Session Management Edge Cases

- [ ] **Session Timeout:**
  - [ ] Verify that a user's session automatically expires and requires re-login after a defined period of inactivity.
- [ ] **Concurrent Logins:**
  - [ ] Test the system's behavior when a single user account logs in from multiple devices/browsers simultaneously. (Define expected behavior: allow, or invalidate older session).

### 4. General Admin & Facility Management

- [ ] **Admin Booking Dashboard:**
  - [ ] Create a centralized view for Admins to see all bookings across all users.
  - [ ] Admins should be able to filter bookings by date, user, or facility.
  - [ ] Admins should have the ability to **create, edit, or cancel** any booking on behalf of a customer.
- [ ] **Blackout Dates:**
  - [ ] Implement a feature for Admins to block specific dates or date ranges for maintenance, holidays, or private events.
  - [ ] The booking form should not allow users to select blackout dates.

### 5. UX & Financial Enhancements

- [ ] **Display Pricing on Booking Form:**
  - [ ] When a user selects a facility and date, dynamically display the price for that booking.
  - [ ] Clearly indicate the required downpayment amount, if applicable.
- [ ] **Improved Error Handling:**
  - [ ] Provide more specific error messages for booking conflicts (e.g., "Sorry, that slot was just taken. Please select another.").

### 6. Notification System (Phase 3 Alignment)

- [ ] **Event-Driven Notifications:**
  - [ ] Create a `notifications` table in the database to log notification events.
  - [ ] The `BookingController` should create a new record in `notifications` when a booking is successfully **created**.
  - [ ] The `BookingController` should create a new record in `notifications` when a booking is successfully **canceled**.
- [ ] **Automated Communication:**
  - [ ] Implement a mechanism (e.g., a cron job or an email service integration) to process the `notifications` table and send emails/SMS to users.
  - [ ] Develop email templates for booking confirmation, cancellation, and reminders.
  - [ ] Add logic to send automated reminders (e.g., 24 hours before the booking).
