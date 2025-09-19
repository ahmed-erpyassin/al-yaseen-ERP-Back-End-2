# 📋 ProjectsManagmentController - Complete API Body Requirements

## 🎯 **ALL ENDPOINTS WITH REQUEST BODY REQUIREMENTS**

---

## **1. 📝 CREATE PROJECT - `POST /api/projects`**

### **✅ Required Fields:**
```json
{
  "company_id": 1,                    // integer, exists in companies table
  "branch_id": 1,                     // integer, exists in branches table
  "fiscal_year_id": 1,                // integer, exists in fiscal_years table
  "cost_center_id": 1,                // integer, exists in cost_centers table
  "customer_id": 1,                   // integer, exists in customers table
  "currency_id": 1,                   // integer, exists in currencies table
  "currency_price": 1.50,             // numeric, min: 0
  "name": "Website Development",       // string, max: 255 characters
  "project_value": 50000.00,          // numeric, min: 0
  "manager_id": 1,                    // integer, exists in users table
  "start_date": "2024-01-01",         // date, must be today or later
  "end_date": "2024-06-30",           // date, must be after start_date
  "status": "active",                 // enum: draft, open, on-hold, cancelled, closed
  "country_id": 1                     // integer, exists in countries table
}
```

### **✅ Optional Fields:**
```json
{
  "include_vat": true,                        // boolean, default: false
  "project_number": "PRJ-001",               // string, max: 255, unique
  "description": "Complete website redesign", // string
  "project_manager_name": "John Doe",        // string, max: 255
  "notes": "Additional project notes",        // string
  "customer_name": "ABC Company",            // string, max: 255 (auto-filled)
  "customer_email": "contact@abc.com",       // email, max: 255 (auto-filled)
  "customer_phone": "+1234567890",           // string, max: 20 (auto-filled)
  "licensed_operator": "Jane Smith"          // string, max: 255 (auto-filled)
}
```

---

## **2. 🔄 UPDATE PROJECT - `PUT/PATCH /api/projects/{id}`**

### **✅ All Fields Optional (use 'sometimes' validation):**
```json
{
  "company_id": 1,                    // sometimes|exists:companies,id
  "branch_id": 1,                     // sometimes|exists:branches,id
  "fiscal_year_id": 1,                // sometimes|exists:fiscal_years,id
  "cost_center_id": 1,                // sometimes|exists:cost_centers,id
  "customer_id": 1,                   // sometimes|exists:customers,id
  "currency_id": 1,                   // sometimes|exists:currencies,id
  "currency_price": 1.50,             // sometimes|numeric|min:0
  "include_vat": true,                // sometimes|boolean
  "project_number": "PRJ-001",        // sometimes|string|max:255|unique (ignoring current)
  "name": "Updated Project Name",      // sometimes|string|max:255
  "description": "Updated description", // sometimes|string
  "project_value": 60000.00,          // sometimes|numeric|min:0
  "manager_id": 1,                    // sometimes|exists:users,id
  "project_manager_name": "John Doe", // sometimes|string|max:255
  "start_date": "2024-01-01",         // sometimes|date
  "end_date": "2024-06-30",           // sometimes|date|after:start_date
  "status": "on-hold",                // sometimes|in:draft,open,on-hold,cancelled,closed
  "country_id": 1,                    // sometimes|exists:countries,id
  "notes": "Updated notes",           // sometimes|string
  "customer_name": "ABC Company",     // sometimes|string|max:255
  "customer_email": "contact@abc.com", // sometimes|email|max:255
  "customer_phone": "+1234567890",    // sometimes|string|max:20
  "licensed_operator": "Jane Smith",  // sometimes|string|max:255
  "budget": 55000.00,                 // sometimes|numeric|min:0
  "actual_cost": 25000.00,            // sometimes|numeric|min:0
  "progress": 45.5                    // sometimes|numeric|min:0|max:100
}
```

---

## **3. 🔍 SEARCH PROJECTS - `POST /api/projects/search`**

### **✅ All Fields Optional:**
```json
{
  "project_number": "PRJ-001",           // nullable|string|max:255
  "project_name": "Website",             // nullable|string|max:255
  "customer_name": "ABC Company",        // nullable|string|max:255
  "status": "active",                    // nullable|string|in:draft,open,on-hold,cancelled,closed
  "project_manager_name": "John Doe",    // nullable|string|max:255
  "exact_date": "2024-01-15",            // nullable|date
  "date_from": "2024-01-01",             // nullable|date
  "date_to": "2024-12-31",               // nullable|date|after_or_equal:date_from
  "start_date_from": "2024-01-01",       // nullable|date
  "start_date_to": "2024-12-31",         // nullable|date|after_or_equal:start_date_from
  "end_date_from": "2024-01-01",         // nullable|date
  "end_date_to": "2024-12-31",           // nullable|date|after_or_equal:end_date_from
  "general_search": "development",        // nullable|string|max:255
  "sort_by": "created_at",               // nullable|string
  "sort_order": "desc",                  // nullable|in:asc,desc
  "per_page": 20                         // nullable|integer|min:1|max:100
}
```

---

## **4. 🎯 GET PROJECTS BY FIELD - `POST /api/projects/by-field`**

### **✅ Required Fields:**
```json
{
  "field": "status",                     // required|string
  "value": "active"                      // required (any type)
}
```

### **✅ Optional Fields:**
```json
{
  "per_page": 15,                        // nullable|integer|min:1|max:100
  "sort_field": "created_at",            // nullable|string
  "sort_direction": "desc"               // nullable|in:asc,desc
}
```

---

## **5. 📊 GET FIELD VALUES - `POST /api/projects/field-values`**

### **✅ Required Fields:**
```json
{
  "field": "status"                      // required|string
}
```

### **✅ Allowed Field Values:**
- `status`
- `customer_name`
- `project_manager_name`
- `country_id`
- `currency_id`
- `manager_id`
- `customer_id`

---

## **6. 🔢 SORT PROJECTS - `POST /api/projects/sort`**

### **✅ Required Fields:**
```json
{
  "sort_field": "created_at",            // required|string
  "sort_direction": "desc"               // required|in:asc,desc,first,last
}
```

### **✅ Optional Fields:**
```json
{
  "per_page": 15                         // nullable|integer|min:1|max:100
}
```

### **✅ Available Sort Fields:**
- `id`, `code`, `project_number`, `name`, `customer_name`
- `project_manager_name`, `status`, `project_value`, `currency_price`
- `budget`, `actual_cost`, `progress`, `start_date`, `end_date`
- `project_date`, `created_at`, `updated_at`

---

## **7. 💰 CALCULATE VAT - `POST /api/projects/calculate-vat`**

### **✅ Required Fields:**
```json
{
  "price": 1000.00,                      // required|numeric|min:0
  "company_id": 1                        // required|exists:companies,id
}
```

### **✅ Optional Fields:**
```json
{
  "include_vat": true                    // boolean, default: false
}
```

---

## **8. 📋 LIST PROJECTS - `GET /api/projects` (Query Parameters)**

### **✅ All Query Parameters Optional:**
```
?project_number=PRJ-001
&project_name=Website Development
&customer_name=ABC Company
&status=active
&project_manager_name=John Doe
&exact_date=2024-01-15
&date_from=2024-01-01
&date_to=2024-12-31
&start_date_from=2024-01-01
&start_date_to=2024-12-31
&end_date_from=2024-01-01
&end_date_to=2024-12-31
&general_search=development
&sort_field=created_at
&sort_direction=desc
&per_page=20
```

---

## **9. 🗑️ GET TRASHED PROJECTS - `GET /api/projects/trashed` (Query Parameters)**

### **✅ Optional Query Parameters:**
```
?per_page=15
```

---

## **🎯 VALIDATION RULES SUMMARY:**

### **✅ Status Values:**
- `draft`, `open`, `on-hold`, `cancelled`, `closed`

### **✅ Sort Directions:**
- `asc`, `desc`, `first`, `last`

### **✅ Date Format:**
- All dates must be in `Y-m-d` format (e.g., `2024-01-15`)

### **✅ Numeric Fields:**
- All numeric fields must be `>= 0`
- Progress must be between `0` and `100`

### **✅ String Limits:**
- Most string fields: `max:255`
- Email fields: `max:255` + valid email format
- Phone fields: `max:20`
- Description/Notes: unlimited length

### **✅ Foreign Key Requirements:**
- All `_id` fields must exist in their respective tables
- Company-specific data is automatically filtered by user's company

---

## **🔒 AUTHENTICATION:**
All endpoints require authentication via Bearer token or session authentication.

## **🏢 COMPANY FILTERING:**
All data is automatically filtered by the authenticated user's company_id.

## **📝 NOTES:**
- Auto-populated fields (customer_name, customer_email, etc.) are filled automatically when customer_id is provided
- Project numbers are auto-generated if not provided
- VAT calculations are based on company settings
- Soft delete is used for project deletion
