# Sales Module - Outgoing Offers APIs

## Overview
This document describes the APIs for managing outgoing offers (quotations) in the Sales module.

## Base URL
```
/api/v1/sales/outgoing-offers
```

## Authentication
All endpoints require authentication using Sanctum token:
```
Authorization: Bearer {token}
```

## Endpoints

### 1. Get All Outgoing Offers
**GET** `/api/v1/sales/outgoing-offers`

**Query Parameters:**
- `customer_search` (optional): Search by customer name
- `status` (optional): Filter by status (draft, approved, sent, invoiced, cancelled)
- `sort_by` (optional): Sort field (default: created_at)
- `sort_order` (optional): Sort direction (asc/desc, default: desc)
- `per_page` (optional): Items per page (default: 15)

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "customer": {
                "id": 1,
                "name": "Customer Name",
                "email": "customer@example.com",
                "phone": "+1234567890"
            },
            "employee": {
                "id": 1,
                "name": "Employee Name",
                "email": "employee@example.com"
            },
            "currency": {
                "id": 1,
                "name": "US Dollar",
                "code": "USD",
                "symbol": "$"
            },
            "items": [...],
            "invoice_number": "INV-001",
            "status": "draft",
            "status_label": "مسودة",
            "total_amount": 1000.00,
            "created_at": "2024-01-01T00:00:00.000000Z"
        }
    ]
}
```

### 2. Create Outgoing Offer
**POST** `/api/v1/sales/outgoing-offers`

**Request Body:**
```json
{
    "branch_id": 1,
    "currency_id": 1,
    "employee_id": 1,
    "customer_id": 1,
    "journal_id": 1,
    "journal_number": 1001,
    "invoice_number": "INV-001",
    "time": "10:30:00",
    "due_date": "2024-02-01",
    "cash_paid": 0,
    "checks_paid": 0,
    "allowed_discount": 0,
    "exchange_rate": 1.0,
    "notes": "Additional notes",
    "items": [
        {
            "item_id": 1,
            "description": "Product description",
            "quantity": 2,
            "unit_price": 100.00,
            "discount_rate": 10,
            "tax_rate": 15,
            "total_foreign": 207.00,
            "total_local": 207.00,
            "total": 207.00
        }
    ]
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "customer": {...},
        "employee": {...},
        "currency": {...},
        "items": [...],
        "status": "draft",
        "total_amount": 207.00,
        "created_at": "2024-01-01T00:00:00.000000Z"
    },
    "message": "Outgoing offer created successfully"
}
```

### 3. Get Single Outgoing Offer
**GET** `/api/v1/sales/outgoing-offers/{id}`

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "customer": {...},
        "employee": {...},
        "currency": {...},
        "items": [...],
        "status": "draft",
        "total_amount": 207.00
    }
}
```

### 4. Update Outgoing Offer
**PUT** `/api/v1/sales/outgoing-offers/{id}`

**Request Body:** Same as create endpoint

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "customer": {...},
        "employee": {...},
        "currency": {...},
        "items": [...],
        "status": "draft",
        "total_amount": 250.00
    },
    "message": "Outgoing offer updated successfully"
}
```

### 5. Delete Outgoing Offer
**DELETE** `/api/v1/sales/outgoing-offers/{id}`

**Response:**
```json
{
    "success": true,
    "message": "Outgoing offer deleted successfully"
}
```

### 6. Approve Outgoing Offer
**PATCH** `/api/v1/sales/outgoing-offers/{id}/approve`

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "status": "approved",
        "status_label": "معتمد"
    },
    "message": "Outgoing offer approved successfully"
}
```

### 7. Send Outgoing Offer
**PATCH** `/api/v1/sales/outgoing-offers/{id}/send`

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "status": "sent",
        "status_label": "مرسل"
    },
    "message": "Outgoing offer sent successfully"
}
```

### 8. Cancel Outgoing Offer
**PATCH** `/api/v1/sales/outgoing-offers/{id}/cancel`

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "status": "cancelled",
        "status_label": "ملغي"
    },
    "message": "Outgoing offer cancelled successfully"
}
```

## Status Flow
1. **draft** → **approved** → **sent** → **invoiced**
2. **draft** → **cancelled**
3. **approved** → **cancelled**
4. **sent** → **cancelled**

## Error Responses
```json
{
    "success": false,
    "error": "Error description",
    "message": "Detailed error message"
}
```

## Validation Rules
- All required fields must be provided
- `due_date` must be today or later
- `exchange_rate` must be greater than 0
- At least one item is required
- Item quantities must be greater than 0
- Discount and tax rates must be between 0-100%
- Only draft offers can be updated or deleted

## Required Database Tables
The following tables must exist in your database for the APIs to work properly:

1. **sales** - Main sales table (created by Sales module migration)
2. **sales_items** - Sales items table (created by Sales module migration)
3. **customers** - Customers table (from Customers module)
4. **employees** - Employees table (from HumanResources module)
5. **currencies** - Currencies table (from FinancialAccounts module)
6. **branches** - Branches table (from Companies module)
7. **items** - Items table (from Inventory module)
8. **users** - Users table (from Users module)

## Database Setup
Make sure to run the following migrations in order:
1. Users module migrations
2. Companies module migrations
3. FinancialAccounts module migrations
4. Customers module migrations
5. HumanResources module migrations
6. Inventory module migrations
7. Sales module migrations

## Model Relationships
- Sale belongs to Customer (customers table)
- Sale belongs to Employee (employees table)
- Sale belongs to Currency (currencies table)
- Sale belongs to Branch (branches table)
- Sale has many SaleItems
- SaleItem belongs to Item (items table)
