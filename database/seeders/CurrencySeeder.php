<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['name' => 'US Dollar', 'code' => 'USD', 'symbol' => '$'],
            ['name' => 'New Israeli Shekel', 'code' => 'ILS', 'symbol' => '₪'],
            ['name' => 'Egyptian Pound', 'code' => 'EGP', 'symbol' => 'E£'],
            ['name' => 'Japanese Yen', 'code' => 'JPY', 'symbol' => '¥'],
            ['name' => 'Saudi Riyal', 'code' => 'SAR', 'symbol' => 'SR'],
            ['name' => 'UAE Dirham', 'code' => 'AED', 'symbol' => 'د.إ'],
            ['name' => 'Bahraini Dinar', 'code' => 'BHD', 'symbol' => 'BD'],
            ['name' => 'Euro', 'code' => 'EUR', 'symbol' => '€'],
            ['name' => 'Omani Rial', 'code' => 'OMR', 'symbol' => 'ر.ع.'],
            ['name' => 'Sudanese Pound', 'code' => 'SDG', 'symbol' => 'ج.س.'],
            ['name' => 'Lebanese Pound', 'code' => 'LBP', 'symbol' => 'ل.ل.'],
            ['name' => 'Algerian Dinar', 'code' => 'DZD', 'symbol' => 'د.ج'],
            ['name' => 'Tunisian Dinar', 'code' => 'TND', 'symbol' => 'د.ت'],
            ['name' => 'Chinese Yuan', 'code' => 'CNY', 'symbol' => '¥'],
            ['name' => 'Indian Rupee', 'code' => 'INR', 'symbol' => '₹'],
            ['name' => 'Malaysian Ringgit', 'code' => 'MYR', 'symbol' => 'RM'],
            ['name' => 'Qatari Riyal', 'code' => 'QAR', 'symbol' => 'ر.ق'],
            ['name' => 'Kuwaiti Dinar', 'code' => 'KWD', 'symbol' => 'د.ك'],
            ['name' => 'Moroccan Dirham', 'code' => 'MAD', 'symbol' => 'د.م.'],
            ['name' => 'Syrian Pound', 'code' => 'SYP', 'symbol' => 'ل.س'],
        ];

        foreach ($currencies as $currency) {
            DB::table('currencies')->insert([
                'name' => $currency['name'],
                'code' => $currency['code'],
                'symbol' => $currency['symbol'],
                'company_id' => 1, // Default company
                'decimal_places' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
