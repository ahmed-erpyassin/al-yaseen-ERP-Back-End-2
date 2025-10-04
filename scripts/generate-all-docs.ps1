# Generate Complete Scribe Documentation for All Modules
# This script generates API documentation for all ERP modules

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Generating Complete ERP Documentation" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Modules included:" -ForegroundColor Yellow
Write-Host "  - Project Management" -ForegroundColor White
Write-Host "  - Inventory Management" -ForegroundColor White
Write-Host "  - Customer Management" -ForegroundColor White
Write-Host "  - Supplier Management" -ForegroundColor White
Write-Host "  - Sales Management" -ForegroundColor White
Write-Host "  - Purchase Management" -ForegroundColor White
Write-Host ""

# Check if we're in the correct directory
if (-not (Test-Path "artisan")) {
    Write-Host "Error: artisan file not found. Please run this script from the project root directory." -ForegroundColor Red
    exit 1
}

Write-Host "Step 1: Clearing Scribe cache..." -ForegroundColor Yellow
php artisan cache:clear
Write-Host "✓ Cache cleared" -ForegroundColor Green
Write-Host ""

Write-Host "Step 2: Generating documentation..." -ForegroundColor Yellow
php artisan scribe:generate --force

if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Documentation generated successfully!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Documentation files created:" -ForegroundColor Cyan
    Write-Host "  - HTML: public/docs/index.html" -ForegroundColor White
    Write-Host "  - Postman Collection: public/docs/collection.json" -ForegroundColor White
    Write-Host "  - OpenAPI Spec: public/docs/openapi.yaml" -ForegroundColor White
    Write-Host ""
    
    # Count the number of endpoint groups
    if (Test-Path ".scribe/endpoints.cache") {
        $endpointFiles = Get-ChildItem -Path ".scribe/endpoints.cache" -Filter "*.yaml" | Measure-Object
        Write-Host "Total endpoint groups: $($endpointFiles.Count)" -ForegroundColor Cyan
    }
    
    Write-Host ""
    Write-Host "To view the documentation:" -ForegroundColor Green
    Write-Host "  1. Open: public/docs/index.html in your browser" -ForegroundColor White
    Write-Host "  2. Or run: php artisan serve and visit http://localhost:8000/docs" -ForegroundColor White
} else {
    Write-Host "✗ Documentation generation failed!" -ForegroundColor Red
    Write-Host "Please check the error messages above." -ForegroundColor Yellow
    exit 1
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Complete ERP Documentation Generated!" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

