# Generate Scribe Documentation for Sales Module
# This script generates API documentation for the Sales Management module

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Generating Sales Module Documentation" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if we're in the correct directory
if (-not (Test-Path "artisan")) {
    Write-Host "Error: artisan file not found. Please run this script from the project root directory." -ForegroundColor Red
    exit 1
}

Write-Host "Step 1: Clearing Scribe cache..." -ForegroundColor Yellow
php artisan scribe:generate --force

if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Documentation generated successfully!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Documentation files created:" -ForegroundColor Cyan
    Write-Host "  - HTML: public/docs/index.html" -ForegroundColor White
    Write-Host "  - Postman Collection: public/docs/collection.json" -ForegroundColor White
    Write-Host "  - OpenAPI Spec: public/docs/openapi.yaml" -ForegroundColor White
    Write-Host ""
    Write-Host "To view the documentation, open: public/docs/index.html" -ForegroundColor Green
} else {
    Write-Host "✗ Documentation generation failed!" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Sales Module Documentation Complete!" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

