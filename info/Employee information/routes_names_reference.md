# أسماء الـ Routes في نظام إدارة الموظفين

## 🎯 **Employee Routes (الموظفين)**

### **العمليات الأساسية**
- `api.employees.list` - GET `/api/v1/employees/list` - قائمة الموظفين
- `api.employees.create` - POST `/api/v1/employees/create` - إضافة موظف جديد
- `api.employees.show` - GET `/api/v1/employees/{employee}/show` - عرض موظف واحد
- `api.employees.update` - PUT `/api/v1/employees/{employee}/update` - تحديث موظف
- `api.employees.delete` - DELETE `/api/v1/employees/{employee}/delete` - حذف موظف

### **المساعدات**
- `api.employees.form-data` - GET `/api/v1/employees/form-data/get` - بيانات النماذج
- `api.employees.next-number` - GET `/api/v1/employees/next-number/generate` - رقم الموظف التالي

### **البحث المتقدم**
- `api.employees.search.advanced` - POST `/api/v1/employees/search/advanced` - البحث المتقدم
- `api.employees.search.quick` - POST `/api/v1/employees/search/quick` - البحث السريع
- `api.employees.search.form-data` - GET `/api/v1/employees/search/form-data` - بيانات البحث
- `api.employees.search.statistics` - GET `/api/v1/employees/search/statistics` - الإحصائيات
- `api.employees.search.export` - POST `/api/v1/employees/search/export` - تصدير البيانات

### **إدارة الحذف الآمن**
- `api.employees.deleted.list` - GET `/api/v1/employees/deleted/list` - الموظفون المحذوفون
- `api.employees.deleted.restore` - POST `/api/v1/employees/deleted/{employeeId}/restore` - استعادة موظف

### **أسعار العملات**
- `api.employees.currency-rates.live-rate` - POST `/api/v1/employees/currency-rates/live-rate` - سعر عملة واحدة
- `api.employees.currency-rates.live-rates` - POST `/api/v1/employees/currency-rates/live-rates` - أسعار متعددة
- `api.employees.currency-rates.update-rate` - PUT `/api/v1/employees/currency-rates/update-rate` - تحديث سعر

---

## 🏢 **Department Routes (الأقسام)**
- `api.departments.list` - GET `/api/v1/departments/list` - قائمة الأقسام
- `api.departments.create` - POST `/api/v1/departments/create` - إضافة قسم جديد
- `api.departments.update` - PUT `/api/v1/departments/{department}/update` - تحديث قسم
- `api.departments.delete` - DELETE `/api/v1/departments/{department}/delete` - حذف قسم

---

## 📋 **Leave Requests Routes (طلبات الإجازة)**
- `api.leave-requests.list` - GET `/api/v1/leave-requests/list` - قائمة طلبات الإجازة
- `api.leave-requests.create` - POST `/api/v1/leave-requests/create` - إضافة طلب إجازة
- `api.leave-requests.update` - PUT `/api/v1/leave-requests/{leaveRequest}/update` - تحديث طلب إجازة
- `api.leave-requests.delete` - DELETE `/api/v1/leave-requests/{leaveRequest}/delete` - حذف طلب إجازة

---

## ⏰ **Attendance Routes (الحضور)**
- `api.attendances.list` - GET `/api/v1/attendances/list` - قائمة سجلات الحضور
- `api.attendances.create` - POST `/api/v1/attendances/create` - إضافة سجل حضور
- `api.attendances.update` - PUT `/api/v1/attendances/{attendance}/update` - تحديث سجل حضور
- `api.attendances.delete` - DELETE `/api/v1/attendances/{attendance}/delete` - حذف سجل حضور

---

## 🔄 **Legacy Employee Routes (للتوافق مع النسخة القديمة)**
- `api.employees-legacy.list` - GET `/api/v1/employees-legacy/list` - قائمة الموظفين (قديم)
- `api.employees-legacy.create` - POST `/api/v1/employees-legacy/create` - إضافة موظف (قديم)
- `api.employees-legacy.update` - PUT `/api/v1/employees-legacy/{employee}/update` - تحديث موظف (قديم)
- `api.employees-legacy.delete` - DELETE `/api/v1/employees-legacy/{employee}/delete` - حذف موظف (قديم)

---

## 🎯 **كيفية استخدام أسماء الـ Routes**

### **في Laravel (PHP)**
```php
// توليد URL باستخدام اسم الـ route
$url = route('api.employees.index');
$url = route('api.employees.show', ['employee' => 1]);
$url = route('api.employees.search.advanced');

// في الـ Controllers
return redirect()->route('api.employees.index');
```

### **في JavaScript/Frontend**
```javascript
// يمكن استخدام الأسماء لتوليد URLs
const employeesUrl = '/api/v1/employees/'; // api.employees.index
const searchUrl = '/api/v1/employees/search/advanced'; // api.employees.search.advanced
```

### **في Postman/Testing**
```
Collection: Employee Management
├── Employees
│   ├── GET List Employees (api.employees.index)
│   ├── POST Create Employee (api.employees.store)
│   ├── GET Show Employee (api.employees.show)
│   ├── PUT Update Employee (api.employees.update)
│   └── DELETE Delete Employee (api.employees.destroy)
├── Search
│   ├── POST Advanced Search (api.employees.search.advanced)
│   ├── POST Quick Search (api.employees.search.quick)
│   └── GET Statistics (api.employees.search.statistics)
└── Currency Rates
    ├── POST Get Live Rate (api.employees.currency-rates.live-rate)
    └── PUT Update Rate (api.employees.currency-rates.update-rate)
```

---

## ✅ **فوائد استخدام أسماء الـ Routes**

1. **سهولة الصيانة**: تغيير URL دون تعديل الكود
2. **وضوح الكود**: أسماء واضحة تدل على الوظيفة
3. **تجنب الأخطاء**: عدم كتابة URLs يدوياً
4. **التوثيق**: أسماء تشرح نفسها
5. **التنظيم**: هيكل منطقي للـ routes

---

## 📊 **إحصائيات الـ Routes**

- **إجمالي routes الموظفين**: 22 route
- **إجمالي routes الأقسام**: 4 routes  
- **إجمالي routes الحضور**: 4 routes
- **إجمالي routes طلبات الإجازة**: 4 routes
- **المجموع الكلي**: 34 route

---

## 🔍 **البحث عن Routes**

```bash
# عرض جميع routes الموظفين
php artisan route:list --path=employees

# عرض جميع routes الأقسام
php artisan route:list --path=departments

# عرض جميع routes الحضور
php artisan route:list --path=attendances

# عرض route محدد
php artisan route:list --name=employees.index
```

