# Scribe Documentation Generation Scripts

This directory contains PowerShell scripts for generating API documentation using Scribe for the Al-Yaseen ERP system.

## 📋 Available Scripts

### Individual Module Scripts

1. **`generate-customers-docs.ps1`** - Generate documentation for Customer Management module
2. **`generate-suppliers-docs.ps1`** - Generate documentation for Supplier Management module
3. **`generate-sales-docs.ps1`** - Generate documentation for Sales Management module
4. **`generate-purchases-docs.ps1`** - Generate documentation for Purchase Management module

### Complete Documentation Script

5. **`generate-all-docs.ps1`** - Generate complete documentation for ALL modules including:
   - Project Management
   - Inventory Management
   - Customer Management
   - Supplier Management
   - Sales Management
   - Purchase Management

## 🚀 Usage

### Running Scripts

From the project root directory, run any of the following commands:

```powershell
# Generate documentation for all modules (RECOMMENDED)
.\scripts\generate-all-docs.ps1

# Or generate for specific modules
.\scripts\generate-customers-docs.ps1
.\scripts\generate-suppliers-docs.ps1
.\scripts\generate-sales-docs.ps1
.\scripts\generate-purchases-docs.ps1
```

### Prerequisites

- PHP must be installed and available in PATH
- Laravel project must be properly configured
- Scribe package must be installed (`knuckleswtf/scribe`)

## 📦 Generated Documentation

After running any script, the following files will be generated:

```
public/docs/
├── index.html              # Interactive HTML documentation
├── collection.json         # Postman collection
└── openapi.yaml           # OpenAPI 3.0 specification
```

## 🔍 Viewing Documentation

### Option 1: Direct File Access
Open `public/docs/index.html` in your web browser

### Option 2: Laravel Server
```bash
php artisan serve
```
Then visit: `http://localhost:8000/docs`

## 📚 Module Coverage

### Customer Management Module
- **Customers**: Customer relationship management with comprehensive customer data

### Supplier Management Module
- **Suppliers**: Supplier management with vendor information and relationships

### Sales Management Module
- **Incoming Orders**: Sales order processing and tracking
- **Outgoing Offers**: Sales quotation and offer management
- **Outgoing Shipments**: Shipment tracking and delivery management
- **Services**: Service management and tracking
- **Return Invoices**: Sales return processing and refunds

### Purchase Management Module
- **Outgoing Orders**: Purchase order creation and management
- **Incoming Offers**: Supplier offer evaluation and acceptance
- **Incoming Shipments**: Receipt and inspection of purchased goods
- **Expenses**: Purchase expense tracking and reporting
- **Invoices**: Purchase invoice processing and payment
- **Return Invoices**: Purchase return processing
- **Reference Invoices**: Invoice referencing and reconciliation

### Project Management Module
- **Projects**: Complete project lifecycle management
- **Tasks**: Task management with assignments and tracking
- **Milestones**: Project milestone tracking
- **Resources**: Resource allocation and management
- **Documents**: Project document management
- **Project Financials**: Financial tracking and budget management
- **Project Risks**: Risk assessment and management

### Inventory Management Module
- **Inventory Items**: Complete inventory item management
- **Warehouses**: Multi-warehouse management
- **Stock Movements**: Stock transfer and movement tracking
- **Items**: Advanced item management
- **Units**: Unit of measure management
- **BOM Items**: Bill of Materials management
- **Barcode Types**: Barcode management
- **Item Types**: Item categorization
- **Manufacturing Formulas**: Production formula management

## 🔧 Troubleshooting

### Script Execution Policy Error
If you get an execution policy error, run:
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

### Artisan Not Found
Make sure you're running the script from the project root directory where the `artisan` file is located.

### Documentation Not Generating
1. Clear Laravel cache: `php artisan cache:clear`
2. Clear Scribe cache: `php artisan scribe:generate --force`
3. Check that all controllers have proper `@group` annotations

## 📝 Controller @group Annotations

All controllers in the following modules have been annotated with `@group` tags for Scribe:

### Customers Module
- ✅ `CustomerController` - `@group Customer Management / Customers`

### Suppliers Module
- ✅ `SupplierController` - `@group Supplier Management / Suppliers`

### Sales Module
- ✅ `IncomingOrderController` - `@group Sales Management / Incoming Orders`
- ✅ `OutgoingOfferController` - `@group Sales Management / Outgoing Offers`
- ✅ `OutgoingShipmentController` - `@group Sales Management / Outgoing Shipments`
- ✅ `ServiceController` - `@group Sales Management / Services`
- ✅ `ReturnInvoiceController` - `@group Sales Management / Return Invoices`

### Purchases Module
- ✅ `OutgoingOrderController` - `@group Purchase Management / Outgoing Orders`
- ✅ `IncomingOfferController` - `@group Purchase Management / Incoming Offers`
- ✅ `IncomingShipmentController` - `@group Purchase Management / Incoming Shipments`
- ✅ `ExpenseController` - `@group Purchase Management / Expenses`
- ✅ `InvoiceController` - `@group Purchase Management / Invoices`
- ✅ `ReturnInvoiceController` - `@group Purchase Management / Return Invoices`
- ✅ `PurchaseReferenceInvoiceController` - `@group Purchase Management / Reference Invoices`

## 🎯 Best Practices

1. **Always use `generate-all-docs.ps1`** for complete documentation
2. **Run documentation generation** after making changes to controllers or routes
3. **Commit generated documentation** to version control for team access
4. **Review the HTML output** to ensure all endpoints are properly documented
5. **Use Postman collection** for API testing and validation

## 📖 Additional Resources

- [Scribe Documentation](https://scribe.knuckles.wtf/)
- [Laravel Sanctum Authentication](https://laravel.com/docs/sanctum)
- [OpenAPI Specification](https://swagger.io/specification/)

## 🤝 Contributing

When adding new controllers or endpoints:

1. Add the `@group` annotation to the controller class
2. Add proper PHPDoc comments to all methods
3. Update the route configuration in `config/scribe.php`
4. Run `generate-all-docs.ps1` to regenerate documentation
5. Verify the documentation in the HTML output

---

**Last Updated**: 2025-09-30
**Scribe Version**: Latest
**Laravel Version**: 10.x

