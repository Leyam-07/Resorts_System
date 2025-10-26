<?php

require_once __DIR__ . '/../app/Models/EmailTemplate.php';

echo "Seeding email templates...\n";

try {
    EmailTemplate::createInitialTemplates();
    echo "Email templates seeded successfully.\n";
} catch (Exception $e) {
    echo "Error seeding email templates: " . $e->getMessage() . "\n";
}

?>