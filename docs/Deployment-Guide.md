# Deployment Guide

This guide provides instructions for deploying the Integrated Digital Management System on a web server.

## Prerequisites for Local Development

Before you begin, you will need a local environment that can run PHP and MySQL. We recommend installing a pre-packaged software stack, as it's the simplest way to get started.

- **Local Server Stack:** Install **XAMPP** ([Download here](https://www.apachefriends.org/index.html)). It includes:
  - **Apache:** The web server.
  - **MySQL:** The database.
  - **PHP:** The programming language.
  - **phpMyAdmin:** A tool to manage the database.
- **Code Editor:** **Visual Studio Code** ([Download here](https://code.visualstudio.com/)) is recommended.
- **Version Control:** **Git** ([Download here](https://git-scm.com/downloads)) is required to clone the repository and manage code versions.

---

## 1. Server Requirements

- **Web Server:** Apache 2.4+ or Nginx 1.18+
- **PHP:** Version 8.0 or higher
  - Required extensions: `pdo_mysql`
- **Database:** MySQL 8.0+ or MariaDB 10.4+

### Dependencies

- **Composer:** This project uses Composer to manage PHP dependencies.
- **PHPMailer:** The email notification system relies on PHPMailer.

## 2. Web Server Configuration (Security Best Practice)

**This is the most important step for securing your application.**

To prevent direct URL access to sensitive files (like database models and controllers), you **must** configure your web server's "Document Root" to point to the `/public` directory within your project folder. This ensures that only files inside `/public` (like `index.php` and your assets) are web-accessible.

#### For Apache:

1.  Find your Apache configuration file for virtual hosts (e.g., `httpd-vhosts.conf`).
2.  Create or modify the virtual host entry for your site to set the `DocumentRoot` correctly:

    ```apache
    <VirtualHost *:80>
        ServerName your-resort-system.local
        DocumentRoot "C:/xampp/htdocs/ResortsSystem/public"
        <Directory "C:/xampp/htdocs/ResortsSystem/public">
            Options Indexes FollowSymLinks
            AllowOverride All
            Require all granted
        </Directory>
    </VirtualHost>
    ```

3.  Restart Apache for the changes to take effect.

#### For Nginx:

1.  Find your Nginx server block configuration file.
2.  Set the `root` directive to point to the `public` directory:

    ```nginx
    server {
        listen 80;
        server_name your-resort-system.local;
        root /var/www/html/ResortsSystem/public;

        index index.php;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        }
    }
    ```

3.  Reload Nginx for the changes to take effect.

## 3. Installation Steps

1.  **Clone the Repository:**
    Clone the project from the source repository into your web server's root directory (e.g., `/var/www/html`).

    ```bash
    git clone <repository-url> .
    ```

2.  **Install Dependencies:**
    Use Composer to install the required libraries (like PHPMailer).

    ```bash
    composer install
    ```

3.  **Set File Permissions:**
    Ensure the web server has the necessary permissions to write to the upload directories.
    ```bash
    # Grant write permissions to the general uploads folder
    chmod -R 775 /path/to/your/project/public/uploads
    # Set ownership to the web server user (e.g., www-data for Apache on Linux)
    chown -R www-data:www-data /path/to/your/project/public/uploads
    ```
    _Note: The specific user may vary depending on your server setup (`apache`, `nginx`, etc.)._

## 4. Database Setup

1.  **Create a Database User (If Needed):**
    Before running the script, ensure you have a MySQL user that matches the credentials in `config/database.php`. If you haven't created one, you can do so via phpMyAdmin or the command line:

    ```sql
    CREATE USER 'resorts_user'@'localhost' IDENTIFIED BY 'your-strong-password';
    GRANT ALL PRIVILEGES ON resorts_system.* TO 'resorts_user'@'localhost';
    FLUSH PRIVILEGES;
    ```

    _Note: The `resorts_system._` privileges will apply correctly after the database is created in the next step.\*

2.  **Run the Initialization Script:**
    Navigate to the project's root directory in your terminal and run the following command. This will create the database structure.

```bash
php scripts/init_db.php
```

3.  **Truncate Database Tables (Optional):**
    To clear all data from the database tables (e.g., for a factory reset during development or testing), run the truncation script:

```bash
php scripts/dev/truncate_db_tables.php
```

4.  **Seed the Database (Optional but Recommended):**
    To populate the database with sample data for testing, run the seeding script:

```bash
php scripts/seed_db.php
```

5.  **Run Database Migrations:**
    If there are any pending database migrations (e.g., for schema updates), run them from the `scripts/migrations` directory.

```bash
php scripts/migrations/add_notes_to_users.php
php scripts/migrations/create_payments_table.php
php scripts/migrations/create_blocked_availabilities_table.php
php scripts/migrations/add_photos_to_facilities.php
php scripts/migrations/create_feedback_table.php
php scripts/migrations/update_bookings_table_for_timeslots.php
php scripts/migrations/add_rich_data_to_resorts.php
php scripts/migrations/create_resort_photos_table.php
php scripts/migrations/create_blocked_resort_availability_table.php
```

### Application Structure

- **`app/Views/partials/`**: This directory contains reusable view components, such as the site-wide header and footer. This allows for a consistent layout across different pages.

### Development Scripts

The `scripts/dev/` directory contains temporary scripts used for testing and diagnostics during the development process. These are not required for the production application to run but can be useful for debugging.

You can run them from the command line (e.g., `php scripts/dev/test_booking_model.php`) or, in some cases, directly in your browser.

- **Financial Report Test:** To verify that the financial reporting features are calculating correctly, navigate to the following URL in your browser:
  - `http://localhost/ResortsSystem/scripts/dev/test_financial_reports.php`

## 5. Application Configuration

1.  **Create Configuration File:**
    Create a `config.php` file in the project's root directory. This file should not be committed to version control.

2.  **Add Database Credentials:**
    Add the following content to `config.php`, replacing the placeholder values with your database credentials.

    ```php
    <?php
    define('DB_HOST', 'localhost');
    define('DB_USER', 'resorts_user');
    define('DB_PASS', 'your-strong-password');
    define('DB_NAME', 'resorts_system');
    ?>
    ```

3.  **Configure Email Settings:**
    Copy the `config/mail.sample.php` file to a new file named `config/mail.php`. Then, open the new `config/mail.php` file and replace the placeholder values with your actual SMTP credentials. This is **required** for sending email notifications (e.g., booking confirmations, welcome emails).

    ```php
    <?php

    // Example for using a Gmail account
    define('MAIL_HOST', 'smtp.gmail.com');
    define('MAIL_PORT', 587);
    define('MAIL_SMTPAUTH', true);
    define('MAIL_SMTPSECURE', 'tls');

    // IMPORTANT: Replace with your credentials
    define('MAIL_USERNAME', 'your.resort.admin@gmail.com');
    define('MAIL_PASSWORD', 'your-google-app-password'); // Use a Google App Password
    define('MAIL_FROM', 'your.resort.admin@gmail.com');
    define('MAIL_FROM_NAME', 'Resorts System Admin');
    ```

    > **Security Note:** For Gmail, it is strongly recommended to use a **Google App Password** instead of your regular account password. [Learn how to create one here](https://support.google.com/accounts/answer/185833).

4.  **Configure Server for Mail (XAMPP Example):**
    For the email system to function in a local XAMPP environment, you must configure PHP to use an external SMTP server.

    a. **Edit `php.ini`:**

    - Open your `php.ini` file (via XAMPP Control Panel > Apache > Config).
    - Find the `[mail function]` section and configure it as follows:
      ```ini
      SMTP = smtp.gmail.com
      smtp_port = 587
      sendmail_from = your.resort.admin@gmail.com
      sendmail_path = "\"C:\xampp\sendmail\sendmail.exe\" -t"
      ```

    b. **Edit `sendmail.ini`:**

    - Open `C:\xampp\sendmail\sendmail.ini`.
    - Configure it with your credentials:
      ```ini
      [sendmail]
      smtp_server=smtp.gmail.com
      smtp_port=587
      auth_username=your.resort.admin@gmail.com
      auth_password=your-google-app-password
      force_sender=your.resort.admin@gmail.com
      ```

## 6. Final Steps

- **Access the Application:**

  - Ensure your project folder (`ResortsSystem`) is located inside your XAMPP document root (e.g., `C:/xampp/htdocs`).
  - Open your web browser and navigate to the special admin registration page to create the first **Admin** account:
  - **URL:** `http://localhost/ResortsSystem/public/?action=showAdminRegisterForm` (or `http://localhost:8080/...` if you use a custom port).
  - After creating the Admin, you can log in to the main dashboard URL: `http://localhost/ResortsSystem/public/`. The system will automatically route you to the appropriate dashboard based on your role (Admin or Staff).
  - You can create Customer accounts by navigating to `http://localhost/ResortsSystem/public/?action=showRegisterForm`.

- **Testing:** After registering the Admin, log in and verify that the Admin Dashboard appears. Use it to create other users and test their roles.
