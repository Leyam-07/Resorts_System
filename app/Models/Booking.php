<?php

class Booking {
    public $bookingId;
    public $customerId;
    public $facilityId;
    public $bookingDate;
    public $startTime;
    public $endTime;
    public $numberOfGuests;
    public $status; // e.g., 'pending', 'confirmed', 'cancelled'
    public $createdAt;

    public function __construct($customerId, $facilityId, $bookingDate, $startTime, $endTime, $numberOfGuests, $status = 'pending') {
        $this->customerId = $customerId;
        $this->facilityId = $facilityId;
        $this->bookingDate = $bookingDate;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->numberOfGuests = $numberOfGuests;
        $this->status = $status;
    }

    // Methods for creating, retrieving, updating, and canceling bookings will go here.
}