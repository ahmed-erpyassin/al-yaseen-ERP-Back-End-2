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
}
