<?php

namespace Modules\Companies\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Companies\Models\City;
use Modules\Companies\Models\Country;
use Modules\Companies\Models\Region;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            [
                'code' => 'PS',
                'name' => 'فلسطين',
                'name_en' => 'Palestine',
                'phone_code' => '970',
                'currency_code' => 'ILS',
                'timezone' => 'Asia/Hebron'
            ],
            [
                'code' => 'SA',
                'name' => 'المملكة العربية السعودية',
                'name_en' => 'Saudi Arabia',
                'phone_code' => '966',
                'currency_code' => 'SAR',
                'timezone' => 'Asia/Riyadh'
            ],
            [
                'code' => 'EG',
                'name' => 'مصر',
                'name_en' => 'Egypt',
                'phone_code' => '20',
                'currency_code' => 'EGP',
                'timezone' => 'Africa/Cairo'
            ],
            [
                'code' => 'JO',
                'name' => 'الأردن',
                'name_en' => 'Jordan',
                'phone_code' => '962',
                'currency_code' => 'JOD',
                'timezone' => 'Asia/Amman'
            ],
            [
                'code' => 'AE',
                'name' => 'الإمارات العربية المتحدة',
                'name_en' => 'United Arab Emirates',
                'phone_code' => '971',
                'currency_code' => 'AED',
                'timezone' => 'Asia/Dubai'
            ],
            [
                'code' => 'KW',
                'name' => 'الكويت',
                'name_en' => 'Kuwait',
                'phone_code' => '965',
                'currency_code' =>    'KWD',
                'timezone'    =>    'Asia/Kuwait'
            ],
            [
                'code' =>    'OM',
                'name'    =>    'عُمان',
                'name_en'    =>    'Oman',
                'phone_code'    =>    '968',
                'currency_code'    =>    'OMR',
                'timezone'    =>    'Asia/Muscat'
            ],
            [
                'code'    =>    'QA',
                'name'    =>    'قطر',
                'name_en'    =>    'Qatar',
                'phone_code'    =>    '974',
                'currency_code' =>    'AED',
                'timezone' =>    'Asia/Qatar'
            ],
            [
                'code' =>    'US',
                'name' =>    'الولايات المتحدة الأمريكية',
                'name_en' =>    'United States of America',
                'phone_code' => 1,
                'currency_code' => 'USD',
                'timezone' => 'America/New_York'
            ],
        ];

        $regions = [
            [
                'country_id' => 1, // فلسطين
                'name' => 'الضفة الغربية',
                'name_en' => 'West Bank',
                'country_code' => 'PS',
            ],
            [
                'country_id' => 1, // فلسطين
                'name' => 'قطاع غزة',
                'name_en' => 'Gaza Strip',
                'country_code' => 'PS',
            ],
            [
                'country_id' => 2, // السعودية
                'name' => 'الرياض',
                'name_en' => 'Riyadh',
                'country_code' => 'SA',
            ],
            [
                'country_id' => 2, // السعودية
                'name' => 'مكة المكرمة',
                'name_en' => 'Makkah',
                'country_code' => 'SA',
            ],
            [
                'country_id' => 2, // السعودية
                'name' => 'المدينة المنورة',
                'name_en' => 'Madinah',
                'country_code' => 'SA',
            ],
            [
                'country_id' => 3, // مصر
                'name' => 'القاهرة',
                'name_en' => 'Cairo',
                'country_code' => 'EG',
            ],
            [
                'country_id' => 3, // مصر
                'name' => 'الإسكندرية',
                'name_en' => 'Alexandria',
                'country_code' => 'EG',
            ],
            [
                'country_id' => 4, // الأردن
                'name' => 'عمّان',
                'name_en' => 'Amman',
                'country_code' => 'JO',
            ],
            [
                'country_id' => 4, // الأردن
                'name' => 'إربد',
                'name_en' => 'Irbid',
                'country_code' => 'JO',
            ],
            [
                'country_id' => 5, // الإمارات
                'name' => 'دبي',
                'name_en' => 'Dubai',
                'country_code' => 'AE',
            ],
            [
                'country_id' => 5, // الإمارات
                'name' => 'أبو ظبي',
                'name_en' =>    "Abu Dhabi",
                'country_code' =>    "AE",
            ],
            [
                'country_id' => 6, // الكويت
                'name' =>    "مدينة الكويت",
                'name_en' =>    "Kuwait City",
                'country_code' =>    "KW",
            ],
            [
                'country_id' => 7, // عُمان
                'name' =>    "مسقط",
                'name_en' =>    "Muscat",
                'country_code' =>    "OM",
            ],
            [
                'country_id' => 8, // قطر
                'name' =>    "الدوحة",
                'name_en' =>    "Doha",
                'country_code' =>    "QA",
            ],
        ];

        $cities = [
            [
                'region_id' => 1, // الضفة الغربية
                'name' => 'رام الله',
                'name_en' => 'Ramallah',
                'region_name' => 'الضفة الغربية',
            ],
            [
                'region_id' => 1, // الضفة الغربية
                'name' => 'نابلس',
                'name_en' => 'Nablus',
                'region_name' => 'الضفة الغربية',
            ],
            [
                'region_id' => 2, // قطاع غزة
                'name' => 'غزة',
                'name_en' => 'Gaza',
                'region_name' => 'قطاع غزة',
            ],
            [
                'region_id' => 2, // قطاع غزة
                'name' => 'خان يونس',
                'name_en' => 'Khan Younis',
                'region_name' => 'قطاع غزة',
            ],
            [
                'region_id' => 3, // الرياض
                'name' => 'الرياض',
                'name_en' => 'Riyadh',
                'region_name' => 'الرياض',
            ],
            [
                'region_id' => 4, // مكة المكرمة
                'name' => 'جدة',
                'name_en' => 'Jeddah',
                'region_name' => 'مكة المكرمة',
            ],
            [
                'region_id' => 5, // المدينة المنورة
                'name' => 'المدينة المنورة',
                'name_en' => 'Madinah',
                'region_name' => 'المدينة المنورة',
            ],
            [
                'region_id' => 6, // القاهرة
                'name' => 'القاهرة',
                'name_en' => 'Cairo',
                'region_name' => 'القاهرة',
            ],
            [
                'region_id' => 7, // الإسكندرية
                'name' => 'الإسكندرية',
                'name_en' =>    "Alexandria",
                'region_name' =>    "الإسكندرية",
            ],
            [
                'region_id' => 8, // عمّان
                'name' =>    "عمّان",
                'name_en' =>    "Amman",
                'region_name' =>    "عمّان",
            ],
            [
                'region_id' => 9, // إربد
                'name' =>    "إربد",
                'name_en' =>    "Irbid",
                'region_name' =>    "إربد",
            ],
            [
                'region_id' => 10, // دبي
                'name' =>    "دبي",
                'name_en' =>    "Dubai",
                'region_name' =>    "دبي",
            ],
            [
                'region_id' => 11, // أبو ظبي
                'name' =>    "أبو ظبي",
                'name_en' =>    "Abu Dhabi",
                'region_name' =>    "أبو ظبي",
            ],
            [
                'region_id' => 12, // مدينة الكويت
                'name' =>    "مدينة الكويت",
                'name_en' =>    "Kuwait City",
                'region_name' =>    "مدينة الكويت",
            ],
            [
                'region_id' => 13, // مسقط
                'name' =>    "مسقط",
                'name_en' =>    "Muscat",
                'region_name' =>    "مسقط",
            ],
        ];

        foreach ($countries as $countryData) {
            $country = Country::updateOrCreate(
                ['code' => $countryData['code']],
                $countryData
            );

            // إضافة المناطق المرتبطة بكل دولة
            foreach ($regions as $regionData) {
                if ($regionData['country_code'] === $country->code) {
                    $region = Region::updateOrCreate(
                        [
                            'country_id' => $country->id,
                            'name' => $regionData['name']
                        ],
                        [
                            'country_id' => $country->id,
                            'name' => $regionData['name'],
                            'name_en' => $regionData['name_en']
                        ]
                    );

                    // إضافة المدن المرتبطة بكل منطقة
                    foreach ($cities as $cityData) {
                        if ($cityData['region_name'] === $region->name) {
                            City::updateOrCreate(
                                [
                                    'region_id' => $region->id,
                                    'name' => $cityData['name']
                                ],
                                [
                                    'region_id' => $region->id,
                                    'country_id' => $country->id,
                                    'name' => $cityData['name'],
                                    'name_en' => $cityData['name_en']
                                ]
                            );
                        }
                    }
                }
            }
        }
    }
}
