<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Inventory\Models\Unit;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get required data
        $user = User::first();
        $company = Company::first();

        if (!$user || !$company) {
            $this->command->warn('⚠️  Users or Companies not found. Please seed Users and Companies modules first.');
            return;
        }

        $units = [
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'name' => 'قطعة',
                'code' => 'PCS',
                'symbol' => 'قطعة',
                'description' => 'وحدة القطعة للعد',
                'decimal_places' => 0,
                'balance_unit' => 'piece',
                'custom_balance_unit' => null,
                'length' => null,
                'width' => null,
                'height' => null,
                'quantity_factor' => 1.0000,
                // Remove problematic fields - let them use defaults or be null
                'default_handling_unit_id' => null,
                'default_warehouse_id' => null,
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'name' => 'كيلوجرام',
                'code' => 'KG',
                'symbol' => 'كجم',
                'description' => 'وحدة الوزن بالكيلوجرام',
                'decimal_places' => 3,
                'balance_unit' => 'kilo',
                'custom_balance_unit' => null,
                'length' => null,
                'width' => null,
                'height' => null,
                'quantity_factor' => 1.0000,
                // Remove problematic fields - let them use defaults or be null
                'default_handling_unit_id' => null,
                'default_warehouse_id' => null,
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'name' => 'لتر',
                'code' => 'LTR',
                'symbol' => 'لتر',
                'description' => 'وحدة الحجم باللتر',
                'decimal_places' => 3,
                'balance_unit' => 'liter',
                'custom_balance_unit' => null,
                'length' => null,
                'width' => null,
                'height' => null,
                'quantity_factor' => 1.0000,
                'second_unit' => null,
                'custom_second_unit' => null,
                'second_unit_contains' => null,
                'custom_second_unit_contains' => null,
                'second_unit_content' => null,
                'second_unit_item_number' => null,
                'third_unit' => null,
                'custom_third_unit' => null,
                'third_unit_contains' => null,
                'custom_third_unit_contains' => null,
                'third_unit_content' => null,
                'third_unit_item_number' => null,
                'default_handling_unit_id' => null,
                'default_warehouse_id' => null,
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'name' => 'طن',
                'code' => 'TON',
                'symbol' => 'طن',
                'description' => 'وحدة الوزن بالطن',
                'decimal_places' => 3,
                'balance_unit' => 'ton',
                'custom_balance_unit' => null,
                'length' => null,
                'width' => null,
                'height' => null,
                'quantity_factor' => 1000.0000,
                'second_unit' => 'kilo',
                'custom_second_unit' => null,
                'second_unit_contains' => 'all',
                'custom_second_unit_contains' => null,
                'second_unit_content' => '1000',
                'second_unit_item_number' => null,
                'third_unit' => null,
                'custom_third_unit' => null,
                'third_unit_contains' => null,
                'custom_third_unit_contains' => null,
                'third_unit_content' => null,
                'third_unit_item_number' => null,
                'default_handling_unit_id' => null,
                'default_warehouse_id' => null,
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'name' => 'كرتون',
                'code' => 'CTN',
                'symbol' => 'كرتون',
                'description' => 'وحدة التعبئة بالكرتون',
                'decimal_places' => 0,
                'balance_unit' => 'carton',
                'custom_balance_unit' => null,
                'length' => 50.00,
                'width' => 30.00,
                'height' => 25.00,
                'quantity_factor' => 1.0000,
                'second_unit' => 'piece',
                'custom_second_unit' => null,
                'second_unit_contains' => 'all',
                'custom_second_unit_contains' => null,
                'second_unit_content' => '24',
                'second_unit_item_number' => null,
                'third_unit' => null,
                'custom_third_unit' => null,
                'third_unit_contains' => null,
                'custom_third_unit_contains' => null,
                'third_unit_content' => null,
                'third_unit_item_number' => null,
                'default_handling_unit_id' => null,
                'default_warehouse_id' => null,
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'name' => 'متر',
                'code' => 'MTR',
                'symbol' => 'م',
                'description' => 'وحدة الطول بالمتر',
                'decimal_places' => 2,
                'balance_unit' => 'piece',
                'custom_balance_unit' => 'متر',
                'length' => 1.00,
                'width' => null,
                'height' => null,
                'quantity_factor' => 1.0000,
                'second_unit' => null,
                'custom_second_unit' => null,
                'second_unit_contains' => null,
                'custom_second_unit_contains' => null,
                'second_unit_content' => null,
                'second_unit_item_number' => null,
                'third_unit' => null,
                'custom_third_unit' => null,
                'third_unit_contains' => null,
                'custom_third_unit_contains' => null,
                'third_unit_content' => null,
                'third_unit_item_number' => null,
                'default_handling_unit_id' => null,
                'default_warehouse_id' => null,
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'name' => 'متر مربع',
                'code' => 'SQM',
                'symbol' => 'م²',
                'description' => 'وحدة المساحة بالمتر المربع',
                'decimal_places' => 2,
                'balance_unit' => 'piece',
                'custom_balance_unit' => 'متر مربع',
                'length' => 1.00,
                'width' => 1.00,
                'height' => null,
                'quantity_factor' => 1.0000,
                'second_unit' => null,
                'custom_second_unit' => null,
                'second_unit_contains' => null,
                'custom_second_unit_contains' => null,
                'second_unit_content' => null,
                'second_unit_item_number' => null,
                'third_unit' => null,
                'custom_third_unit' => null,
                'third_unit_contains' => null,
                'custom_third_unit_contains' => null,
                'third_unit_content' => null,
                'third_unit_item_number' => null,
                'default_handling_unit_id' => null,
                'default_warehouse_id' => null,
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'name' => 'دزينة',
                'code' => 'DOZ',
                'symbol' => 'دزينة',
                'description' => 'وحدة العد بالدزينة (12 قطعة)',
                'decimal_places' => 0,
                'balance_unit' => 'piece',
                'custom_balance_unit' => null,
                'length' => null,
                'width' => null,
                'height' => null,
                'quantity_factor' => 12.0000,
                'second_unit' => 'piece',
                'custom_second_unit' => null,
                'second_unit_contains' => 'all',
                'custom_second_unit_contains' => null,
                'second_unit_content' => '12',
                'second_unit_item_number' => null,
                'third_unit' => null,
                'custom_third_unit' => null,
                'third_unit_contains' => null,
                'custom_third_unit_contains' => null,
                'third_unit_content' => null,
                'third_unit_item_number' => null,
                'default_handling_unit_id' => null,
                'default_warehouse_id' => null,
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
        ];

        foreach ($units as $unitData) {
            // Convert null values to empty strings for required fields
            $requiredStringFields = [
                'second_unit', 'custom_second_unit', 'second_unit_contains',
                'custom_second_unit_contains', 'second_unit_content', 'second_unit_item_number',
                'third_unit', 'custom_third_unit', 'third_unit_contains',
                'custom_third_unit_contains', 'third_unit_content', 'third_unit_item_number'
            ];

            foreach ($requiredStringFields as $field) {
                if (!isset($unitData[$field]) || $unitData[$field] === null) {
                    // For enum fields, use null instead of empty string
                    if (in_array($field, ['second_unit', 'third_unit'])) {
                        unset($unitData[$field]); // Remove the field entirely
                    } elseif (in_array($field, ['second_unit_contains', 'third_unit_contains'])) {
                        $unitData[$field] = 'all'; // Use default value
                    } else {
                        $unitData[$field] = '';
                    }
                }
            }

            Unit::firstOrCreate([
                'company_id' => $unitData['company_id'],
                'code' => $unitData['code']
            ], $unitData);
        }

        $this->command->info('✅ Units seeded successfully!');
    }
}
