<?php

// test_financial_reports.php

// Purpose: To test the financial reporting and booking history methods in the Booking model.

// Set the correct timezone
date_default_timezone_set('Asia/Manila');

// Include necessary files
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Models/Booking.php';

echo "<h1>Financial and Booking History Report Test</h1>";
echo "<p>This script tests the `getMonthlyIncome` and `getBookingHistory` methods from the `Booking` class.</p>";
echo "<hr>";

try {
    // --- Test Monthly Income ---
    $currentYear = date('Y');
    $currentMonth = date('m');

    echo "<h2>Testing: getMonthlyIncome({$currentYear}, {$currentMonth})</h2>";
    
    $monthlyIncome = Booking::getMonthlyIncome($currentYear, $currentMonth);

    if ($monthlyIncome !== false) {
        echo "<p style='color:green; font-weight:bold;'>SUCCESS: Monthly Income for {$currentYear}-{$currentMonth} is: " . number_format($monthlyIncome, 2) . "</p>";
    } else {
        echo "<p style='color:red; font-weight:bold;'>ERROR: Failed to retrieve monthly income.</p>";
    }

    echo "<hr>";

    // --- Test Booking History ---
    $limit = 15;
    echo "<h2>Testing: getBookingHistory(limit: {$limit})</h2>";
    
    $bookingHistory = Booking::getBookingHistory($limit);

    if ($bookingHistory !== false) {
        if (empty($bookingHistory)) {
            echo "<p style='color:orange;'>No past booking history found.</p>";
        } else {
            echo "<p style='color:green; font-weight:bold;'>SUCCESS: Retrieved " . count($bookingHistory) . " booking(s) from history.</p>";
            echo "<table border='1' cellpadding='5' cellspacing='0'>
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer</th>
                        <th>Facility</th>
                        <th>Booking Date</th>
                        <th>Status</th>
                    </tr>";
            foreach ($bookingHistory as $booking) {
                echo "<tr>
                        <td>{$booking->BookingID}</td>
                        <td>{$booking->CustomerName}</td>
                        <td>{$booking->FacilityName}</td>
                        <td>{$booking->BookingDate}</td>
                        <td>{$booking->Status}</td>
                      </tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p style='color:red; font-weight:bold;'>ERROR: Failed to retrieve booking history.</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color:red; font-weight:bold;'>DATABASE ERROR: " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p style='color:red; font-weight:bold;'>An unexpected error occurred: " . $e->getMessage() . "</p>";
}

?>