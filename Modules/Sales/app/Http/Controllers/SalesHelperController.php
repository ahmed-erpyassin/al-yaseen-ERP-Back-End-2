<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Customers\Models\Customer;
use Modules\FinancialAccounts\Models\Currency;
use Modules\FinancialAccounts\Models\ExchangeRate;
use Modules\FinancialAccounts\Models\TaxRate;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\Unit;
use Modules\Companies\Models\Company;



/**
 * @group Sales Management / Helpers
 *
 * Helper APIs for sales operations including customer lookup, item search, currency rates, and form data preparation.
 */
class SalesHelperController extends Controller
{
    /**
     * Get customers for dropdown
     *
     * Retrieve a list of active customers for dropdown/autocomplete fields with optional search functionality.
     *
     * @queryParam search string Optional search term to filter customers by name, code, or email. Example: John
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "customer_number": "CUST-001",
     *       "customer_name": "John Doe",
     *       "email": "john@example.com",
     *       "licensed_operator": "Operator A",
     *       "phone": "+1234567890",
     *       "mobile": "+0987654321"
     *     }
     *   ]
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "message": "Error fetching customers: Database connection failed"
     * }
     */
    public function getCustomers(Request $request)
    {
        try {
            $query = Customer::query()
                ->where('company_id', $request->user()->company_id ?? 101)
                ->where('status', 'active');

            // Search functionality
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $customers = $query->select([
                'id',
                'code as customer_number',
                'name as customer_name',
                'email',
                'licensed_operator',
                'phone',
                'mobile'
            ])->get();

            return response()->json([
                'success' => true,
                'data' => $customers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching customers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get currencies with exchange rates
     *
     * Retrieve all available currencies with their current exchange rates for the company.
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "code": "USD",
     *       "name": "US Dollar",
     *       "symbol": "$",
     *       "exchange_rate": 1.0
     *     },
     *     {
     *       "id": 2,
     *       "code": "EUR",
     *       "name": "Euro",
     *       "symbol": "€",
     *       "exchange_rate": 0.85
     *     }
     *   ]
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "message": "Error fetching currencies: Database error"
     * }
     */
    public function getCurrencies(Request $request)
    {
        try {
            $currencies = Currency::where('company_id', $request->user()->company_id ?? 101)
                ->select(['id', 'code', 'name', 'symbol'])
                ->get();

            // Add exchange rates
            $currencies->each(function ($currency) {
                $exchangeRate = ExchangeRate::where('currency_id', $currency->id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                $currency->exchange_rate = $exchangeRate ? $exchangeRate->rate : 1.0;
            });

            return response()->json([
                'success' => true,
                'data' => $currencies
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching currencies: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get items for dropdown with autocomplete
     *
     * Retrieve active inventory items for dropdown/autocomplete fields with optional search functionality.
     * Limited to 50 items for performance.
     *
     * @queryParam search string Optional search term to filter items by name, item number, or code. Example: Laptop
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "item_number": "ITEM-001",
     *       "name": "Laptop Dell XPS 15",
     *       "description": "High-performance laptop",
     *       "unit_id": 1,
     *       "first_sale_price": 1200.00,
     *       "second_sale_price": 1150.00,
     *       "third_sale_price": 1100.00,
     *       "unit": {
     *         "id": 1,
     *         "name": "Piece",
     *         "symbol": "pc"
     *       }
     *     }
     *   ]
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "message": "Error fetching items: Database error"
     * }
     */
    public function getItems(Request $request)
    {
        try {
            $query = Item::query()
                ->where('company_id', $request->user()->company_id ?? 101)
                ->where('active', true);

            // Search functionality
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('item_number', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            }

            $items = $query->with('unit:id,name,symbol')
                ->select([
                    'id',
                    'item_number',
                    'name',
                    'description',
                    'unit_id',
                    'first_sale_price',
                    'second_sale_price',
                    'third_sale_price'
                ])
                ->limit(50) // Limit for performance
                ->get();

            return response()->json([
                'success' => true,
                'data' => $items
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching items: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get units for dropdown
     *
     * Retrieve all active measurement units for dropdown fields.
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Piece",
     *       "code": "PC",
     *       "symbol": "pc"
     *     },
     *     {
     *       "id": 2,
     *       "name": "Kilogram",
     *       "code": "KG",
     *       "symbol": "kg"
     *     }
     *   ]
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "message": "Error fetching units: Database error"
     * }
     */
    public function getUnits(Request $request)
    {
        try {
            $units = Unit::where('company_id', $request->user()->company_id ?? 101)
                ->where('status', 'active')
                ->select(['id', 'name', 'code', 'symbol'])
                ->get();

            return response()->json([
                'success' => true,
                'data' => $units
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching units: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tax rates for dropdown
     *
     * Retrieve all tax rates configured for the company.
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Standard VAT",
     *       "code": "VAT",
     *       "rate": 15.0,
     *       "type": "vat"
     *     },
     *     {
     *       "id": 2,
     *       "name": "Zero Rated",
     *       "code": "ZERO",
     *       "rate": 0.0,
     *       "type": "vat"
     *     }
     *   ]
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "message": "Error fetching tax rates: Database error"
     * }
     */
    public function getTaxRates(Request $request)
    {
        try {
            $taxRates = TaxRate::where('company_id', $request->user()->company_id ?? 101)
                ->select(['id', 'name', 'code', 'rate', 'type'])
                ->get();

            return response()->json([
                'success' => true,
                'data' => $taxRates
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching tax rates: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get company VAT rate
     *
     * Retrieve the company's configured VAT and income tax rates.
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "vat_rate": 15.0,
     *     "income_tax_rate": 5.0
     *   }
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "message": "Error fetching company VAT rate: Database error"
     * }
     */
    public function getCompanyVatRate(Request $request)
    {
        try {
            $company = Company::find($request->user()->company_id ?? 101);

            return response()->json([
                'success' => true,
                'data' => [
                    'vat_rate' => $company ? $company->vat_rate : 0,
                    'income_tax_rate' => $company ? $company->income_tax_rate : 0
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching company VAT rate: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get exchange rate for specific currency
     *
     * Retrieve the latest exchange rate for a specific currency.
     *
     * @urlParam currencyId integer required The ID of the currency. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "currency_id": 1,
     *     "exchange_rate": 1.0,
     *     "updated_at": "2025-09-30T10:30:00.000000Z"
     *   }
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "message": "Error fetching currency rate: Database error"
     * }
     */
    public function getCurrencyRate(Request $request, $currencyId)
    {
        try {
            $exchangeRate = ExchangeRate::where('currency_id', $currencyId)
                ->orderBy('created_at', 'desc')
                ->first();

            $rate = $exchangeRate ? $exchangeRate->rate : 1.0;

            return response()->json([
                'success' => true,
                'data' => [
                    'currency_id' => $currencyId,
                    'exchange_rate' => $rate,
                    'updated_at' => $exchangeRate ? $exchangeRate->created_at : null
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching currency rate: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get item details by ID
     *
     * Retrieve detailed information about a specific item including unit and pricing.
     *
     * @urlParam itemId integer required The ID of the item. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "item_number": "ITEM-001",
     *     "name": "Laptop Dell XPS 15",
     *     "description": "High-performance laptop",
     *     "unit_id": 1,
     *     "first_sale_price": 1200.00,
     *     "second_sale_price": 1150.00,
     *     "third_sale_price": 1100.00,
     *     "unit": {
     *       "id": 1,
     *       "name": "Piece",
     *       "symbol": "pc"
     *     }
     *   }
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "message": "Item not found"
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "message": "Error fetching item details: Database error"
     * }
     */
    public function getItemDetails(Request $request, $itemId)
    {
        try {
            $item = Item::with('unit:id,name,symbol')
                ->where('id', $itemId)
                ->where('company_id', $request->user()->company_id ?? 101)
                ->select([
                    'id',
                    'item_number',
                    'name',
                    'description',
                    'unit_id',
                    'first_sale_price',
                    'second_sale_price',
                    'third_sale_price'
                ])
                ->first();

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $item
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching item details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get live currency rate with tax calculation
     *
     * Fetch real-time exchange rate from external API with optional tax rates.
     * Uses exchangerate-api.com for live rates.
     *
     * @urlParam currencyId integer required The ID of the currency. Example: 2
     * @queryParam include_tax boolean Optional flag to include tax rates in response. Example: true
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "currency_id": 2,
     *     "currency_code": "EUR",
     *     "currency_name": "Euro",
     *     "exchange_rate": 0.85,
     *     "tax_rates": [
     *       {
     *         "id": 1,
     *         "name": "Standard VAT",
     *         "code": "VAT",
     *         "rate": 15.0
     *       }
     *     ],
     *     "updated_at": "2025-09-30T10:30:00.000000Z"
     *   }
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "error": "Currency not found."
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "error": "An error occurred while fetching live currency rate.",
     *   "message": "API timeout"
     * }
     */
    public function getLiveCurrencyRateWithTax(Request $request, $currencyId)
    {
        try {
            $currency = Currency::where('id', $currencyId)
                ->where('company_id', $request->user()->company_id ?? 101)
                ->first();

            if (!$currency) {
                return response()->json([
                    'success' => false,
                    'error' => 'Currency not found.'
                ], 404);
            }

            // Get live rate from external API
            $liveRate = $this->fetchLiveExchangeRate($currency->code);

            // Get tax rates if tax is applicable
            $taxRates = [];
            if ($request->get('include_tax', false)) {
                $taxRates = TaxRate::where('company_id', $request->user()->company_id ?? 101)
                    ->where('type', 'vat')
                    ->select(['id', 'name', 'code', 'rate'])
                    ->get();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'currency_id' => $currencyId,
                    'currency_code' => $currency->code,
                    'currency_name' => $currency->name,
                    'exchange_rate' => $liveRate,
                    'tax_rates' => $taxRates,
                    'updated_at' => now()
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching live currency rate.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch live exchange rate from external API
     */
    private function fetchLiveExchangeRate($currencyCode)
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->get("https://api.exchangerate-api.com/v4/latest/USD");

            if ($response->successful()) {
                $rates = $response->json()['rates'] ?? [];
                return $rates[$currencyCode] ?? 1.0;
            }
        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Illuminate\Support\Facades\Log::warning('Failed to fetch live exchange rate: ' . $e->getMessage());
        }

        return 1.0; // Default rate
    }

    /**
     * Search customers for invoice creation
     *
     * Search customers by number, name, or email for invoice/order creation with result limit.
     *
     * @queryParam search string Optional search term to filter customers. Example: John
     * @queryParam limit integer Optional maximum number of results (default: 10). Example: 20
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "customer_number": "CUST-001",
     *       "name": "John Doe",
     *       "email": "john@example.com",
     *       "phone": "+1234567890",
     *       "licensed_operator": "Operator A"
     *     }
     *   ]
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "message": "Error searching customers: Database error"
     * }
     */
    public function searchCustomersForInvoice(Request $request)
    {
        try {
            $search = $request->get('search', '');
            $limit = $request->get('limit', 10);

            $customers = Customer::where('company_id', $request->user()->company_id ?? 101)
                ->where('status', 'active')
                ->where(function ($query) use ($search) {
                    if ($search) {
                        $query->where('customer_number', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    }
                })
                ->select([
                    'id',
                    'customer_number',
                    'name',
                    'email',
                    'phone',
                    'licensed_operator'
                ])
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $customers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching customers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search items for invoice creation
     *
     * Search items by number or name for invoice/order creation with formatted response including pricing.
     *
     * @queryParam search string Optional search term to filter items by number or name. Example: Laptop
     * @queryParam limit integer Optional maximum number of results (default: 10). Example: 20
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "item_number": "ITEM-001",
     *       "item_name": "Laptop Dell XPS 15",
     *       "unit": "Piece",
     *       "unit_price": 1200.00,
     *       "category": "Electronics",
     *       "supplier": "Dell Inc."
     *     }
     *   ]
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "message": "Error searching items: Database error"
     * }
     */
    public function searchItemsForInvoice(Request $request)
    {
        try {
            $search = $request->get('search', '');
            $limit = $request->get('limit', 10);

            $items = Item::where('company_id', $request->user()->company_id ?? 101)
                ->where('active', true)
                ->where(function ($query) use ($search) {
                    if ($search) {
                        $query->where('item_number', 'like', "%{$search}%")
                            ->orWhere('item_name_en', 'like', "%{$search}%")
                            ->orWhere('item_name_ar', 'like', "%{$search}%");
                    }
                })
                ->with(['category', 'supplier'])
                ->select([
                    'id',
                    'item_number',
                    'item_name_en',
                    'item_name_ar',
                    'unit',
                    'first_sale_price',
                    'second_sale_price',
                    'third_sale_price',
                    'category_id',
                    'supplier_id'
                ])
                ->limit($limit)
                ->get();

            // Format the response
            $formattedItems = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_number' => $item->item_number,
                    'item_name' => $item->item_name_en ?? $item->item_name_ar,
                    'unit' => $item->unit,
                    'unit_price' => $item->first_sale_price,
                    'category' => $item->category ? $item->category->name : null,
                    'supplier' => $item->supplier ? $item->supplier->supplier_name_en : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedItems
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching items: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get licensed operators for dropdown
     *
     * Retrieve a list of unique licensed operators from the customers database for dropdown selection.
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     "Operator A",
     *     "Operator B",
     *     "Operator C"
     *   ]
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "message": "Error fetching licensed operators: Database error"
     * }
     */
    public function getLicensedOperators(Request $request)
    {
        try {
            // Get unique licensed operators from customers table
            $operators = Customer::where('company_id', $request->user()->company_id ?? 101)
                ->whereNotNull('licensed_operator')
                ->where('licensed_operator', '!=', '')
                ->distinct()
                ->pluck('licensed_operator')
                ->filter()
                ->values();

            return response()->json([
                'success' => true,
                'data' => $operators
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching licensed operators: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customer details by ID
     *
     * Retrieve complete customer information by ID for auto-population in forms.
     *
     * @urlParam customerId integer required The ID of the customer. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "customer_number": "CUST-001",
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "phone": "+1234567890",
     *     "licensed_operator": "Operator A",
     *     "address_one": "123 Main St",
     *     "address_two": "Suite 100"
     *   }
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "error": "Customer not found."
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "message": "Error fetching customer details: Database error"
     * }
     */
    public function getCustomerDetails(Request $request, $customerId)
    {
        try {
            $customer = Customer::where('id', $customerId)
                ->where('company_id', $request->user()->company_id ?? 101)
                ->select([
                    'id',
                    'customer_number',
                    'name',
                    'email',
                    'phone',
                    'licensed_operator',
                    'address_one',
                    'address_two'
                ])
                ->first();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'error' => 'Customer not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $customer
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching customer details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get item details for invoice creation
     *
     * Retrieve complete item information including pricing, units, category, and supplier for invoice/order creation.
     *
     * @urlParam itemId integer required The ID of the item. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "item_number": "ITEM-001",
     *     "item_name": "Laptop Dell XPS 15",
     *     "unit": "Piece",
     *     "first_sale_price": 1200.00,
     *     "second_sale_price": 1150.00,
     *     "third_sale_price": 1100.00,
     *     "category": "Electronics",
     *     "supplier": "Dell Inc.",
     *     "available_units": [
     *       {
     *         "id": 1,
     *         "name": "Piece",
     *         "code": "PC",
     *         "symbol": "pc"
     *       }
     *     ]
     *   }
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "error": "Item not found."
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "message": "Error fetching item details: Database error"
     * }
     */
    public function getItemDetailsForInvoice(Request $request, $itemId)
    {
        try {
            $item = Item::where('id', $itemId)
                ->where('company_id', $request->user()->company_id ?? 101)
                ->with(['category', 'supplier'])
                ->first();

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'error' => 'Item not found.'
                ], 404);
            }

            // Get available units for this item
            $units = Unit::where('company_id', $request->user()->company_id ?? 101)
                ->where('status', 'active')
                ->select(['id', 'name', 'code', 'symbol'])
                ->get();

            $itemData = [
                'id' => $item->id,
                'item_number' => $item->item_number,
                'item_name' => $item->item_name_en ?? $item->item_name_ar,
                'unit' => $item->unit,
                'first_sale_price' => $item->first_sale_price,
                'second_sale_price' => $item->second_sale_price,
                'third_sale_price' => $item->third_sale_price,
                'category' => $item->category ? $item->category->name : null,
                'supplier' => $item->supplier ? $item->supplier->supplier_name_en : null,
                'available_units' => $units
            ];

            return response()->json([
                'success' => true,
                'data' => $itemData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching item details: ' . $e->getMessage()
            ], 500);
        }
    }
}
