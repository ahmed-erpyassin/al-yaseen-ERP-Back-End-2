# Customer Module API Documentation

## Overview
This document describes all available API endpoints for the Customer module with comprehensive search, filtering, and sorting capabilities.

## Base URL
All endpoints are prefixed with: `/api/v1/customers`

## Authentication
All endpoints require authentication using Sanctum: `Authorization: Bearer {token}`

## Available Endpoints

### 1. Basic CRUD Operations

#### GET `/` - List Customers
Get all customers with optional pagination and basic search.

**Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `paginate` (boolean, optional): Enable pagination (default: true)
- `customerSearch` (string, optional): Basic search across multiple fields
- `sort_by` (string, optional): Sort field (default: created_at)
- `sort_order` (string, optional): Sort order - asc/desc (default: desc)

**Response:**
```json
{
    "success": true,
    "data": [...],
    "pagination": {...}
}
```

#### POST `/` - Create Customer
Create a new customer.

#### GET `/{customer}` - Show Customer
Get specific customer with all relationships loaded.

#### PUT/PATCH `/{customer}` - Update Customer
Update customer with comprehensive validation and relationship loading.

#### DELETE `/{customer}` - Delete Customer
Soft delete customer with audit trail.

### 2. Advanced Search Operations

#### POST `/advanced-search` - Advanced Search
Comprehensive search with multiple filters.

**Request Body:**
```json
{
    "customer_number_from": "C001",
    "customer_number_to": "C999",
    "customer_name": "John",
    "last_transaction_date": "2025-01-15",
    "last_transaction_date_from": "2025-01-01",
    "last_transaction_date_to": "2025-01-31",
    "sales_representative": 1,
    "currency_id": 1,
    "status": "active",
    "company_id": 1,
    "email": "john@example.com",
    "phone": "123456789",
    "category": "retail",
    "country_id": 1,
    "region_id": 1,
    "city_id": 1,
    "created_from": "2025-01-01",
    "created_to": "2025-01-31",
    "sort_by": "customer_number",
    "sort_order": "asc",
    "per_page": 20,
    "paginate": true
}
```

**Search Capabilities:**
1. **Customer Number Range**: Search from-to number range
2. **Customer Name**: Search across first_name, second_name, company_name
3. **Last Transaction Date**: 
   - Exact date search
   - Date range search (from-to)
4. **Sales Representative**: Filter by employee_id
5. **Currency**: Filter by currency_id
6. **Status**: Filter by active/inactive
7. **Location**: Filter by country, region, city
8. **Contact Info**: Search by email, phone
9. **Date Range**: Filter by creation date range

### 3. Sorting Operations

#### GET `/sort/{field}` - Sort by Field
Sort customers by specific field with ascending/descending order.

**Parameters:**
- `field` (string, required): Field to sort by
- `order` (string, optional): asc/desc (default: asc)
- `per_page` (integer, optional): Items per page

**Allowed Sort Fields:**
- id, customer_number, company_name, first_name, second_name
- email, phone, mobile, status, created_at, updated_at
- contact_name, address_one, postal_code, tax_number, category

### 4. Field-Specific Operations

#### GET `/field/{field}/{value}` - Search by Field
Get customers matching specific field value.

**Allowed Search Fields:**
- id, customer_number, company_name, first_name, second_name
- email, phone, mobile, contact_name, tax_number

#### GET `/field-values/{field}` - Get Field Values
Get all unique values for a specific field (useful for dropdowns).

**Allowed Fields:**
- status, category, invoice_type, country_id, region_id
- city_id, currency_id, employee_id

### 5. Transaction-Related Operations

#### GET `/with-transactions` - Customers with Transaction Data
Get customers with their last transaction date information.

**Response includes:**
- All customer data
- `last_transaction_date`: Latest transaction from sales or invoices
- `sales_count`: Number of sales transactions
- `invoices_count`: Number of invoices

### 6. Legacy Search (Backward Compatibility)

#### GET `/search/{query}` - Basic Search
Simple search across multiple customer fields.

### 7. Filter Operations

#### GET `/filter/status/{status}` - Filter by Status
Filter customers by active/inactive status.

#### GET `/filter/company/{companyId}` - Filter by Company
Filter customers by company ID.

### 8. Bulk Operations

#### DELETE `/bulk/delete` - Bulk Delete
Soft delete multiple customers.

**Request Body:**
```json
{
    "customer_ids": [1, 2, 3, 4, 5]
}
```

#### POST `/bulk/restore` - Bulk Restore
Restore multiple soft-deleted customers.

### 9. Soft Delete Operations

#### POST `/{customer}/restore` - Restore Customer
Restore a soft-deleted customer.

### 10. Statistics and Reports

#### GET `/stats/overview` - Customer Statistics
Get comprehensive customer statistics including transaction data.

**Response includes:**
- Total customers count
- Active/inactive customers count
- Deleted customers count
- Monthly/yearly statistics
- Customers with sales/invoices
- Statistics by status and category

### 11. Import/Export Operations

#### GET `/export/excel` - Export to Excel
Export customers to Excel format (placeholder for future implementation).

#### POST `/import/excel` - Import from Excel
Import customers from Excel file (placeholder for future implementation).

## Response Format

All endpoints return responses in this format:
```json
{
    "success": true,
    "data": [...],
    "pagination": {...},  // Only for paginated responses
    "count": 10,          // Only for non-paginated responses
    "message": "...",     // Only for operations like delete
    "filters_applied": {...}, // Only for search operations
    "sort_info": {...}    // Only for sort operations
}
```

## Error Handling

Error responses follow this format:
```json
{
    "error": "Error message",
    "details": "Detailed error information"
}
```

## Customer Resource Structure

The customer resource includes:
- Basic customer information
- Related entities (user, company, currency, etc.)
- Transaction statistics
- Audit information (created_by, updated_by, deleted_by)
- Timestamps

## Notes

1. All search parameters are optional
2. Pagination is enabled by default
3. Soft deletes are implemented with audit trail
4. All relationships are eagerly loaded for comprehensive data
5. Field validation ensures data integrity
6. Sorting supports all major customer fields
7. Transaction data is calculated dynamically
