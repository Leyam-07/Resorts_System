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
  `FirstName` VARCHAR(255),
  `LastName` VARCHAR(255),
  `Email` VARCHAR(255) NOT NULL UNIQUE,
  `PhoneNumber` VARCHAR(20),
  `Notes` TEXT,
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
  `ContactPerson` VARCHAR(255)
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
  `Capacity` INT,
  `Rate` DECIMAL(10, 2),
  FOREIGN KEY (`ResortID`) REFERENCES `Resorts`(`ResortID`)
);
```

---

### Table: `Bookings`

The central table for managing all reservations.

```sql
CREATE TABLE IF NOT EXISTS `Bookings` (
  `BookingID` INT PRIMARY KEY AUTO_INCREMENT,
  `CustomerID` INT,
  `FacilityID` INT,
  `BookingDate` DATE NOT NULL,
  `StartTime` TIME NOT NULL,
  `EndTime` TIME NOT NULL,
  `NumberOfGuests` INT,
  `Status` ENUM('Pending', 'Confirmed', 'Cancelled', 'Completed') NOT NULL,
  `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`CustomerID`) REFERENCES `Users`(`UserID`),
  FOREIGN KEY (`FacilityID`) REFERENCES `Facilities`(`FacilityID`)
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
  `PaymentMethod` ENUM('Gcash', 'Bank Transfer', 'Cash') NOT NULL,
  `PaymentDate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `Status` ENUM('Paid', 'Unpaid', 'Partial') NOT NULL,
  `ProofOfPaymentURL` VARCHAR(255),
  FOREIGN KEY (`BookingID`) REFERENCES `Bookings`(`BookingID`)
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
