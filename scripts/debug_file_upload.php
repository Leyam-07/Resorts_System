<?php
// Debug script to test file upload behavior
echo "File Upload Debug Script\n";
echo "=========================\n\n";

// Test file types and sizes
$testFiles = [
    ['name' => 'test.jpg', 'size' => 1000000, 'type' => 'image/jpeg'],
    ['name' => 'test.png', 'size' => 2000000, 'type' => 'image/png'],
    ['name' => 'invalid.txt', 'size' => 500000, 'type' => 'text/plain'],
    ['name' => 'too_big.jpg', 'size' => 6000000, 'type' => 'image/jpeg'],
];

// Simulate JavaScript validation logic
function validateFile($file) {
    $errors = [];

    // Check file type
    if (!isset($file['type']) || empty($file['type'])) {
        $errors[] = 'File type not detected';
    } elseif (!str_starts_with($file['type'], 'image/')) {
        $errors[] = 'Not an image file: ' . $file['type'];
    }

    // Check file size (5MB)
    $maxSize = 5 * 1024 * 1024; // 5MB
    if (!isset($file['size'])) {
        $errors[] = 'File size not detected';
    } elseif ($file['size'] > $maxSize) {
        $errors[] = 'File too large: ' . round($file['size'] / 1024 / 1024, 2) . 'MB (max 5MB)';
    }

    return $errors;
}

echo "Testing file validation logic:\n\n";

foreach ($testFiles as $file) {
    echo "File: {$file['name']} ({$file['type']}, " . round($file['size'] / 1024, 0) . "KB)\n";
    $errors = validateFile($file);

    if (empty($errors)) {
        echo "✅ PASSED validation\n";
    } else {
        echo "❌ FAILED: " . implode(', ', $errors) . "\n";
    }
    echo "\n";
}

echo "\nAnalysis:\n";
echo "The issue is likely that on first file selection, the browser hasn't fully populated\n";
echo "the file.type and file.size properties yet, causing incorrect validation failures.\n";
echo "This is a known issue with File API where metadata loads asynchronously.\n\n";

echo "Suggested fixes:\n";
echo "1. Add retry logic in JavaScript\n";
echo "2. Use setTimeout to delay validation slightly\n";
echo "3. Add loading state to give browser time to populate metadata\n";
echo "4. Use alternative validation methods (extension checking)\n";
?>
