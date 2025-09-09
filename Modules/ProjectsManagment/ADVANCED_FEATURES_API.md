# Advanced Project Management API Features

## Overview
This document covers the advanced features implemented for the Project Management system including search, filtering, sorting, field selection, and soft delete functionality.

## Base URL
All endpoints are prefixed with: `/api/v1/projects`

## Authentication
All endpoints require authentication using Sanctum token:
```
Authorization: Bearer {your-token}
```

---

## 🔍 Advanced Search Features

### 1. Enhanced Project Listing with Search
**GET** `/api/v1/projects`

Now supports advanced search parameters:

**Query Parameters:**
```
project_number: Search by project number (partial match)
project_name: Search by project name (partial match)
customer_name: Search by customer name (partial match)
status: Filter by project status
project_manager_name: Search by project manager name (partial match)
exact_date: Search by exact project date (YYYY-MM-DD)
date_from: Search projects from this date onwards
date_to: Search projects up to this date
start_date_from: Filter by start date range (from)
start_date_to: Filter by start date range (to)
end_date_from: Filter by end date range (from)
end_date_to: Filter by end date range (to)
general_search: Search across multiple fields
sort_field: Field to sort by
sort_direction: asc/desc
per_page: Results per page (1-100)
```

**Example Request:**
```
GET /api/v1/projects?customer_name=Ahmed&status=open&date_from=2025-01-01&sort_field=created_at&sort_direction=desc
```

### 2. Dedicated Advanced Search
**POST** `/api/v1/projects/search`

More comprehensive search with validation:

**Request Body:**
```json
{
    "project_number": "PRJ-2025",
    "project_name": "Website",
    "customer_name": "Ahmed",
    "status": "open",
    "project_manager_name": "John",
    "exact_date": "2025-01-15",
    "date_from": "2025-01-01",
    "date_to": "2025-12-31",
    "general_search": "development",
    "sort_field": "name",
    "sort_direction": "asc",
    "per_page": 20
}
```

---

## 🎯 Dynamic Field Selection & Filtering

### 3. Filter Projects by Specific Field
**GET** `/api/v1/projects/filter/by-field`

Filter projects based on any field value:

**Query Parameters:**
```
field: Field name to filter by (required)
value: Value to filter by (required)
sort_field: Field to sort results
sort_direction: asc/desc
per_page: Results per page
```

**Supported Fields:**
- `status`, `customer_name`, `project_manager_name`
- `country_id`, `currency_id`, `manager_id`, `customer_id`
- `project_number`, `name`, `start_date`, `end_date`, `project_date`

**Example:**
```
GET /api/v1/projects/filter/by-field?field=status&value=open&sort_field=name&sort_direction=asc
```

### 4. Get Unique Field Values
**GET** `/api/v1/projects/fields/values`

Get all unique values for a specific field (useful for dropdowns):

**Query Parameters:**
```
field: Field name (required)
```

**Example:**
```
GET /api/v1/projects/fields/values?field=status
```

**Response:**
```json
{
    "success": true,
    "data": {
        "field": "status",
        "field_name": "Status",
        "values": ["draft", "open", "on-hold", "closed"]
    }
}
```

---

## 📊 Advanced Sorting Features

### 5. Get Sortable Fields
**GET** `/api/v1/projects/fields/sortable`

Returns all fields that can be used for sorting:

**Response:**
```json
{
    "success": true,
    "data": {
        "id": "ID",
        "code": "Project Code",
        "project_number": "Project Number",
        "name": "Project Name",
        "customer_name": "Customer Name",
        "project_manager_name": "Project Manager Name",
        "status": "Status",
        "project_value": "Project Value",
        "currency_price": "Currency Price",
        "start_date": "Start Date",
        "end_date": "End Date",
        "created_at": "Created Date"
    }
}
```

### 6. Sort Projects with First/Last Functionality
**POST** `/api/v1/projects/sort`

Sort projects with support for "first" and "last" options:

**Request Body:**
```json
{
    "sort_field": "name",
    "sort_direction": "first", // "first", "last", "asc", "desc"
    "per_page": 15
}
```

**Sort Direction Options:**
- `first` = ascending (A-Z, 1-9, oldest first)
- `last` = descending (Z-A, 9-1, newest first)
- `asc` = ascending
- `desc` = descending

---

## 📋 Enhanced Project Display

### 7. Comprehensive Project Details
**GET** `/api/v1/projects/{id}`

Now returns comprehensive project data including:

**Response includes:**
```json
{
    "success": true,
    "data": {
        // All project fields
        "id": 1,
        "code": "PRJ-2025-0001",
        "name": "Website Development",
        // ... all other fields
        
        // Calculated fields
        "calculated_fields": {
            "vat_amount": 1500.00,
            "total_price_with_vat": 11500.00,
            "days_remaining": 45,
            "project_duration_days": 150,
            "is_overdue": false,
            "completion_percentage": 75
        },
        
        // Project statistics
        "statistics": {
            "total_milestones": 5,
            "completed_milestones": 3,
            "total_tasks": 15,
            "completed_tasks": 10,
            "total_documents": 8,
            "total_risks": 2,
            "open_risks": 1
        },
        
        // All relationships loaded
        "customer": {...},
        "currency": {...},
        "manager": {...},
        "country": {...},
        "company": {...},
        "milestones": [...],
        "tasks": [...],
        "documents": [...],
        "financials": [...],
        "risks": [...]
    }
}
```

---

## 🗑️ Soft Delete Management

### 8. Soft Delete Project
**DELETE** `/api/v1/projects/{id}`

Soft deletes a project (can be restored):

**Business Rules:**
- Cannot delete closed projects
- Only company members can delete their projects
- Sets `deleted_by` field before deletion

### 9. Restore Deleted Project
**POST** `/api/v1/projects/{id}/restore`

Restores a soft-deleted project:

**Response:**
```json
{
    "success": true,
    "data": {...},
    "message": "Project restored successfully"
}
```

### 10. Permanently Delete Project
**DELETE** `/api/v1/projects/{id}/force-delete`

Permanently deletes a project (cannot be restored):

**Authorization:**
- Only administrators or project creators can force delete
- Company access validation

### 11. Get Trashed Projects
**GET** `/api/v1/projects/trashed/list`

Returns all soft-deleted projects:

**Query Parameters:**
```
per_page: Results per page (default: 15)
```

**Response:**
```json
{
    "success": true,
    "data": {
        "data": [...], // Trashed projects with relationships
        "current_page": 1,
        "per_page": 15,
        "total": 5
    }
}
```

---

## 🔧 Frontend Integration Examples

### Search Implementation
```javascript
// Advanced search
const searchProjects = async (criteria) => {
    const response = await fetch('/api/v1/projects/search', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(criteria)
    });
    return response.json();
};

// Usage
const results = await searchProjects({
    customer_name: 'Ahmed',
    status: 'open',
    date_from: '2025-01-01',
    sort_field: 'name',
    sort_direction: 'asc'
});
```

### Dynamic Field Filtering
```javascript
// Filter by field
const filterByField = async (field, value) => {
    const response = await fetch(
        `/api/v1/projects/filter/by-field?field=${field}&value=${value}`,
        {
            headers: { 'Authorization': `Bearer ${token}` }
        }
    );
    return response.json();
};

// Get field values for dropdown
const getFieldValues = async (field) => {
    const response = await fetch(
        `/api/v1/projects/fields/values?field=${field}`,
        {
            headers: { 'Authorization': `Bearer ${token}` }
        }
    );
    return response.json();
};
```

### Sorting with First/Last
```javascript
// Sort projects
const sortProjects = async (field, direction) => {
    const response = await fetch('/api/v1/projects/sort', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            sort_field: field,
            sort_direction: direction // 'first', 'last', 'asc', 'desc'
        })
    });
    return response.json();
};
```

### Soft Delete Management
```javascript
// Restore project
const restoreProject = async (projectId) => {
    const response = await fetch(`/api/v1/projects/${projectId}/restore`, {
        method: 'POST',
        headers: { 'Authorization': `Bearer ${token}` }
    });
    return response.json();
};

// Get trashed projects
const getTrashedProjects = async () => {
    const response = await fetch('/api/v1/projects/trashed/list', {
        headers: { 'Authorization': `Bearer ${token}` }
    });
    return response.json();
};
```

---

## 🔒 Security & Authorization

All endpoints include:
- **Authentication**: Sanctum token required
- **Company Access Control**: Users can only access their company's projects
- **Role-based Permissions**: Some operations require admin privileges
- **Input Validation**: All parameters are validated
- **SQL Injection Protection**: Using Eloquent ORM with parameter binding

## Error Handling

Consistent error responses:
```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field_name": ["Validation error message"]
    }
}
```

HTTP Status Codes:
- `200`: Success
- `400`: Bad Request (validation errors)
- `403`: Forbidden (authorization errors)
- `404`: Not Found
- `422`: Unprocessable Entity (business logic errors)
- `500`: Internal Server Error
