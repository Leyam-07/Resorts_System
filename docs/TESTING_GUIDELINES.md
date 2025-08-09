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

## 🎯 Future Suggestions & Refinements Checklist

This section contains suggestions for more in-depth testing to improve robustness and user experience.

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

### 2. Deeper Authorization Tests

- [ ] **Staff &#34;View-Only&#34; Enforcement:**
  - [ ] Log in as Staff and confirm that UI elements for editing/deleting data are not visible (e.g., "Save" or "Delete" buttons on a user profile).
  - [ ] Attempt to bypass the UI by sending a direct `POST` request to an endpoint that modifies data. The server should return a `403 Forbidden` error.
- [ ] **Admin User Creation:**
  - [ ] Verify an Admin can create a new user of any role (Customer, Staff, Admin) via the admin panel.

### 3. Account Deletion Workflow

- [ ] **Deletion Pending State:**
  - [ ] After a Customer requests deletion, log in as an Admin to check for a pending approval queue or notification.
  - [ ] Determine the account status of a user who has requested deletion but has not been approved yet. Can they still log in?

### 4. Session Management Edge Cases

- [ ] **Session Timeout:**
  - [ ] Verify that a user's session automatically expires and requires re-login after a defined period of inactivity.
- [ ] **Concurrent Logins:**
  - [ ] Test the system's behavior when a single user account logs in from multiple devices/browsers simultaneously. (Define expected behavior: allow, or invalidate older session).

---
