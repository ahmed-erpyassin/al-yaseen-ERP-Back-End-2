<?php

/**
 * Comprehensive Employee API Test Guide
 * 
 * This file contains all the API endpoints and test cases for the Employee module
 */

echo "=== EMPLOYEE API COMPREHENSIVE TEST GUIDE ===\n\n";

// Base URL for API
$baseUrl = 'http://localhost:8000/api/v1/employees';

echo "## 1. BASIC CRUD OPERATIONS\n\n";

echo "### 1.1 Get All Employees (with advanced filtering and sorting)\n";
echo "GET {$baseUrl}/\n";
echo "Query Parameters:\n";
echo "- search: General search term\n";
echo "- employee_name: Search by name specifically\n";
echo "- department_id: Filter by department\n";
echo "- currency_id: Filter by currency\n";
echo "- employee_number_from: Employee number range start\n";
echo "- employee_number_to: Employee number range end\n";
echo "- balance_from: Balance range start\n";
echo "- balance_to: Balance range end\n";
echo "- salary_from: Salary range start\n";
echo "- salary_to: Salary range end\n";
echo "- hire_date_from: Hire date range start (YYYY-MM-DD)\n";
echo "- hire_date_to: Hire date range end (YYYY-MM-DD)\n";
echo "- birth_date_from: Birth date range start (YYYY-MM-DD)\n";
echo "- birth_date_to: Birth date range end (YYYY-MM-DD)\n";
echo "- gender: male/female\n";
echo "- is_driver: true/false\n";
echo "- is_sales: true/false\n";
echo "- sort_by: Field to sort by (employee_number, first_name, last_name, hire_date, salary, balance, etc.)\n";
echo "- sort_direction: asc/desc\n";
echo "- per_page: Items per page (1-100)\n\n";

echo "Example: GET {$baseUrl}/?search=أحمد&department_id=1&sort_by=hire_date&sort_direction=desc&per_page=20\n\n";

echo "### 1.2 Create New Employee\n";
echo "POST {$baseUrl}/\n";
$employeeData = [
    'company_id' => 1,
    'branch_id' => 1,
    'department_id' => 1,
    'job_title_id' => 1,
    'employee_number' => 'EMP-0001', // Auto-generated if not provided
    'first_name' => 'أحمد',
    'last_name' => 'محمد',
    'second_name' => 'علي',
    'third_name' => 'حسن',
    'nickname' => 'أبو علي',
    'phone1' => '01234567890',
    'phone2' => '01987654321',
    'email' => 'ahmed.mohamed@example.com',
    'birth_date' => '1990-01-15',
    'address' => 'شارع الجامعة، المنصورة، مصر',
    'national_id' => '29001150123456',
    'id_number' => 'ID123456',
    'gender' => 'male',
    'wives_count' => 1,
    'children_count' => 2,
    'dependents_count' => 4,
    'students_count' => 1,
    'is_driver' => true,
    'is_sales' => false,
    'car_number' => 'ABC-1234',
    'hire_date' => '2023-01-01',
    'employee_code' => 'EMP001',
    'employee_identifier' => 'AHMED001',
    'job_address' => 'مكتب المنصورة الرئيسي',
    'salary' => 5000.00,
    'billing_rate' => 50.00,
    'monthly_discount' => 100.00,
    'balance' => 1000.00,
    'currency_id' => 1,
    'currency_rate' => 1.0000, // Auto-fetched if not provided
    'notes' => 'موظف ممتاز ومتفاني في العمل'
];
echo "Body: " . json_encode($employeeData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "### 1.3 Get Single Employee (with all relationships)\n";
echo "GET {$baseUrl}/{employee_id}\n";
echo "Returns: Complete employee data with all relationships and computed fields\n\n";

echo "### 1.4 Update Employee\n";
echo "PUT {$baseUrl}/{employee_id}\n";
echo "Body: Same structure as create, but all fields are optional\n";
echo "Features: Validates unique constraints, logs changes, updates currency rates\n\n";

echo "### 1.5 Delete Employee (Soft Delete)\n";
echo "DELETE {$baseUrl}/{employee_id}\n";
echo "Features: Validates subordinates, logs deletion, prevents deletion if employee has subordinates\n\n";

echo "## 2. ADVANCED SEARCH OPERATIONS\n\n";

echo "### 2.1 Advanced Search\n";
echo "POST {$baseUrl}/search/advanced\n";
echo "Body: All the same parameters as GET employees, but in POST body for complex searches\n\n";

echo "### 2.2 Quick Search\n";
echo "POST {$baseUrl}/search/quick\n";
echo "Body: {\"query\": \"search_term\", \"limit\": 10}\n";
echo "Use: Fast autocomplete-style search by name or employee number\n\n";

echo "### 2.3 Get Search Form Data\n";
echo "GET {$baseUrl}/search/form-data\n";
echo "Returns: All dropdown options for search filters (departments, currencies, sort options, etc.)\n\n";

echo "### 2.4 Get Employee Statistics\n";
echo "GET {$baseUrl}/search/statistics\n";
echo "Returns: Dashboard statistics (total employees, drivers, sales reps, gender distribution, etc.)\n\n";

echo "### 2.5 Export Employees\n";
echo "POST {$baseUrl}/search/export\n";
echo "Body: {\"format\": \"csv|excel|pdf\", \"fields\": [\"employee_number\", \"full_name\", \"email\"]}\n";
echo "Note: Currently returns preview data, actual file generation pending\n\n";

echo "## 3. SOFT DELETE MANAGEMENT\n\n";

echo "### 3.1 Get Deleted Employees\n";
echo "GET {$baseUrl}/deleted/\n";
echo "Query Parameters: search, per_page\n";
echo "Returns: List of soft-deleted employees\n\n";

echo "### 3.2 Restore Deleted Employee\n";
echo "POST {$baseUrl}/deleted/{employee_id}/restore\n";
echo "Restores a soft-deleted employee\n\n";

echo "## 4. HELPER ENDPOINTS\n\n";

echo "### 4.1 Get Form Data\n";
echo "GET {$baseUrl}/form-data/get\n";
echo "Returns: All dropdown data needed for employee forms\n\n";

echo "### 4.2 Get Next Employee Number\n";
echo "GET {$baseUrl}/next-number/generate\n";
echo "Returns: Next sequential employee number (EMP-0001 format)\n\n";

echo "## 5. CURRENCY RATE ENDPOINTS\n\n";

echo "### 5.1 Get Live Currency Rate\n";
echo "POST {$baseUrl}/currency-rates/live-rate\n";
echo "Body: {\"currency_id\": 1}\n";
echo "Returns: Live exchange rate from external API\n\n";

echo "### 5.2 Get Multiple Live Rates\n";
echo "POST {$baseUrl}/currency-rates/live-rates\n";
echo "Body: {\"currency_ids\": [1, 2, 3]}\n\n";

echo "### 5.3 Update Currency Rate Manually\n";
echo "PUT {$baseUrl}/currency-rates/update-rate\n";
echo "Body: {\"currency_id\": 1, \"rate\": 1.2345}\n\n";

echo "## 6. SAMPLE cURL COMMANDS\n\n";

echo "### Get employees with advanced filtering:\n";
echo "curl -X GET '{$baseUrl}/?employee_name=أحمد&department_id=1&balance_from=0&balance_to=5000&sort_by=salary&sort_direction=desc' \\\n";
echo "  -H 'Authorization: Bearer YOUR_TOKEN' \\\n";
echo "  -H 'Content-Type: application/json'\n\n";

echo "### Create employee:\n";
echo "curl -X POST '{$baseUrl}/' \\\n";
echo "  -H 'Authorization: Bearer YOUR_TOKEN' \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -d '" . json_encode($employeeData) . "'\n\n";

echo "### Advanced search:\n";
echo "curl -X POST '{$baseUrl}/search/advanced' \\\n";
echo "  -H 'Authorization: Bearer YOUR_TOKEN' \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -d '{\"employee_number_from\":\"EMP-0001\",\"employee_number_to\":\"EMP-0100\",\"department_id\":1,\"sort_by\":\"hire_date\"}'\n\n";

echo "### Quick search:\n";
echo "curl -X POST '{$baseUrl}/search/quick' \\\n";
echo "  -H 'Authorization: Bearer YOUR_TOKEN' \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -d '{\"query\":\"أحمد\",\"limit\":5}'\n\n";

echo "### Get live currency rate:\n";
echo "curl -X POST '{$baseUrl}/currency-rates/live-rate' \\\n";
echo "  -H 'Authorization: Bearer YOUR_TOKEN' \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -d '{\"currency_id\":1}'\n\n";

echo "## 7. EXPECTED RESPONSE FORMATS\n\n";

$expectedListResponse = [
    'success' => true,
    'data' => [
        [
            'id' => 1,
            'employee_number' => 'EMP-0001',
            'full_name' => 'أحمد علي حسن محمد',
            'employee_type' => 'driver',
            'email' => 'ahmed.mohamed@example.com',
            'phone1' => '01234567890',
            'company' => ['id' => 1, 'title' => 'شركة الياسين'],
            'department' => ['id' => 1, 'name' => 'قسم المبيعات'],
            'currency' => ['id' => 1, 'code' => 'USD', 'symbol' => '$'],
            'formatted' => [
                'salary' => '5,000.00',
                'balance' => '1,000.00',
                'hire_date' => '01/01/2023',
                'employment_duration' => '1 year, 9 months'
            ],
            'computed' => [
                'total_family_members' => 4,
                'net_salary' => 4900.00,
                'is_manager' => false,
                'employment_status' => 'Active'
            ]
        ]
    ],
    'pagination' => [
        'current_page' => 1,
        'last_page' => 5,
        'per_page' => 15,
        'total' => 67,
        'from' => 1,
        'to' => 15
    ],
    'filters' => [
        'search' => 'أحمد',
        'department_id' => 1,
        'sort_by' => 'hire_date',
        'sort_direction' => 'desc'
    ],
    'message' => 'Employees retrieved successfully.'
];

echo "### List Response Format:\n";
echo json_encode($expectedListResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "## 8. SORTING CAPABILITIES\n\n";
echo "All employee fields support sorting with click-to-sort functionality:\n";
echo "- employee_number: Employee Number (ascending/descending)\n";
echo "- first_name, last_name: Names (alphabetical)\n";
echo "- hire_date, birth_date: Dates (chronological)\n";
echo "- salary, balance: Numerical values\n";
echo "- department_id, job_title_id: By relationship names\n";
echo "- created_at, updated_at: System timestamps\n\n";

echo "## 9. VALIDATION RULES\n\n";
echo "### Required Fields:\n";
echo "- first_name, last_name, email, birth_date, hire_date, salary, currency_id\n\n";
echo "### Unique Fields:\n";
echo "- employee_number (per company)\n";
echo "- email (globally unique)\n\n";
echo "### Format Validations:\n";
echo "- email: Valid email format\n";
echo "- birth_date: Date before today\n";
echo "- hire_date: Date before or equal to today\n";
echo "- phone numbers: Cleaned automatically\n";
echo "- salary, balance: Positive numbers\n\n";

echo "## 10. FEATURES IMPLEMENTED\n\n";
echo "✅ Advanced search with multiple criteria\n";
echo "✅ Range searches (employee number, balance, salary, dates)\n";
echo "✅ Comprehensive sorting on all fields\n";
echo "✅ Soft delete with restore functionality\n";
echo "✅ Live currency rate integration\n";
echo "✅ Audit logging for all changes\n";
echo "✅ Relationship loading and computed fields\n";
echo "✅ Validation with detailed error messages\n";
echo "✅ Auto-generation of employee numbers\n";
echo "✅ Export functionality (structure ready)\n";
echo "✅ Statistics and dashboard data\n";
echo "✅ Quick search for autocomplete\n";
echo "✅ Comprehensive API documentation\n\n";

echo "=== END OF EMPLOYEE API GUIDE ===\n";
