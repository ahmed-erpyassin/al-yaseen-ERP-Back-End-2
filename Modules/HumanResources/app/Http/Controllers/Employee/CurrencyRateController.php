<?php

namespace Modules\HumanResources\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\FinancialAccounts\Models\Currency;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CurrencyRateController extends Controller
{
    /**
     * Get live currency rate for a specific currency
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
     */
    public function getLiveRates(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'currency_ids' => 'required|array',
                'currency_ids.*' => 'integer|exists:currencies,id'
            ]);

            $currencies = Currency::whereIn('id', $request->currency_ids)->get();
            $rates = [];

            foreach ($currencies as $currency) {
                if ($currency->is_base_currency) {
                    $rates[] = [
                        'currency_id' => $currency->id,
                        'currency_code' => $currency->code,
                        'rate' => 1.0000,
                        'is_base_currency' => true,
                    ];
                } else {
                    $rates[] = [
                        'currency_id' => $currency->id,
                        'currency_code' => $currency->code,
                        'rate' => $this->fetchLiveRate($currency),
                        'is_base_currency' => false,
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

        } catch (\Exception $e) {
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
            \Log::warning("Failed to fetch live rate for {$currency->code}: " . $e->getMessage());
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
