# Deployment Guide

This guide provides instructions for deploying the Integrated Digital Management System on a web server.

## 1. Server Requirements

- **Web Server:** Apache 2.4+ or Nginx 1.18+
- **PHP:** Version 8.0 or higher
  - Required extensions: `pdo_mysql`
- **Database:** MySQL 8.0+ or MariaDB 10.4+

## 2. Installation Steps

1.  **Clone the Repository:**
    Clone the project from the source repository into your web server's root directory (e.g., `/var/www/html`).

    ```bash
    git clone <repository-url> .
    ```

2.  **Set File Permissions:**
    Ensure the web server has the necessary permissions to write to specific directories (e.g., for uploads).
    ```bash
    chown -R www-data:www-data /path/to/your/project
    chmod -R 755 /path/to/your/project
    ```

## 3. Database Setup

1.  **Create a Database:**
    Log in to your MySQL server and create a new database.

    ```sql
    CREATE DATABASE resorts_system;
    ```

2.  **Create a Database User:**
    Create a user and grant it privileges to the new database.

    ```sql
    CREATE USER 'resorts_user'@'localhost' IDENTIFIED BY 'your-strong-password';
    GRANT ALL PRIVILEGES ON resorts_system.* TO 'resorts_user'@'localhost';
    FLUSH PRIVILEGES;
    ```

3.  **Import the Schema:**
    Import the table structures from the `Database-Schema.md` file or a dedicated `.sql` dump file if available.

## 4. Application Configuration

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

## 5. Final Steps

- **Access the Application:** Open your web browser and navigate to your server's domain or IP address.
- **Testing:** Perform end-to-end testing to ensure all features, including registration, booking, and payments, are working correctly.
