<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            ['name_ar' => 'الولايات المتحدة الأمريكية', 'name_en' => 'United States', 'code' => 'US'],
            ['name_ar' => 'فلسطين', 'name_en' => 'Palestine', 'code' => null],
            ['name_ar' => 'مصر', 'name_en' => 'Egypt', 'code' => 'EG'],
            ['name_ar' => 'اليابان', 'name_en' => 'Japan', 'code' => 'JP'],
            ['name_ar' => 'السعودية', 'name_en' => 'Saudi Arabia', 'code' => 'SA'],
            ['name_ar' => 'الإمارات العربية المتحدة', 'name_en' => 'United Arab Emirates', 'code' => 'AE'],
            ['name_ar' => 'البحرين', 'name_en' => 'Bahrain', 'code' => 'BH'],
            ['name_ar' => 'الاتحاد الأوروبي', 'name_en' => 'European Union', 'code' => 'EU'],
            ['name_ar' => 'سلطنة عمان', 'name_en' => 'Oman', 'code' => 'OM'],
            ['name_ar' => 'السودان', 'name_en' => 'Sudan', 'code' => 'SD'],
            ['name_ar' => 'لبنان', 'name_en' => 'Lebanon', 'code' => 'LB'],
            ['name_ar' => 'الجزائر', 'name_en' => 'Algeria', 'code' => 'DZ'],
            ['name_ar' => 'تونس', 'name_en' => 'Tunisia', 'code' => 'TN'],
            ['name_ar' => 'الصين', 'name_en' => 'China', 'code' => 'CN'],
            ['name_ar' => 'الهند', 'name_en' => 'India', 'code' => 'IN'],
            ['name_ar' => 'ماليزيا', 'name_en' => 'Malaysia', 'code' => 'MY'],
            ['name_ar' => 'قطر', 'name_en' => 'Qatar', 'code' => 'QA'],
            ['name_ar' => 'الكويت', 'name_en' => 'Kuwait', 'code' => 'KW'],
            ['name_ar' => 'المغرب', 'name_en' => 'Morocco', 'code' => 'MA'],
            ['name_ar' => 'سوريا', 'name_en' => 'Syria', 'code' => 'SY'],
            ['name_ar' => 'تركيا', 'name_en' => 'Turkey', 'code' => 'TR'],
            ['name_ar' => 'الأردن', 'name_en' => 'Jordan', 'code' => 'JO'],
            ['name_ar' => 'المملكة المتحدة', 'name_en' => 'United Kingdom', 'code' => 'GB'],
            ['name_ar' => 'كندا', 'name_en' => 'Canada', 'code' => 'CA'],
        ];

        DB::table('countries')->insert($countries);
    }
}
