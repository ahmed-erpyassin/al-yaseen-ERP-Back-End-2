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

class SalesHelperController extends Controller
{
    /**
     * Get customers for dropdown (number and name sync)
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
     * Get items for dropdown with auto-complete
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
     * Search customers by number or name for invoice creation
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
     * Search items by number or name for invoice creation
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
     * Get customer details by ID for auto-population
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
     * Get item details with unit information for auto-population
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
