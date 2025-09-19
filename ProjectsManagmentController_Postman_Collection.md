# 🚀 ProjectsManagmentController - Complete Postman Collection

## 📋 **POSTMAN SETUP REQUIREMENTS**

### **🔑 Authentication Setup:**
```
Authorization: Bearer Token
Token: your_api_token_here
```

### **📝 Headers (for all requests):**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer your_token_here
```

---

## **1. 📋 LIST PROJECTS - `GET`**

### **URL:**
```
{{base_url}}/api/projects
```

### **Method:** `GET`

### **Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer your_token_here
```

### **Query Parameters (all optional):**
```
project_number: PRJ-001
project_name: Website Development
customer_name: ABC Company
status: active
project_manager_name: John Doe
exact_date: 2024-01-15
date_from: 2024-01-01
date_to: 2024-12-31
start_date_from: 2024-01-01
start_date_to: 2024-12-31
end_date_from: 2024-01-01
end_date_to: 2024-12-31
general_search: development
sort_field: created_at
sort_direction: desc
per_page: 20
```

### **Full URL Example:**
```
{{base_url}}/api/projects?project_name=Website&status=active&per_page=15&sort_direction=desc
```

---

## **2. 📝 CREATE PROJECT - `POST`**

### **URL:**
```
{{base_url}}/api/projects
```

### **Method:** `POST`

### **Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer your_token_here
```

### **Body (JSON):**
```json
{
  "company_id": 1,
  "branch_id": 1,
  "fiscal_year_id": 1,
  "cost_center_id": 1,
  "customer_id": 1,
  "currency_id": 1,
  "currency_price": 1.50,
  "include_vat": true,
  "project_number": "PRJ-001",
  "name": "Website Development Project",
  "description": "Complete website redesign and development with modern UI/UX",
  "project_value": 50000.00,
  "manager_id": 1,
  "project_manager_name": "John Doe",
  "start_date": "2024-01-01",
  "end_date": "2024-06-30",
  "status": "open",
  "country_id": 1,
  "notes": "High priority project with tight deadline",
  "customer_name": "ABC Company Ltd",
  "customer_email": "contact@abccompany.com",
  "customer_phone": "+1234567890",
  "licensed_operator": "Jane Smith"
}
```

---

## **3. 👁️ SHOW PROJECT - `GET`**

### **URL:**
```
{{base_url}}/api/projects/1
```

### **Method:** `GET`

### **Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer your_token_here
```

### **Path Parameters:**
```
id: 1 (Project ID)
```

---

## **4. 🔄 UPDATE PROJECT - `PUT`**

### **URL:**
```
{{base_url}}/api/projects/1
```

### **Method:** `PUT`

### **Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer your_token_here
```

### **Body (JSON) - All fields optional:**
```json
{
  "name": "Updated Website Development Project",
  "description": "Updated project description with new requirements",
  "project_value": 60000.00,
  "status": "on-hold",
  "end_date": "2024-08-30",
  "budget": 55000.00,
  "actual_cost": 25000.00,
  "progress": 45.5,
  "notes": "Project temporarily on hold due to client requirements change",
  "manager_id": 2,
  "project_manager_name": "Sarah Johnson"
}
```

---

## **5. 🔍 SEARCH PROJECTS - `POST`**

### **URL:**
```
{{base_url}}/api/projects/search
```

### **Method:** `POST`

### **Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer your_token_here
```

### **Body (JSON) - All fields optional:**
```json
{
  "project_number": "PRJ",
  "project_name": "Website",
  "customer_name": "ABC",
  "status": "active",
  "project_manager_name": "John",
  "exact_date": "2024-01-15",
  "date_from": "2024-01-01",
  "date_to": "2024-12-31",
  "start_date_from": "2024-01-01",
  "start_date_to": "2024-12-31",
  "end_date_from": "2024-01-01",
  "end_date_to": "2024-12-31",
  "general_search": "development",
  "sort_by": "created_at",
  "sort_order": "desc",
  "per_page": 20
}
```

---

## **6. 🎯 GET PROJECTS BY FIELD - `POST`**

### **URL:**
```
{{base_url}}/api/projects/by-field
```

### **Method:** `POST`

### **Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer your_token_here
```

### **Body (JSON):**
```json
{
  "field": "status",
  "value": "active",
  "per_page": 15,
  "sort_field": "created_at",
  "sort_direction": "desc"
}
```

### **Alternative Examples:**
```json
{
  "field": "customer_name",
  "value": "ABC Company",
  "per_page": 10
}
```

```json
{
  "field": "project_manager_name",
  "value": "John Doe",
  "sort_field": "project_value",
  "sort_direction": "asc"
}
```

---

## **7. 📊 GET FIELD VALUES - `POST`**

### **URL:**
```
{{base_url}}/api/projects/field-values
```

### **Method:** `POST`

### **Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer your_token_here
```

### **Body (JSON):**
```json
{
  "field": "status"
}
```

### **Alternative Field Examples:**
```json
{"field": "customer_name"}
{"field": "project_manager_name"}
{"field": "country_id"}
{"field": "currency_id"}
{"field": "manager_id"}
{"field": "customer_id"}
```

---

## **8. 🔢 SORT PROJECTS - `POST`**

### **URL:**
```
{{base_url}}/api/projects/sort
```

### **Method:** `POST`

### **Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer your_token_here
```

### **Body (JSON):**
```json
{
  "sort_field": "created_at",
  "sort_direction": "desc",
  "per_page": 15
}
```

### **Alternative Sort Examples:**
```json
{
  "sort_field": "project_value",
  "sort_direction": "first",
  "per_page": 20
}
```

```json
{
  "sort_field": "name",
  "sort_direction": "asc"
}
```

---

## **9. 💰 CALCULATE VAT - `POST`**

### **URL:**
```
{{base_url}}/api/projects/calculate-vat
```

### **Method:** `POST`

### **Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer your_token_here
```

### **Body (JSON):**
```json
{
  "price": 1000.00,
  "company_id": 1,
  "include_vat": true
}
```

### **Alternative Examples:**
```json
{
  "price": 5000.50,
  "company_id": 1,
  "include_vat": false
}
```

---

## **10. 👥 GET CUSTOMERS - `GET`**

### **URL:**
```
{{base_url}}/api/projects/customers
```

### **Method:** `GET`

### **Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer your_token_here
```

---

## **11. 👤 GET CUSTOMER DATA - `GET`**

### **URL:**
```
{{base_url}}/api/projects/customers/1
```

### **Method:** `GET`

### **Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer your_token_here
```

### **Path Parameters:**
```
customerId: 1
```

---

## **12. 💱 GET CURRENCIES - `GET`**

### **URL:**
```
{{base_url}}/api/projects/currencies
```

### **Method:** `GET`

### **Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer your_token_here
```

---

## **13. 👨‍💼 GET EMPLOYEES - `GET`**

### **URL:**
```
{{base_url}}/api/projects/employees
```

### **Method:** `GET`

### **Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer your_token_here
```

---

## **14. 🌍 GET COUNTRIES - `GET`**

### **URL:**
```
{{base_url}}/api/projects/countries
```

### **Method:** `GET`

### **Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer your_token_here
```

---

## **15. 📊 GET PROJECT STATUSES - `GET`**

### **URL:**
```
{{base_url}}/api/projects/statuses
```

### **Method:** `GET`

### **Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer your_token_here
```

---

## **16. 🔤 GET SORTABLE FIELDS - `GET`**

### **URL:**
```
{{base_url}}/api/projects/sortable-fields
```

### **Method:** `GET`

### **Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer your_token_here
```

---

## **17. 🔢 GENERATE PROJECT CODE - `GET`**

### **URL:**
```
{{base_url}}/api/projects/generate-code
```

### **Method:** `GET`

### **Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer your_token_here
```

---

## **18. 🗑️ DELETE PROJECT - `DELETE`**

### **URL:**
```
{{base_url}}/api/projects/1
```

### **Method:** `DELETE`

### **Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer your_token_here
```

### **Path Parameters:**
```
id: 1 (Project ID)
```

---

## **19. ♻️ RESTORE PROJECT - `POST`**

### **URL:**
```
{{base_url}}/api/projects/1/restore
```

### **Method:** `POST`

### **Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer your_token_here
```

### **Path Parameters:**
```
id: 1 (Project ID)
```

---

## **20. 💀 FORCE DELETE PROJECT - `DELETE`**

### **URL:**
```
{{base_url}}/api/projects/1/force-delete
```

### **Method:** `DELETE`

### **Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer your_token_here
```

### **Path Parameters:**
```
id: 1 (Project ID)
```

---

## **21. 🗂️ GET TRASHED PROJECTS - `GET`**

### **URL:**
```
{{base_url}}/api/projects/trashed
```

### **Method:** `GET`

### **Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer your_token_here
```

### **Query Parameters (optional):**
```
per_page: 15
```

---

## **🔧 POSTMAN ENVIRONMENT VARIABLES:**

### **Set these variables in your Postman environment:**
```
base_url: http://127.0.0.1:8000
api_token: your_bearer_token_here
```

### **Usage in requests:**
- URL: `{{base_url}}/api/projects`
- Authorization: `Bearer {{api_token}}`

---

## **📝 IMPORTANT NOTES:**

1. **Authentication Required:** All endpoints require Bearer token
2. **Company Filtering:** Data automatically filtered by user's company
3. **Date Format:** Use `YYYY-MM-DD` format for all dates
4. **Status Values:** `draft`, `open`, `on-hold`, `cancelled`, `closed`
5. **Sort Directions:** `asc`, `desc`, `first`, `last`
6. **Numeric Fields:** Must be >= 0, progress 0-100%
7. **Foreign Keys:** Must exist in respective tables

---

## **🎯 TESTING SEQUENCE:**

1. **Setup:** Get authentication token
2. **Get Data:** Fetch customers, currencies, countries, employees
3. **Create:** Create new project with required fields
4. **Read:** Get project details, list projects
5. **Update:** Modify project data
6. **Search:** Test various search criteria
7. **Delete:** Soft delete and restore
8. **Cleanup:** Force delete if needed
