# Enhanced Suppliers Module API Documentation

## Overview
This document describes the enhanced Suppliers module API endpoints with comprehensive search functionality, proper relationships, and complete CRUD operations.

## Base URL
All endpoints are prefixed with: `/api/v1/suppliers`

## Authentication
All endpoints require authentication using Sanctum token:
```
Authorization: Bearer {token}
```

## Endpoints

### 1. List Suppliers
**GET** `/`

**Description:** Get paginated list of suppliers with optional search and sorting.

**Query Parameters:**
- `supplier_search` (string, optional): Search term for supplier name, number, code, email, phone, mobile
- `sort_by` (string, optional): Field to sort by (default: 'created_at')
- `sort_order` (string, optional): Sort direction 'asc' or 'desc' (default: 'desc')
- `per_page` (integer, optional): Items per page (default: 15, max: 100)
- `paginate` (boolean, optional): Whether to paginate results (default: true)

**Sortable Fields:**
- `id`, `supplier_number`, `supplier_name_ar`, `supplier_name_en`, `supplier_type`
- `balance`, `last_transaction_date`, `classification`, `status`
- `email`, `phone`, `mobile`, `created_at`, `updated_at`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "supplier_number": "SUP-0001",
      "supplier_name_ar": "شركة المورد الأول",
      "supplier_name_en": "First Supplier Company",
      "supplier_type": "business",
      "supplier_type_display": "Business",
      "balance": "1500.00",
      "last_transaction_date": "2024-01-15",
      "classification": "major",
      "classification_display": "Major Suppliers",
      "status": "active",
      "email": "supplier@example.com",
      "phone": "+1234567890",
      "mobile": "+0987654321",
      "branch": {
        "id": 1,
        "name": "Main Branch",
        "code": "MB001"
      },
      "currency": {
        "id": 1,
        "code": "USD",
        "name": "US Dollar",
        "symbol": "$"
      },
      "sales_representative": {
        "id": 1,
        "representative_number": "REP-0001",
        "first_name": "John",
        "last_name": "Doe"
      },
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75,
    "from": 1,
    "to": 15
  }
}
```

### 2. Advanced Search
**GET** `/search/advanced`

**Description:** Advanced search with multiple criteria and filters.

**Query Parameters:**
- `supplier_number_from` (string, optional): Supplier number range start
- `supplier_number_to` (string, optional): Supplier number range end
- `supplier_name` (string, optional): Search by supplier name (AR/EN)
- `date` (date, optional): Filter by specific creation date
- `date_from` (date, optional): Creation date range start
- `date_to` (date, optional): Creation date range end
- `last_transaction_date` (date, optional): Filter by specific last transaction date
- `last_transaction_date_from` (date, optional): Last transaction date range start
- `last_transaction_date_to` (date, optional): Last transaction date range end
- `balance` (decimal, optional): Filter by exact balance
- `balance_from` (decimal, optional): Balance range start
- `balance_to` (decimal, optional): Balance range end
- `branch_id` (integer, optional): Filter by branch
- `currency_id` (integer, optional): Filter by currency
- `supplier_type` (string, optional): Filter by supplier type (individual/business)
- `classification` (string, optional): Filter by classification (major/medium/minor)
- `status` (string, optional): Filter by status (active/inactive)
- `sort_by` (string, optional): Field to sort by
- `sort_order` (string, optional): Sort direction
- `per_page` (integer, optional): Items per page

**Response:** Same format as List Suppliers endpoint.

### 3. Create Supplier
**POST** `/`

**Description:** Create a new supplier with all required and optional fields.

**Request Body:**
```json
{
  "supplier_type": "business",
  "supplier_name_ar": "شركة المورد الجديد",
  "supplier_name_en": "New Supplier Company",
  "first_name": "Ahmed",
  "second_name": "Ali",
  "email": "supplier@example.com",
  "phone": "+1234567890",
  "mobile": "+0987654321",
  "address_one": "123 Main Street",
  "address_two": "Suite 100",
  "postal_code": "12345",
  "licensed_operator": "John Smith",
  "code_number": "CODE001",
  "branch_id": 1,
  "currency_id": 1,
  "department_id": 1,
  "project_id": 1,
  "donor_id": 1,
  "sales_representative_id": 1,
  "barcode_type_id": 1,
  "country_id": 1,
  "classification": "major",
  "balance": "1000.00",
  "last_transaction_date": "2024-01-15",
  "credit_limit": "5000.00",
  "payment_terms": 30,
  "notes": "Important supplier notes",
  "status": "active"
}
```

**Required Fields:**
- `supplier_type`: "individual" or "business"
- `supplier_name_ar`: Company Name/Trade Name (required)
- `classification`: "major", "medium", or "minor"
- `status`: "active" or "inactive"

**Auto-Generated Fields:**
- `supplier_number`: Sequential number (SUP-0001, SUP-0002, etc.)

**Response:**
```json
{
  "success": true,
  "data": {
    // Full supplier object with all relationships loaded
  }
}
```

### 4. Get Form Data
**GET** `/form-data/get-form-data`

**Description:** Get all dropdown data needed for supplier creation/editing forms.

**Response:**
```json
{
  "success": true,
  "data": {
    "supplier_types": {
      "individual": "Individual",
      "business": "Business"
    },
    "classifications": {
      "major": "Major Suppliers",
      "medium": "Medium Suppliers",
      "minor": "Minor Suppliers"
    },
    "status_options": {
      "active": "Active",
      "inactive": "Inactive"
    },
    "next_supplier_number": "SUP-0025",
    "branches": [
      {
        "id": 1,
        "name": "Main Branch",
        "code": "MB001",
        "display_name": "MB001 - Main Branch"
      }
    ],
    "departments": [
      {
        "id": 1,
        "name": "Sales Department",
        "number": "DEPT001",
        "display_name": "DEPT001 - Sales Department"
      }
    ],
    "projects": [
      {
        "id": 1,
        "name": "Project Alpha",
        "project_number": "PROJ001",
        "display_name": "PROJ001 - Project Alpha"
      }
    ],
    "donors": [
      {
        "id": 1,
        "donor_number": "DON001",
        "donor_name_ar": "المتبرع الأول",
        "display_name": "DON001 - المتبرع الأول"
      }
    ],
    "sales_representatives": [
      {
        "id": 1,
        "representative_number": "REP001",
        "first_name": "John",
        "last_name": "Doe",
        "display_name": "REP001 - John Doe"
      }
    ],
    "currencies": [
      {
        "id": 1,
        "code": "USD",
        "name": "US Dollar",
        "symbol": "$",
        "display_name": "USD - US Dollar ($)"
      }
    ],
    "barcode_types": [
      {
        "id": 1,
        "code": "C128",
        "name": "Code 128",
        "display_name": "C128 - Code 128"
      }
    ],
    "countries": [
      {
        "id": 1,
        "name": "United States",
        "code": "US",
        "display_name": "United States (US)"
      }
    ]
  }
}
```

### 5. Show Supplier
**GET** `/{supplier}`

**Description:** Get detailed information about a specific supplier.

**Response:**
```json
{
  "success": true,
  "data": {
    // Full supplier object with all relationships loaded
  }
}
```

### 6. Update Supplier
**PUT** `/{supplier}`

**Description:** Update an existing supplier.

**Request Body:** Same as Create Supplier (all fields optional except required ones)

**Response:** Same as Create Supplier

### 7. Delete Supplier (Soft Delete)
**DELETE** `/{supplier}`

**Description:** Soft delete a supplier (can be restored later).

**Response:**
```json
{
  "success": true,
  "message": "Supplier deleted successfully"
}
```

### 8. Restore Supplier
**POST** `/{supplier}/restore`

**Description:** Restore a soft-deleted supplier.

**Response:**
```json
{
  "success": true,
  "data": {
    // Restored supplier object
  }
}
```

### 9. Get Deleted Suppliers
**GET** `/deleted/list`

**Description:** Get list of soft-deleted suppliers.

**Query Parameters:** Same sorting and pagination options as List Suppliers

**Response:** Same format as List Suppliers

### 10. Force Delete Supplier
**DELETE** `/deleted/{id}/force-delete`

**Description:** Permanently delete a supplier (cannot be restored).

**Response:**
```json
{
  "success": true,
  "message": "Supplier permanently deleted",
  "supplier_number": "SUP-0001"
}
```

### 11. Get Search Form Data
**GET** `/form-data/get-search-form-data`

**Description:** Get dropdown data for search forms.

### 12. Get Sortable Fields
**GET** `/form-data/get-sortable-fields`

**Description:** Get list of fields that can be used for sorting.

## Field Descriptions

### Supplier Fields
- **supplier_type**: Individual or Business supplier
- **supplier_number**: Auto-generated sequential number (SUP-0001, SUP-0002, etc.)
- **supplier_name_ar**: Company Name/Trade Name (required)
- **supplier_name_en**: English company name (optional)
- **first_name**: Individual's first name (optional)
- **second_name**: Individual's second name (optional)
- **phone**: Phone number (optional)
- **mobile**: Mobile number (optional)
- **address_one**: Street Address 1 (optional)
- **address_two**: Street Address 2 (optional)
- **postal_code**: Postal code (optional)
- **licensed_operator**: Licensed operator name (optional)
- **code_number**: Manual code number (optional)
- **barcode_type_id**: Barcode type from library (optional)
- **branch_id**: Branch from branches table (optional)
- **department_id**: Department from departments table (optional)
- **project_id**: Project from projects table (optional)
- **donor_id**: Donor/Sponsor from donors table (optional)
- **currency_id**: Currency from currencies table (optional)
- **sales_representative_id**: Sales representative (optional)
- **email**: Email address (optional)
- **classification**: Major/Medium/Minor Suppliers (required)
- **custom_classification**: Custom classification option (optional)
- **balance**: Current balance (optional)
- **last_transaction_date**: Date of last transaction (optional)
- **notes**: Additional notes (optional)

## Error Responses

All endpoints return error responses in this format:
```json
{
  "error": "Error message describing what went wrong"
}
```

Common HTTP status codes:
- `200`: Success
- `422`: Validation Error
- `404`: Resource Not Found
- `500`: Internal Server Error

## Notes

1. **Sequential Supplier Numbers**: Supplier numbers are automatically generated in sequence (SUP-0001, SUP-0002, etc.)
2. **Relationships**: All foreign key relationships are properly linked to their respective tables
3. **Soft Delete**: Suppliers are soft deleted with `deleted_by` field tracking who deleted them
4. **Search**: Comprehensive search functionality supports exact matches, ranges, and partial text searches
5. **Sorting**: All major fields support ascending/descending sorting
6. **Validation**: Comprehensive validation ensures data integrity
7. **Audit Trail**: Created by, updated by, and deleted by fields track all changes
