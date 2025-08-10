<?php

class Facility {
    public $facilityId;
    public $resortId;
    public $name;
    public $description;
    public $capacity;
    public $pricePerHour;
    public $createdAt;

    public function __construct($resortId, $name, $description, $capacity, $pricePerHour) {
        $this->resortId = $resortId;
        $this->name = $name;
        $this->description = $description;
        $this->capacity = $capacity;
        $this->pricePerHour = $pricePerHour;
    }

    // Methods for managing facilities will go here.
}