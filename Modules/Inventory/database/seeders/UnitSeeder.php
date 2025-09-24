<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Inventory\Models\Unit;
use Modules\Inventory\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get required data using DB queries to avoid model dependencies
        $user = DB::table('users')->first();
        $company = DB::table('companies')->first();
        $warehouse = DB::table('warehouses')->first();
        $branch = DB::table('branches')->first();

        if (!$user || !$company) {
            $this->command->warn('⚠️  Users or Companies not found. Please seed Users and Companies modules first.');
            return;
        }

        // Clear existing units to avoid conflicts
        DB::table('units')->delete();
        $this->command->info('🗑️  Cleared existing units data.');

        $units = [
            [
                // ✅ Required System Fields
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch ? $branch->id : null,

                // ✅ Basic Unit Information
                'name' => 'قطعة',
                'code' => 'PCS',
                'symbol' => 'قطعة',
                'description' => 'وحدة القطعة للعد - الوحدة الأساسية للعد',
                'decimal_places' => 0,

                // ✅ Balance Unit
                'balance_unit' => 'piece',
                'custom_balance_unit' => null,

                // ✅ Dimensions
                'length' => null,
                'width' => null,
                'height' => null,
                'quantity_factor' => 1.0000,

                // ✅ Second Unit (complete data)
                'second_unit' => null,
                'custom_second_unit' => null,
                'second_unit_contains' => 'all',
                'custom_second_unit_contains' => null,
                'second_unit_content' => null,
                'second_unit_item_number' => null,

                // ✅ Third Unit (complete data)
                'third_unit' => null,
                'custom_third_unit' => null,
                'third_unit_contains' => 'all',
                'custom_third_unit_contains' => null,
                'third_unit_content' => null,
                'third_unit_item_number' => null,

                // ✅ Default References
                'default_handling_unit_id' => null,
                'default_warehouse_id' => $warehouse ? $warehouse->id : null,

                // ✅ Status and Audit
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                // ✅ Required System Fields
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch ? $branch->id : null,

                // ✅ Basic Unit Information
                'name' => 'كيلوجرام',
                'code' => 'KG',
                'symbol' => 'كجم',
                'description' => 'وحدة الوزن بالكيلوجرام - وحدة قياس الوزن الأساسية',
                'decimal_places' => 3,

                // ✅ Balance Unit
                'balance_unit' => 'kilo',
                'custom_balance_unit' => null,

                // ✅ Dimensions
                'length' => null,
                'width' => null,
                'height' => null,
                'quantity_factor' => 1.0000,

                // ✅ Second Unit (Gram conversion)
                'second_unit' => 'piece',
                'custom_second_unit' => 'جرام',
                'second_unit_contains' => 'all',
                'custom_second_unit_contains' => 'جرام',
                'second_unit_content' => '1000 جرام في الكيلوجرام الواحد',
                'second_unit_item_number' => 'GRM-001',

                // ✅ Third Unit (complete data)
                'third_unit' => null,
                'custom_third_unit' => null,
                'third_unit_contains' => 'all',
                'custom_third_unit_contains' => null,
                'third_unit_content' => null,
                'third_unit_item_number' => null,

                // ✅ Default References
                'default_handling_unit_id' => null,
                'default_warehouse_id' => $warehouse ? $warehouse->id : null,

                // ✅ Status and Audit
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                // ✅ Required System Fields
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch ? $branch->id : null,

                // ✅ Basic Unit Information
                'name' => 'لتر',
                'code' => 'LTR',
                'symbol' => 'لتر',
                'description' => 'وحدة الحجم باللتر - وحدة قياس السوائل والحجم',
                'decimal_places' => 3,

                // ✅ Balance Unit
                'balance_unit' => 'liter',
                'custom_balance_unit' => null,

                // ✅ Dimensions
                'length' => null,
                'width' => null,
                'height' => null,
                'quantity_factor' => 1.0000,

                // ✅ Second Unit (Milliliter conversion)
                'second_unit' => 'piece',
                'custom_second_unit' => 'مليلتر',
                'second_unit_contains' => 'all',
                'custom_second_unit_contains' => 'مليلتر',
                'second_unit_content' => '1000 مليلتر في اللتر الواحد',
                'second_unit_item_number' => 'ML-001',

                // ✅ Third Unit (Gallon conversion)
                'third_unit' => 'piece',
                'custom_third_unit' => 'جالون',
                'third_unit_contains' => 'all',
                'custom_third_unit_contains' => 'جالون',
                'third_unit_content' => '3.785 لتر في الجالون الواحد',
                'third_unit_item_number' => 'GAL-001',

                // ✅ Default References
                'default_handling_unit_id' => null,
                'default_warehouse_id' => $warehouse ? $warehouse->id : null,

                // ✅ Status and Audit
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                // ✅ Required System Fields
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch ? $branch->id : null,

                // ✅ Basic Unit Information
                'name' => 'طن',
                'code' => 'TON',
                'symbol' => 'طن',
                'description' => 'وحدة الوزن بالطن - وحدة قياس الأوزان الثقيلة',
                'decimal_places' => 3,

                // ✅ Balance Unit
                'balance_unit' => 'ton',
                'custom_balance_unit' => null,

                // ✅ Dimensions
                'length' => null,
                'width' => null,
                'height' => null,
                'quantity_factor' => 1000.0000,

                // ✅ Second Unit (Kilogram conversion)
                'second_unit' => 'kilo',
                'custom_second_unit' => null,
                'second_unit_contains' => 'all',
                'custom_second_unit_contains' => 'كيلوجرام',
                'second_unit_content' => '1000 كيلوجرام في الطن الواحد',
                'second_unit_item_number' => 'KG-TON-001',

                // ✅ Third Unit (Pound conversion)
                'third_unit' => 'piece',
                'custom_third_unit' => 'رطل',
                'third_unit_contains' => 'all',
                'custom_third_unit_contains' => 'رطل',
                'third_unit_content' => '2204.62 رطل في الطن الواحد',
                'third_unit_item_number' => 'LB-TON-001',

                // ✅ Default References
                'default_handling_unit_id' => null,
                'default_warehouse_id' => $warehouse ? $warehouse->id : null,

                // ✅ Status and Audit
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                // ✅ Required System Fields
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch ? $branch->id : null,

                // ✅ Basic Unit Information
                'name' => 'كرتون',
                'code' => 'CTN',
                'symbol' => 'كرتون',
                'description' => 'وحدة التعبئة بالكرتون - وحدة تعبئة وتغليف',
                'decimal_places' => 0,

                // ✅ Balance Unit
                'balance_unit' => 'carton',
                'custom_balance_unit' => null,

                // ✅ Dimensions (with actual carton dimensions)
                'length' => 50.00,
                'width' => 30.00,
                'height' => 25.00,
                'quantity_factor' => 1.0000,

                // ✅ Second Unit (Pieces inside carton)
                'second_unit' => 'piece',
                'custom_second_unit' => null,
                'second_unit_contains' => 'all',
                'custom_second_unit_contains' => 'قطعة',
                'second_unit_content' => '24 قطعة في الكرتون الواحد',
                'second_unit_item_number' => 'PCS-CTN-001',

                // ✅ Third Unit (Box inside carton)
                'third_unit' => 'piece',
                'custom_third_unit' => 'علبة',
                'third_unit_contains' => 'all',
                'custom_third_unit_contains' => 'علبة',
                'third_unit_content' => '6 علب في الكرتون الواحد',
                'third_unit_item_number' => 'BOX-CTN-001',

                // ✅ Default References
                'default_handling_unit_id' => null,
                'default_warehouse_id' => $warehouse ? $warehouse->id : null,

                // ✅ Status and Audit
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                // ✅ Required System Fields
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch ? $branch->id : null,

                // ✅ Basic Unit Information
                'name' => 'متر',
                'code' => 'MTR',
                'symbol' => 'م',
                'description' => 'وحدة الطول بالمتر - وحدة قياس الأطوال والمسافات',
                'decimal_places' => 2,

                // ✅ Balance Unit
                'balance_unit' => 'piece',
                'custom_balance_unit' => 'متر',

                // ✅ Dimensions (length measurement)
                'length' => 1.00,
                'width' => null,
                'height' => null,
                'quantity_factor' => 1.0000,

                // ✅ Second Unit (Centimeter conversion)
                'second_unit' => 'piece',
                'custom_second_unit' => 'سنتيمتر',
                'second_unit_contains' => 'all',
                'custom_second_unit_contains' => 'سنتيمتر',
                'second_unit_content' => '100 سنتيمتر في المتر الواحد',
                'second_unit_item_number' => 'CM-MTR-001',

                // ✅ Third Unit (Millimeter conversion)
                'third_unit' => 'piece',
                'custom_third_unit' => 'مليمتر',
                'third_unit_contains' => 'all',
                'custom_third_unit_contains' => 'مليمتر',
                'third_unit_content' => '1000 مليمتر في المتر الواحد',
                'third_unit_item_number' => 'MM-MTR-001',

                // ✅ Default References
                'default_handling_unit_id' => null,
                'default_warehouse_id' => $warehouse ? $warehouse->id : null,

                // ✅ Status and Audit
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                // ✅ Required System Fields
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch ? $branch->id : null,

                // ✅ Basic Unit Information
                'name' => 'متر مربع',
                'code' => 'SQM',
                'symbol' => 'م²',
                'description' => 'وحدة المساحة بالمتر المربع - وحدة قياس المساحات والأراضي',
                'decimal_places' => 2,

                // ✅ Balance Unit
                'balance_unit' => 'piece',
                'custom_balance_unit' => 'متر مربع',

                // ✅ Dimensions (area measurement)
                'length' => 1.00,
                'width' => 1.00,
                'height' => null,
                'quantity_factor' => 1.0000,

                // ✅ Second Unit (Square centimeter conversion)
                'second_unit' => 'piece',
                'custom_second_unit' => 'سنتيمتر مربع',
                'second_unit_contains' => 'all',
                'custom_second_unit_contains' => 'سنتيمتر مربع',
                'second_unit_content' => '10000 سنتيمتر مربع في المتر المربع الواحد',
                'second_unit_item_number' => 'SQCM-SQM-001',

                // ✅ Third Unit (Square foot conversion)
                'third_unit' => 'piece',
                'custom_third_unit' => 'قدم مربع',
                'third_unit_contains' => 'all',
                'custom_third_unit_contains' => 'قدم مربع',
                'third_unit_content' => '10.764 قدم مربع في المتر المربع الواحد',
                'third_unit_item_number' => 'SQFT-SQM-001',

                // ✅ Default References
                'default_handling_unit_id' => null,
                'default_warehouse_id' => $warehouse ? $warehouse->id : null,

                // ✅ Status and Audit
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
            [
                // ✅ Required System Fields
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch ? $branch->id : null,

                // ✅ Basic Unit Information
                'name' => 'دزينة',
                'code' => 'DOZ',
                'symbol' => 'دزينة',
                'description' => 'وحدة العد بالدزينة (12 قطعة) - وحدة عد تجارية',
                'decimal_places' => 0,

                // ✅ Balance Unit
                'balance_unit' => 'piece',
                'custom_balance_unit' => null,

                // ✅ Dimensions
                'length' => null,
                'width' => null,
                'height' => null,
                'quantity_factor' => 12.0000,

                // ✅ Second Unit (Pieces conversion)
                'second_unit' => 'piece',
                'custom_second_unit' => null,
                'second_unit_contains' => 'all',
                'custom_second_unit_contains' => 'قطعة',
                'second_unit_content' => '12 قطعة في الدزينة الواحدة',
                'second_unit_item_number' => 'PCS-DOZ-001',

                // ✅ Third Unit (Half dozen conversion)
                'third_unit' => 'piece',
                'custom_third_unit' => 'نصف دزينة',
                'third_unit_contains' => 'all',
                'custom_third_unit_contains' => 'نصف دزينة',
                'third_unit_content' => '2 نصف دزينة في الدزينة الواحدة',
                'third_unit_item_number' => 'HDOZ-DOZ-001',

                // ✅ Default References
                'default_handling_unit_id' => null,
                'default_warehouse_id' => $warehouse ? $warehouse->id : null,

                // ✅ Status and Audit
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],

            // ✅ Additional comprehensive unit - Cubic Meter
            [
                // ✅ Required System Fields
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch ? $branch->id : null,

                // ✅ Basic Unit Information
                'name' => 'متر مكعب',
                'code' => 'CBM',
                'symbol' => 'م³',
                'description' => 'وحدة الحجم بالمتر المكعب - وحدة قياس الحجم والسعة',
                'decimal_places' => 3,

                // ✅ Balance Unit
                'balance_unit' => 'piece',
                'custom_balance_unit' => 'متر مكعب',

                // ✅ Dimensions (volume measurement)
                'length' => 1.00,
                'width' => 1.00,
                'height' => 1.00,
                'quantity_factor' => 1.0000,

                // ✅ Second Unit (Liter conversion)
                'second_unit' => 'liter',
                'custom_second_unit' => null,
                'second_unit_contains' => 'all',
                'custom_second_unit_contains' => 'لتر',
                'second_unit_content' => '1000 لتر في المتر المكعب الواحد',
                'second_unit_item_number' => 'LTR-CBM-001',

                // ✅ Third Unit (Cubic foot conversion)
                'third_unit' => 'piece',
                'custom_third_unit' => 'قدم مكعب',
                'third_unit_contains' => 'all',
                'custom_third_unit_contains' => 'قدم مكعب',
                'third_unit_content' => '35.314 قدم مكعب في المتر المكعب الواحد',
                'third_unit_item_number' => 'CBFT-CBM-001',

                // ✅ Default References
                'default_handling_unit_id' => null,
                'default_warehouse_id' => $warehouse ? $warehouse->id : null,

                // ✅ Status and Audit
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ],
        ];

        $createdUnits = [];

        foreach ($units as $unitData) {
            // ✅ Clean and validate data before insertion
            $cleanedData = $this->cleanUnitData($unitData);

            $unit = Unit::firstOrCreate([
                'company_id' => $cleanedData['company_id'],
                'code' => $cleanedData['code']
            ], $cleanedData);

            $createdUnits[$cleanedData['code']] = $unit;
        }

        // ✅ Now update default_handling_unit_id with references to created units
        $this->updateDefaultHandlingUnits($createdUnits);

        $this->command->info('✅ Units seeded successfully with ALL columns populated including branch_id and default_handling_unit_id!');
    }

    /**
     * ✅ Clean and validate unit data before insertion
     */
    private function cleanUnitData(array $unitData): array
    {
        // Handle enum fields - set to null if empty
        $enumFields = ['second_unit', 'third_unit'];
        foreach ($enumFields as $field) {
            if (isset($unitData[$field]) && empty($unitData[$field])) {
                $unitData[$field] = null;
            }
        }

        // Handle string fields with default values
        $stringFieldsWithDefaults = [
            'second_unit_contains' => 'all',
            'third_unit_contains' => 'all',
        ];

        foreach ($stringFieldsWithDefaults as $field => $default) {
            if (!isset($unitData[$field]) || $unitData[$field] === null) {
                $unitData[$field] = $default;
            }
        }

        // Handle nullable string fields
        $nullableStringFields = [
            'custom_second_unit', 'custom_second_unit_contains',
            'second_unit_content', 'second_unit_item_number',
            'custom_third_unit', 'custom_third_unit_contains',
            'third_unit_content', 'third_unit_item_number'
        ];

        foreach ($nullableStringFields as $field) {
            if (!isset($unitData[$field])) {
                $unitData[$field] = null;
            }
        }

        return $unitData;
    }

    /**
     * ✅ Update default_handling_unit_id with references to created units
     */
    private function updateDefaultHandlingUnits(array $createdUnits): void
    {
        // Set logical default handling units
        $updates = [
            'KG' => 'PCS',    // Kilogram's default handling unit is Piece
            'LTR' => 'PCS',   // Liter's default handling unit is Piece
            'TON' => 'KG',    // Ton's default handling unit is Kilogram
            'CTN' => 'PCS',   // Carton's default handling unit is Piece
            'MTR' => 'PCS',   // Meter's default handling unit is Piece
            'SQM' => 'MTR',   // Square Meter's default handling unit is Meter
            'DOZ' => 'PCS',   // Dozen's default handling unit is Piece
            'CBM' => 'LTR',   // Cubic Meter's default handling unit is Liter
        ];

        foreach ($updates as $unitCode => $defaultHandlingCode) {
            if (isset($createdUnits[$unitCode]) && isset($createdUnits[$defaultHandlingCode])) {
                $createdUnits[$unitCode]->update([
                    'default_handling_unit_id' => $createdUnits[$defaultHandlingCode]->id
                ]);

                $this->command->info("✅ Updated {$unitCode} default handling unit to {$defaultHandlingCode}");
            }
        }
    }
}
