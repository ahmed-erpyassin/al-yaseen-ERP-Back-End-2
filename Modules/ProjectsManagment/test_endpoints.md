# Project Management API Test Guide

## Quick Test Endpoints

### 1. Test Dropdown Data Endpoints

#### Get Customers
```bash
GET /api/v1/projects/customers/list
Authorization: Bearer {your-token}
```

#### Get Currencies
```bash
GET /api/v1/projects/currencies/list
Authorization: Bearer {your-token}
```

#### Get Employees
```bash
GET /api/v1/projects/employees/list
Authorization: Bearer {your-token}
```

#### Get Countries
```bash
GET /api/v1/projects/countries/list
Authorization: Bearer {your-token}
```

#### Get Project Statuses
```bash
GET /api/v1/projects/statuses/list
Authorization: Bearer {your-token}
```

### 2. Test Customer Data Auto-Population

#### Get Customer Data (replace {customerId} with actual customer ID)
```bash
GET /api/v1/projects/customers/{customerId}/data
Authorization: Bearer {your-token}
```

### 3. Test Utility Functions

#### Generate Project Code
```bash
GET /api/v1/projects/generate-code
Authorization: Bearer {your-token}
```

#### Calculate VAT
```bash
POST /api/v1/projects/calculate-vat
Authorization: Bearer {your-token}
Content-Type: application/json

{
    "price": 10000.00,
    "company_id": 1,
    "include_vat": true
}
```

### 4. Test Project Creation

#### Create New Project
```bash
POST /api/v1/projects
Authorization: Bearer {your-token}
Content-Type: application/json

{
    "customer_id": 1,
    "currency_id": 1,
    "currency_price": 10000.00,
    "include_vat": true,
    "name": "Test Project",
    "description": "This is a test project",
    "project_value": 15000.00,
    "manager_id": 1,
    "project_manager_name": "John Doe",
    "start_date": "2025-01-15",
    "end_date": "2025-06-15",
    "status": "draft",
    "country_id": 1,
    "notes": "Test project notes",
    "branch_id": 1,
    "fiscal_year_id": 1,
    "cost_center_id": 1
}
```

### 5. Test Project Listing

#### Get All Projects
```bash
GET /api/v1/projects
Authorization: Bearer {your-token}
```

#### Get Single Project (replace {id} with actual project ID)
```bash
GET /api/v1/projects/{id}
Authorization: Bearer {your-token}
```

## Expected Behavior

### Auto-Population Features:
1. **Project Code**: Automatically generated as PRJ-YYYY-NNNN
2. **Date/Time**: Automatically set to current date/time
3. **Customer Data**: When customer_id is provided, customer_name, customer_email, customer_phone, and licensed_operator are auto-populated
4. **VAT Calculation**: When include_vat is true, currency_price is adjusted based on company's VAT rate

### Validation Rules:
- customer_id: Required, must exist in customers table
- currency_id: Required, must exist in currencies table
- currency_price: Required, numeric, minimum 0
- name: Required, string, max 255 characters
- project_value: Required, numeric, minimum 0
- manager_id: Required, must exist in users table
- start_date: Required, date, must be today or later
- end_date: Required, date, must be after start_date
- status: Required, must be one of: draft, open, on-hold, cancelled, closed
- country_id: Required, must exist in countries table

### Response Format:
All endpoints return JSON in this format:
```json
{
    "success": true/false,
    "data": {...},
    "message": "Description of the result"
}
```

## Troubleshooting

### Common Issues:
1. **Authentication Error**: Make sure you're sending the Bearer token
2. **Validation Errors**: Check that all required fields are provided and valid
3. **Foreign Key Errors**: Ensure referenced IDs exist in their respective tables
4. **Company Access**: User must have a company_id to access company-specific data

### Database Requirements:
Make sure these tables exist and have data:
- customers (with active status)
- currencies
- users (employees)
- countries
- companies (with vat_rate field)
- branches
- fiscal_years
- cost_centers
