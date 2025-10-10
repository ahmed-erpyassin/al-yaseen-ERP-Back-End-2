<?php

namespace Modules\HumanResources\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\FinancialAccounts\Models\Currency;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * @group Employee/Currency Rate Management
 *
 * APIs for managing currency exchange rates for employee salary calculations and financial operations.
 */
class CurrencyRateController extends Controller
{
    /**
     * Get Live Currency Rate
     *
     * Retrieve the current live exchange rate for a specific currency from external API sources.
     *
     * @bodyParam currency_id integer required The currency ID to get the rate for. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "currency_id": 1,
     *     "currency_code": "USD",
     *     "rate": 1.2500,
     *     "is_base_currency": false,
     *     "last_updated": "2025-10-05 12:00:00"
     *   },
     *   "message": "Live currency rate retrieved successfully."
     * }
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "currency_id": 2,
     *     "currency_code": "SAR",
     *     "rate": 1.0000,
     *     "is_base_currency": true,
     *     "last_updated": "2025-10-05 12:00:00"
     *   },
     *   "message": "Base currency rate retrieved successfully."
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "error": "Currency not found.",
     *   "message": "The specified currency does not exist."
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "currency_id": ["The currency id field is required."]
     *   }
     * }
     */
    public function getLiveRate(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'currency_id' => 'required|integer|exists:currencies,id'
            ]);

            $currency = Currency::find($request->currency_id);

            if (!$currency) {
                return response()->json([
                    'success' => false,
                    'error' => 'Currency not found.',
                    'message' => 'The specified currency does not exist.'
                ], 404);
            }

            // If it's the base currency, return 1.0
            if ($currency->is_base_currency) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'currency_id' => $currency->id,
                        'currency_code' => $currency->code,
                        'rate' => 1.0000,
                        'is_base_currency' => true,
                        'last_updated' => now()->format('Y-m-d H:i:s')
                    ],
                    'message' => 'Base currency rate retrieved successfully.'
                ]);
            }

            // Get live rate
            $liveRate = $this->fetchLiveRate($currency);

            return response()->json([
                'success' => true,
                'data' => [
                    'currency_id' => $currency->id,
                    'currency_code' => $currency->code,
                    'rate' => $liveRate,
                    'is_base_currency' => false,
                    'last_updated' => now()->format('Y-m-d H:i:s')
                ],
                'message' => 'Live currency rate retrieved successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching currency rate.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get live rates for multiple currencies
     *
     * @bodyParam currency_ids array required Array of currency IDs to get rates for. Example: [1, 2, 3]
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "rates": [
     *       {
     *         "currency_id": 1,
     *         "currency_code": "USD",
     *         "rate": 1.2500,
     *         "is_base_currency": false
     *       },
     *       {
     *         "currency_id": 2,
     *         "currency_code": "SAR",
     *         "rate": 1.0000,
     *         "is_base_currency": true
     *       }
     *     ],
     *     "last_updated": "2025-10-10 12:00:00"
     *   },
     *   "message": "Live currency rates retrieved successfully."
     * }
     */
    public function getLiveRates(Request $request): JsonResponse
    {
        try {
            // Log the incoming request for debugging
            Log::info('getLiveRates called with data: ', $request->all());

            // Validate the request
            $validated = $request->validate([
                'currency_ids' => 'required|array|min:1',
                'currency_ids.*' => 'integer|exists:currencies,id'
            ]);

            $currencyIds = $validated['currency_ids'];

            // Get currencies from database
            $currencies = Currency::whereIn('id', $currencyIds)->get();

            if ($currencies->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'No valid currencies found.',
                    'message' => 'None of the provided currency IDs exist.'
                ], 404);
            }

            $rates = [];

            foreach ($currencies as $currency) {
                try {
                    if ($currency->is_base_currency) {
                        $rates[] = [
                            'currency_id' => $currency->id,
                            'currency_code' => $currency->code,
                            'rate' => 1.0000,
                            'is_base_currency' => true,
                        ];
                    } else {
                        $liveRate = $this->fetchLiveRate($currency);
                        $rates[] = [
                            'currency_id' => $currency->id,
                            'currency_code' => $currency->code,
                            'rate' => $liveRate,
                            'is_base_currency' => false,
                        ];
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to get rate for currency {$currency->code}: " . $e->getMessage());
                    // Continue with other currencies, add this one with stored rate
                    $rates[] = [
                        'currency_id' => $currency->id,
                        'currency_code' => $currency->code,
                        'rate' => $currency->exchange_rate ?? 1.0,
                        'is_base_currency' => $currency->is_base_currency ?? false,
                        'error' => 'Failed to fetch live rate, using stored rate'
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'rates' => $rates,
                    'last_updated' => now()->format('Y-m-d H:i:s')
                ],
                'message' => 'Live currency rates retrieved successfully.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed.',
                'message' => 'The given data was invalid.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('getLiveRates error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching currency rates.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch live rate from external API or return stored rate
     */
    private function fetchLiveRate(Currency $currency): float
    {
        try {
            // Cache key for this currency
            $cacheKey = "live_rate_{$currency->code}";

            // Check if we have a cached rate (cache for 5 minutes)
            $cachedRate = Cache::get($cacheKey);
            if ($cachedRate !== null) {
                return (float) $cachedRate;
            }

            // Try to fetch from external API (example with exchangerate-api.com)
            $liveRate = $this->fetchFromExternalAPI($currency);

            if ($liveRate !== null) {
                // Cache the rate for 5 minutes
                Cache::put($cacheKey, $liveRate, 300);

                // Update the currency record with the new rate
                $currency->update(['exchange_rate' => $liveRate]);

                return $liveRate;
            }

            // Fallback to stored rate
            return $currency->exchange_rate ?? 1.0;

        } catch (\Exception $e) {
            // Fallback to stored rate on error
            return $currency->exchange_rate ?? 1.0;
        }
    }

    /**
     * Fetch rate from external API
     */
    private function fetchFromExternalAPI(Currency $currency): ?float
    {
        try {
            // Example using exchangerate-api.com (free tier)
            // You can replace this with your preferred API
            $baseCurrency = Currency::where('is_base_currency', true)->first();
            if (!$baseCurrency) {
                return null;
            }

            $response = Http::timeout(10)->get("https://api.exchangerate-api.com/v4/latest/{$baseCurrency->code}");

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['rates'][$currency->code])) {
                    return (float) $data['rates'][$currency->code];
                }
            }

            return null;

        } catch (\Exception $e) {
            // Log the error if needed
            Log::warning("Failed to fetch live rate for {$currency->code}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update currency rate manually
     */
    public function updateRate(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'currency_id' => 'required|integer|exists:currencies,id',
                'rate' => 'required|numeric|min:0.0001'
            ]);

            $currency = Currency::find($request->currency_id);

            if ($currency->is_base_currency) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot update base currency rate.',
                    'message' => 'Base currency rate is always 1.0'
                ], 400);
            }

            $currency->update(['exchange_rate' => $request->rate]);

            // Clear cache
            Cache::forget("live_rate_{$currency->code}");

            return response()->json([
                'success' => true,
                'data' => [
                    'currency_id' => $currency->id,
                    'currency_code' => $currency->code,
                    'rate' => (float) $request->rate,
                    'updated_at' => $currency->updated_at->format('Y-m-d H:i:s')
                ],
                'message' => 'Currency rate updated successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while updating currency rate.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
