<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarcodeTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $barcodeTypes = [
            [
                'code' => 'C128',
                'name' => 'Code 128',
                'name_ar' => 'كود 128',
                'description' => 'Code 128 is a high-density linear barcode symbology defined in ISO/IEC 15417:2007.',
                'description_ar' => 'كود 128 هو رمز شريطي خطي عالي الكثافة محدد في ISO/IEC 15417:2007.',
                'is_default' => true, // C128 as default
                'is_active' => true,
                'validation_rules' => json_encode([
                    'type' => 'alphanumeric',
                    'supports_all_ascii' => true
                ]),
                'min_length' => 1,
                'max_length' => 80,
                'pattern' => '/^[\x00-\x7F]+$/', // ASCII characters
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'EAN13',
                'name' => 'EAN-13',
                'name_ar' => 'إي إيه إن-13',
                'description' => 'European Article Number 13-digit barcode standard.',
                'description_ar' => 'معيار الرقم الأوروبي للمقالات المكون من 13 رقماً.',
                'is_default' => false,
                'is_active' => true,
                'validation_rules' => json_encode([
                    'type' => 'numeric',
                    'check_digit' => true
                ]),
                'min_length' => 13,
                'max_length' => 13,
                'pattern' => '/^\d{13}$/', // Exactly 13 digits
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'C39',
                'name' => 'Code 39',
                'name_ar' => 'كود 39',
                'description' => 'Code 39 is a variable length, discrete barcode symbology.',
                'description_ar' => 'كود 39 هو رمز شريطي متغير الطول ومنفصل.',
                'is_default' => false,
                'is_active' => true,
                'validation_rules' => json_encode([
                    'type' => 'alphanumeric',
                    'allowed_chars' => '0-9, A-Z, space, -, ., $, /, +, %'
                ]),
                'min_length' => 1,
                'max_length' => 43,
                'pattern' => '/^[0-9A-Z\-\.\$\/\+\%\s]+$/', // Code 39 allowed characters
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'UPCA',
                'name' => 'UPC-A',
                'name_ar' => 'يو بي سي-إيه',
                'description' => 'Universal Product Code version A, 12-digit barcode.',
                'description_ar' => 'كود المنتج العالمي الإصدار أ، رمز شريطي مكون من 12 رقماً.',
                'is_default' => false,
                'is_active' => true,
                'validation_rules' => json_encode([
                    'type' => 'numeric',
                    'check_digit' => true
                ]),
                'min_length' => 12,
                'max_length' => 12,
                'pattern' => '/^\d{12}$/', // Exactly 12 digits
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'ITF',
                'name' => 'Interleaved 2 of 5',
                'name_ar' => 'متداخل 2 من 5',
                'description' => 'Interleaved 2 of 5 is a continuous two-width barcode symbology.',
                'description_ar' => 'متداخل 2 من 5 هو رمز شريطي مستمر بعرضين.',
                'is_default' => false,
                'is_active' => true,
                'validation_rules' => json_encode([
                    'type' => 'numeric',
                    'even_length_required' => true
                ]),
                'min_length' => 2,
                'max_length' => 80,
                'pattern' => '/^\d+$/', // Only digits, even length
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('barcode_types')->insert($barcodeTypes);
    }
}
