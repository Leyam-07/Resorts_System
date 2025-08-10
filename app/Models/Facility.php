<?php

class Facility {
    public $facilityId;
    public $resortId;
    public $name;
    public $capacity;
    public $rate;
    public $createdAt;

    public function __construct($resortId, $name, $capacity, $rate) {
        $this->resortId = $resortId;
        $this->name = $name;
        $this->capacity = $capacity;
        $this->rate = $rate;
    }

    // Methods for managing facilities will go here.
}