<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['name_en' => 'US Dollar',        'name_ar' => 'دولار أمريكي', 'code' => 'USD'],
            ['name_en' => 'New Israeli Shekel','name_ar' => 'شيكل جديد', 'code' => 'ILS'],
            ['name_en' => 'Egyptian Pound',   'name_ar' => 'جنيه مصري', 'code' => 'EGP'],
            ['name_en' => 'Japanese Yen',     'name_ar' => 'ين ياباني', 'code' => 'JPY'],
            ['name_en' => 'Saudi Riyal',      'name_ar' => 'ريال سعودي', 'code' => 'SAR'],
            ['name_en' => 'UAE Dirham',       'name_ar' => 'درهم إماراتي', 'code' => 'AED'],
            ['name_en' => 'Bahraini Dinar',   'name_ar' => 'دينار بحريني', 'code' => 'BHD'],
            ['name_en' => 'Euro',             'name_ar' => 'يورو', 'code' => 'EUR'],
            ['name_en' => 'Omani Rial',       'name_ar' => 'ريال عماني', 'code' => 'OMR'],
            ['name_en' => 'Sudanese Pound',   'name_ar' => 'جنيه سوداني', 'code' => 'SDG'],
            ['name_en' => 'Lebanese Pound',   'name_ar' => 'ليرة لبنانية', 'code' => 'LBP'],
            ['name_en' => 'Algerian Dinar',   'name_ar' => 'دينار جزائري', 'code' => 'DZD'],
            ['name_en' => 'Tunisian Dinar',   'name_ar' => 'دينار تونسي', 'code' => 'TND'],
            ['name_en' => 'Chinese Yuan',     'name_ar' => 'يوان صيني', 'code' => 'CNY'],
            ['name_en' => 'Indian Rupee',     'name_ar' => 'روبية هندية', 'code' => 'INR'],
            ['name_en' => 'Malaysian Ringgit','name_ar' => 'رنجت ماليزي', 'code' => 'MYR'],
            ['name_en' => 'Qatari Riyal',     'name_ar' => 'ريال قطري', 'code' => 'QAR'],
            ['name_en' => 'Kuwaiti Dinar',    'name_ar' => 'دينار كويتي', 'code' => 'KWD'],
            ['name_en' => 'Moroccan Dirham',  'name_ar' => 'درهم مغربي', 'code' => 'MAD'],
            ['name_en' => 'Syrian Pound',     'name_ar' => 'ليرة سورية', 'code' => 'SYP'],
        ];

        foreach ($currencies as $currency) {
            DB::table('currencies')->insert([
                'name_en' => $currency['name_en'],
                'name_ar' => $currency['name_ar'],
                'code'    => $currency['code'],
                'status'  => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
