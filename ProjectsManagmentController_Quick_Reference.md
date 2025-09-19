# 🚀 ProjectsManagmentController - Quick Postman Reference

## 🔧 **SETUP FIRST:**

### **Environment Variables:**
```
base_url = http://127.0.0.1:8000
api_token = your_bearer_token_here
```

### **Headers (for all requests):**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {{api_token}}
```

---

## **📋 QUICK COPY-PASTE REQUESTS:**

### **1. 📝 CREATE PROJECT - POST**
**URL:** `{{base_url}}/api/projects`
**Body:**
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
  "name": "Website Development Project",
  "description": "Complete website redesign and development",
  "project_value": 50000.00,
  "manager_id": 1,
  "start_date": "2024-01-01",
  "end_date": "2024-06-30",
  "status": "open",
  "country_id": 1,
  "notes": "High priority project"
}
```

### **2. 🔄 UPDATE PROJECT - PUT**
**URL:** `{{base_url}}/api/projects/1`
**Body:**
```json
{
  "name": "Updated Project Name",
  "project_value": 60000.00,
  "status": "on-hold",
  "progress": 45.5,
  "budget": 55000.00,
  "actual_cost": 25000.00
}
```

### **3. 🔍 SEARCH PROJECTS - POST**
**URL:** `{{base_url}}/api/projects/search`
**Body:**
```json
{
  "project_name": "Website",
  "status": "active",
  "date_from": "2024-01-01",
  "date_to": "2024-12-31",
  "general_search": "development",
  "sort_by": "created_at",
  "sort_order": "desc",
  "per_page": 20
}
```

### **4. 🎯 GET BY FIELD - POST**
**URL:** `{{base_url}}/api/projects/by-field`
**Body:**
```json
{
  "field": "status",
  "value": "active",
  "per_page": 15
}
```

### **5. 📊 GET FIELD VALUES - POST**
**URL:** `{{base_url}}/api/projects/field-values`
**Body:**
```json
{
  "field": "status"
}
```

### **6. 🔢 SORT PROJECTS - POST**
**URL:** `{{base_url}}/api/projects/sort`
**Body:**
```json
{
  "sort_field": "created_at",
  "sort_direction": "desc",
  "per_page": 15
}
```

### **7. 💰 CALCULATE VAT - POST**
**URL:** `{{base_url}}/api/projects/calculate-vat`
**Body:**
```json
{
  "price": 1000.00,
  "company_id": 1,
  "include_vat": true
}
```

---

## **📋 GET REQUESTS (No Body Required):**

### **8. 📋 LIST PROJECTS - GET**
```
{{base_url}}/api/projects?status=active&per_page=15
```

### **9. 👁️ SHOW PROJECT - GET**
```
{{base_url}}/api/projects/1
```

### **10. 👥 GET CUSTOMERS - GET**
```
{{base_url}}/api/projects/customers
```

### **11. 👤 GET CUSTOMER DATA - GET**
```
{{base_url}}/api/projects/customers/1
```

### **12. 💱 GET CURRENCIES - GET**
```
{{base_url}}/api/projects/currencies
```

### **13. 👨‍💼 GET EMPLOYEES - GET**
```
{{base_url}}/api/projects/employees
```

### **14. 🌍 GET COUNTRIES - GET**
```
{{base_url}}/api/projects/countries
```

### **15. 📊 GET STATUSES - GET**
```
{{base_url}}/api/projects/statuses
```

### **16. 🔤 GET SORTABLE FIELDS - GET**
```
{{base_url}}/api/projects/sortable-fields
```

### **17. 🔢 GENERATE CODE - GET**
```
{{base_url}}/api/projects/generate-code
```

### **18. 🗂️ GET TRASHED - GET**
```
{{base_url}}/api/projects/trashed?per_page=15
```

---

## **🗑️ DELETE OPERATIONS:**

### **19. 🗑️ DELETE PROJECT - DELETE**
```
{{base_url}}/api/projects/1
```

### **20. ♻️ RESTORE PROJECT - POST**
```
{{base_url}}/api/projects/1/restore
```

### **21. 💀 FORCE DELETE - DELETE**
```
{{base_url}}/api/projects/1/force-delete
```

---

## **🎯 TESTING SEQUENCE:**

1. **Get Auth Token** → Set in environment
2. **Get Dropdown Data:**
   - GET `/api/projects/customers`
   - GET `/api/projects/currencies`
   - GET `/api/projects/countries`
   - GET `/api/projects/employees`
   - GET `/api/projects/statuses`

3. **Create Project:**
   - POST `/api/projects` with full body

4. **Test Operations:**
   - GET `/api/projects` (list)
   - GET `/api/projects/1` (show)
   - PUT `/api/projects/1` (update)
   - POST `/api/projects/search` (search)

5. **Test Utilities:**
   - POST `/api/projects/calculate-vat`
   - GET `/api/projects/generate-code`
   - POST `/api/projects/sort`

6. **Test Delete/Restore:**
   - DELETE `/api/projects/1` (soft delete)
   - GET `/api/projects/trashed` (view deleted)
   - POST `/api/projects/1/restore` (restore)
   - DELETE `/api/projects/1/force-delete` (permanent)

---

## **⚠️ IMPORTANT VALIDATION:**

### **Required for CREATE:**
- `company_id`, `branch_id`, `fiscal_year_id`, `cost_center_id`
- `customer_id`, `currency_id`, `currency_price`
- `name`, `project_value`, `manager_id`
- `start_date`, `end_date`, `status`, `country_id`

### **Status Values:**
- `draft`, `open`, `on-hold`, `cancelled`, `closed`

### **Sort Directions:**
- `asc`, `desc`, `first`, `last`

### **Date Format:**
- `YYYY-MM-DD` (e.g., `2024-01-15`)

### **Field Names for field-values:**
- `status`, `customer_name`, `project_manager_name`
- `country_id`, `currency_id`, `manager_id`, `customer_id`

---

## **🔄 COMMON ERRORS & FIXES:**

1. **401 Unauthorized** → Check Bearer token
2. **422 Validation Error** → Check required fields
3. **404 Not Found** → Check project ID exists
4. **403 Forbidden** → Check company permissions
5. **500 Server Error** → Check foreign key relationships

---

## **💡 PRO TIPS:**

1. **Use Variables:** Set project_id as environment variable
2. **Test Sequence:** Always get dropdown data first
3. **Save Responses:** Use Tests tab to save IDs
4. **Batch Testing:** Use Collection Runner
5. **Environment Switch:** Create dev/staging/prod environments
