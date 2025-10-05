<?php

require_once 'vendor/autoload.php';

use Modules\HumanResources\Models\Department;

// Test department number generation
echo "Testing department number generation...\n";

try {
    $nextNumber = Department::generateDepartmentNumber(1);
    echo "Next department number: " . $nextNumber . "\n";
    echo "Department functionality is working!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
