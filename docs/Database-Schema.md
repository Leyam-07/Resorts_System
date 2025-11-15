# Database Schema

This document provides the detailed `MySQL` database schema for the Integrated Digital Management System.

---

### Table: `Users`

Stores user accounts for Admins, Staff, and Customers.

```sql
CREATE TABLE IF NOT EXISTS `Users` (
  `UserID` INT PRIMARY KEY AUTO_INCREMENT,
  `Username` VARCHAR(255) NOT NULL UNIQUE,
  `Password` VARCHAR(255) NOT NULL,
  `Role` ENUM('Admin', 'Staff', 'Customer') NOT NULL,
  `AdminType` ENUM('Admin', 'BookingAdmin', 'OperationsAdmin', 'ReportsAdmin') NULL DEFAULT NULL,
  `FirstName` VARCHAR(255),
  `LastName` VARCHAR(255),
  `Email` VARCHAR(255) NOT NULL UNIQUE,
  `PhoneNumber` VARCHAR(20),
  `ProfileImageURL` VARCHAR(255) NULL,
  `Notes` TEXT,
  `Socials` TEXT,
  `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

### Table: `Resorts`

Stores information about the resort properties.

```sql
CREATE TABLE IF NOT EXISTS `Resorts` (
  `ResortID` INT PRIMARY KEY AUTO_INCREMENT,
  `Name` VARCHAR(255) NOT NULL,
  `Address` TEXT,
  `ContactPerson` VARCHAR(255),
  `ShortDescription` TEXT,
  `FullDescription` TEXT,
  `MainPhotoURL` VARCHAR(255)
);
```

---

### Table: `ResortPhotos`

Stores multiple photos for each resort.

```sql
CREATE TABLE IF NOT EXISTS `ResortPhotos` (
  `PhotoID` INT PRIMARY KEY AUTO_INCREMENT,
  `ResortID` INT,
  `PhotoURL` VARCHAR(255) NOT NULL,
  `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`ResortID`) REFERENCES `Resorts`(`ResortID`) ON DELETE CASCADE
);
```

---

### Table: `Facilities`

Stores details about the individual facilities within a resort (e.g., pools, cottages).

```sql
CREATE TABLE IF NOT EXISTS `Facilities` (
  `FacilityID` INT PRIMARY KEY AUTO_INCREMENT,
  `ResortID` INT,
  `Name` VARCHAR(255) NOT NULL,
  `Rate` DECIMAL(10, 2),
  `ShortDescription` TEXT,
  `FullDescription` TEXT,
  `MainPhotoURL` VARCHAR(255),
  FOREIGN KEY (`ResortID`) REFERENCES `Resorts`(`ResortID`)
);
```

---

### Table: `Bookings`

The central table for managing all reservations. Updated to support resort-centric booking with integrated payment tracking.

```sql
CREATE TABLE IF NOT EXISTS `Bookings` (
  `BookingID` INT PRIMARY KEY AUTO_INCREMENT,
  `CustomerID` INT,
  `FacilityID` INT,
  `ResortID` INT,
  `BookingDate` DATE NOT NULL,
  `TimeSlotType` ENUM('12_hours', '24_hours', 'overnight') NOT NULL,
  `TotalAmount` DECIMAL(10, 2),
  `PaymentProofURL` VARCHAR(255),
  `PaymentReference` VARCHAR(100),
  `RemainingBalance` DECIMAL(10, 2) DEFAULT 0.00,
  `Status` ENUM('Pending', 'Confirmed', 'Cancelled', 'Completed') NOT NULL,
  `ExpiresAt` DATETIME NULL DEFAULT NULL,
  `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`CustomerID`) REFERENCES `Users`(`UserID`),
  FOREIGN KEY (`FacilityID`) REFERENCES `Facilities`(`FacilityID`),
  FOREIGN KEY (`ResortID`) REFERENCES `Resorts`(`ResortID`)
);
```

---

### Table: `ResortTimeframePricing`

Stores pricing information for different timeframes at each resort, including weekend and holiday surcharges.

```sql
CREATE TABLE IF NOT EXISTS `ResortTimeframePricing` (
  `PricingID` INT PRIMARY KEY AUTO_INCREMENT,
  `ResortID` INT NOT NULL,
  `TimeframeType` ENUM('12_hours', '24_hours', 'overnight') NOT NULL,
  `BasePrice` DECIMAL(10, 2) NOT NULL,
  `WeekendSurcharge` DECIMAL(10, 2) DEFAULT 0.00,
  `HolidaySurcharge` DECIMAL(10, 2) DEFAULT 0.00,
  `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`ResortID`) REFERENCES `Resorts`(`ResortID`) ON DELETE CASCADE,
  UNIQUE KEY `unique_resort_timeframe` (`ResortID`, `TimeframeType`)
);
```

---

### Table: `BookingFacilities`

Junction table that enables multiple facility selection per booking, supporting the new resort-centric booking model.

```sql
CREATE TABLE IF NOT EXISTS `BookingFacilities` (
  `BookingFacilityID` INT PRIMARY KEY AUTO_INCREMENT,
  `BookingID` INT NOT NULL,
  `FacilityID` INT NOT NULL,
  `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`BookingID`) REFERENCES `Bookings`(`BookingID`) ON DELETE CASCADE,
  FOREIGN KEY (`FacilityID`) REFERENCES `Facilities`(`FacilityID`) ON DELETE CASCADE,
  UNIQUE KEY `unique_booking_facility` (`BookingID`, `FacilityID`)
);
```

---

### Table: `ResortPaymentMethods`

Configuration table for resort-specific payment methods and settings.

```sql
CREATE TABLE IF NOT EXISTS `ResortPaymentMethods` (
  `PaymentMethodID` INT PRIMARY KEY AUTO_INCREMENT,
  `ResortID` INT NOT NULL,
  `MethodType` ENUM('GCash', 'Maya', 'Cash') NOT NULL,
  `AccountName` VARCHAR(255) NULL,
  `AccountNumber` VARCHAR(50) NULL,
  `QrCodeURL` VARCHAR(255) NULL,
  `IsDefault` BOOLEAN DEFAULT FALSE,
  `IsActive` BOOLEAN DEFAULT TRUE,
  `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`ResortID`) REFERENCES `Resorts`(`ResortID`) ON DELETE CASCADE,
  UNIQUE KEY `unique_resort_method` (`ResortID`, `MethodType`)
);
```

---

### Table: `Payments`

Tracks all payment transactions related to bookings.

```sql
CREATE TABLE IF NOT EXISTS `Payments` (
  `PaymentID` INT PRIMARY KEY AUTO_INCREMENT,
  `BookingID` INT,
  `Amount` DECIMAL(10, 2) NOT NULL,
  `PaymentMethod` ENUM('GCash', 'Maya', 'Cash', 'On-Site Payment') NOT NULL,
  `PaymentDate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `Status` ENUM('Paid', 'Unpaid', 'Partial') NOT NULL,
  `ProofOfPaymentURL` VARCHAR(255),
  `Reference` VARCHAR(100),
  FOREIGN KEY (`BookingID`) REFERENCES `Bookings`(`BookingID`)
);
```

---

---

### Table: `BlockedResortAvailability`

Stores records of dates that are manually blocked by an administrator for an entire resort.

```sql
CREATE TABLE IF NOT EXISTS `BlockedResortAvailability` (
  `BlockedAvailabilityID` INT PRIMARY KEY AUTO_INCREMENT,
  `ResortID` INT,
  `BlockDate` DATE NOT NULL,
  `Reason` VARCHAR(255),
  `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`ResortID`) REFERENCES `Resorts`(`ResortID`) ON DELETE CASCADE
);
```

### Table: `BlockedFacilityAvailability`

Stores records of dates that are manually blocked by an administrator for a specific facility.

```sql
CREATE TABLE IF NOT EXISTS `BlockedFacilityAvailability` (
  `BlockedAvailabilityID` INT PRIMARY KEY AUTO_INCREMENT,
  `FacilityID` INT,
  `BlockDate` DATE NOT NULL,
  `Reason` VARCHAR(255),
  `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`FacilityID`) REFERENCES `Facilities`(`FacilityID`) ON DELETE CASCADE
);
```

### Table: `BlockedAvailabilities`

Stores records of time slots that are manually blocked by an administrator and are unavailable for booking.

```sql
CREATE TABLE IF NOT EXISTS `BlockedAvailabilities` (
  `BlockedAvailabilityID` INT PRIMARY KEY AUTO_INCREMENT,
  `FacilityID` INT,
  `BlockDate` DATE NOT NULL,
  `StartTime` TIME NOT NULL,
  `EndTime` TIME NOT NULL,
  `Reason` VARCHAR(255),
  `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`FacilityID`) REFERENCES `Facilities`(`FacilityID`) ON DELETE CASCADE
);
```

---

### Table: `FacilityPhotos`

Stores multiple photos for each facility.

```sql
CREATE TABLE IF NOT EXISTS `FacilityPhotos` (
  `PhotoID` INT PRIMARY KEY AUTO_INCREMENT,
  `FacilityID` INT,
  `PhotoURL` VARCHAR(255) NOT NULL,
  `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`FacilityID`) REFERENCES `Facilities`(`FacilityID`) ON DELETE CASCADE
);
```

---

### Table: `PaymentSchedules`

Supports installment payment tracking and management for bookings with scheduled payments.

```sql
CREATE TABLE IF NOT EXISTS `PaymentSchedules` (
    `ScheduleID` INT PRIMARY KEY AUTO_INCREMENT,
    `BookingID` INT NOT NULL,
    `InstallmentNumber` INT NOT NULL,
    `DueDate` DATE NOT NULL,
    `Amount` DECIMAL(10, 2) NOT NULL,
    `Status` ENUM('Pending', 'Paid', 'Overdue', 'Cancelled') DEFAULT 'Pending',
    `PaymentID` INT NULL,
    `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `UpdatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`BookingID`) REFERENCES `Bookings`(`BookingID`) ON DELETE CASCADE,
    FOREIGN KEY (`PaymentID`) REFERENCES `Payments`(`PaymentID`) ON DELETE SET NULL,
    UNIQUE KEY `unique_booking_installment` (`BookingID`, `InstallmentNumber`),
    INDEX `idx_due_date` (`DueDate`),
    INDEX `idx_status` (`Status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### Table: `BookingAuditTrail`

Comprehensive audit trail system for tracking all booking modifications with full user attribution and change history.

```sql
CREATE TABLE IF NOT EXISTS `BookingAuditTrail` (
    `AuditID` INT PRIMARY KEY AUTO_INCREMENT,
    `BookingID` INT NOT NULL,
    `UserID` INT NOT NULL,
    `Action` ENUM('CREATE', 'UPDATE', 'DELETE', 'STATUS_CHANGE', 'PAYMENT_UPDATE', 'FACILITY_CHANGE') NOT NULL,
    `FieldName` VARCHAR(100) NOT NULL,
    `OldValue` TEXT NULL,
    `NewValue` TEXT NULL,
    `ChangeReason` TEXT NULL,
    `IPAddress` VARCHAR(45) NULL,
    `UserAgent` TEXT NULL,
    `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`BookingID`) REFERENCES `Bookings`(`BookingID`) ON DELETE CASCADE,
    FOREIGN KEY (`UserID`) REFERENCES `Users`(`UserID`) ON DELETE CASCADE,
    INDEX `idx_booking_id` (`BookingID`),
    INDEX `idx_user_id` (`UserID`),
    INDEX `idx_action` (`Action`),
    INDEX `idx_created_at` (`CreatedAt`),
    INDEX `idx_field_name` (`FieldName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `EmailTemplates`

Stores customizable email templates for automated notifications.

```sql
CREATE TABLE IF NOT EXISTS `EmailTemplates` (
  `TemplateID` INT PRIMARY KEY AUTO_INCREMENT,
  `TemplateType` VARCHAR(255) NOT NULL UNIQUE,
  `Subject` VARCHAR(255) NOT NULL,
  `Body` TEXT NOT NULL,
  `UseCustom` BOOLEAN NOT NULL DEFAULT FALSE,
  `IsEnabled` BOOLEAN DEFAULT TRUE,
  `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

### Table: `Feedback`

Stores customer feedback and reviews.

```sql
CREATE TABLE IF NOT EXISTS `Feedback` (
  `FeedbackID` INT PRIMARY KEY AUTO_INCREMENT,
  `BookingID` INT,
  `Rating` INT CHECK (`Rating` >= 1 AND `Rating` <= 5),
  `Comment` TEXT,
  `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`BookingID`) REFERENCES `Bookings`(`BookingID`)
);
```

---

### Table: `FacilityFeedback`

Stores feedback and ratings for individual facilities, linked to a main booking feedback entry.

```sql
CREATE TABLE IF NOT EXISTS `FacilityFeedback` (
  `FacilityFeedbackID` INT PRIMARY KEY AUTO_INCREMENT,
  `FeedbackID` INT,
  `FacilityID` INT,
  `Rating` INT NOT NULL CHECK (`Rating` >= 1 AND `Rating` <= 5),
  `Comment` TEXT,
  `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`FeedbackID`) REFERENCES `Feedback`(`FeedbackID`) ON DELETE CASCADE,
  FOREIGN KEY (`FacilityID`) REFERENCES `Facilities`(`FacilityID`) ON DELETE CASCADE
);
```

---

### Table: `FeedbackMedia`

Stores uploaded images and videos associated with a feedback entry.

```sql
CREATE TABLE IF NOT EXISTS `FeedbackMedia` (
  `MediaID` INT PRIMARY KEY AUTO_INCREMENT,
  `FeedbackID` INT,
  `MediaType` ENUM('Image', 'Video') NOT NULL,
  `MediaURL` VARCHAR(255) NOT NULL,
  `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`FeedbackID`) REFERENCES `Feedback`(`FeedbackID`) ON DELETE CASCADE
);
```

---
