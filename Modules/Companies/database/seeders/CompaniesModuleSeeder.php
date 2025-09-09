<?php

namespace Modules\Companies\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Companies\Models\BusinessType;
use Modules\Companies\Models\City;
use Modules\Companies\Models\Country;
use Modules\Companies\Models\Industry;
use Modules\Companies\Models\Region;

class CompaniesModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /**
         * 1. Countries
         */
        $countries = [
            [
                'code'         => 'PS',
                'name'         => 'فلسطين',
                'name_en'      => 'Palestine',
                'phone_code'   => '+970',
                'currency_code' => 'ILS',
                'timezone'     => 'Asia/Gaza',
                'regions'      => [
                    [
                        'name'    => 'قطاع غزة',
                        'name_en' => 'Gaza Strip',
                        'cities'  => [
                            ['name' => 'غزة', 'name_en' => 'Gaza'],
                            ['name' => 'خان يونس', 'name_en' => 'Khan Younis'],
                            ['name' => 'رفح', 'name_en' => 'Rafah'],
                        ],
                    ],
                    [
                        'name'    => 'الضفة الغربية',
                        'name_en' => 'West Bank',
                        'cities'  => [
                            ['name' => 'رام الله', 'name_en' => 'Ramallah'],
                            ['name' => 'الخليل', 'name_en' => 'Hebron'],
                            ['name' => 'نابلس', 'name_en' => 'Nablus'],
                        ],
                    ],
                ],
            ],
            [
                'code'         => 'SA',
                'name'         => 'السعودية',
                'name_en'      => 'Saudi Arabia',
                'phone_code'   => '+966',
                'currency_code' => 'SAR',
                'timezone'     => 'Asia/Riyadh',
                'regions'      => [
                    [
                        'name'    => 'منطقة الرياض',
                        'name_en' => 'Riyadh Region',
                        'cities'  => [
                            ['name' => 'الرياض', 'name_en' => 'Riyadh'],
                            ['name' => 'الخرج', 'name_en' => 'Al Kharj'],
                        ],
                    ],
                    [
                        'name'    => 'منطقة مكة',
                        'name_en' => 'Makkah Region',
                        'cities'  => [
                            ['name' => 'مكة المكرمة', 'name_en' => 'Makkah'],
                            ['name' => 'جدة', 'name_en' => 'Jeddah'],
                            ['name' => 'الطائف', 'name_en' => 'Taif'],
                        ],
                    ],
                ],
            ],
            [
                'code'         => 'EG',
                'name'         => 'مصر',
                'name_en'      => 'Egypt',
                'phone_code'   => '+20',
                'currency_code' => 'EGP',
                'timezone'     => 'Africa/Cairo',
                'regions'      => [
                    [
                        'name'    => 'القاهرة الكبرى',
                        'name_en' => 'Greater Cairo',
                        'cities'  => [
                            ['name' => 'القاهرة', 'name_en' => 'Cairo'],
                            ['name' => 'الجيزة', 'name_en' => 'Giza'],
                        ],
                    ],
                    [
                        'name'    => 'الإسكندرية',
                        'name_en' => 'Alexandria',
                        'cities'  => [
                            ['name' => 'الإسكندرية', 'name_en' => 'Alexandria'],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($countries as $countryData) {
            $country = Country::firstOrCreate(
                ['code' => $countryData['code']],
                collect($countryData)->except('regions')->toArray()
            );

            foreach ($countryData['regions'] as $regionData) {
                $region = Region::firstOrCreate(
                    ['name_en' => $regionData['name_en']],
                    [
                        'country_id' => $country->id,
                        'name'       => $regionData['name'],
                        'name_en'    => $regionData['name_en'],
                    ]
                );

                foreach ($regionData['cities'] as $cityData) {
                    City::firstOrCreate(
                        ['name_en' => $cityData['name_en']],
                        [
                            'country_id' => $country->id,
                            'region_id'  => $region->id,
                            'name'       => $cityData['name'],
                            'name_en'    => $cityData['name_en'],
                        ]
                    );
                }
            }
        }

        /**
         * 2. Industries & Business Types
         */
        $industries = [
            [
                'name'    => 'تكنولوجيا المعلومات',
                'name_en' => 'Information Technology',
                'desc'    => 'قطاع البرمجيات والحلول التقنية',
                'types'   => [
                    ['name' => 'شركة برمجيات', 'desc' => 'متخصصة في تطوير البرمجيات'],
                    ['name' => 'شركة استشارات تقنية', 'desc' => 'خدمات استشارية تقنية'],
                ],
            ],
            [
                'name'    => 'البناء',
                'name_en' => 'Construction',
                'desc'    => 'قطاع المقاولات والبنية التحتية',
                'types'   => [
                    ['name' => 'مقاول عام', 'desc' => 'شركة متخصصة في جميع أعمال البناء'],
                    ['name' => 'مقاول فرعي', 'desc' => 'تنفيذ جزئي ضمن مشروع البناء'],
                ],
            ],
            [
                'name'    => 'الصناعة',
                'name_en' => 'Manufacturing',
                'desc'    => 'الإنتاج الصناعي والسلع',
                'types'   => [
                    ['name' => 'مصنع مواد غذائية', 'desc' => 'إنتاج الأغذية والمشروبات'],
                    ['name' => 'مصنع ملابس', 'desc' => 'إنتاج الملابس الجاهزة'],
                ],
            ],
        ];

        foreach ($industries as $indData) {
            $industry = Industry::firstOrCreate(
                ['name_en' => $indData['name_en']],
                [
                    'name'        => $indData['name'],
                    'name_en'     => $indData['name_en'],
                    'description' => $indData['desc'],
                ]
            );

            foreach ($indData['types'] as $typeData) {
                BusinessType::firstOrCreate(
                    ['name' => $typeData['name']],
                    [
                        'industry_id' => $industry->id,
                        'name'        => $typeData['name'],
                        'description' => $typeData['desc'],
                        'status'      => 'active',
                    ]
                );
            }
        }
    }
}
