<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['title_ar' => 'المقاولات والإنشاءات', 'title_en' => 'Contracting & Construction'],
            ['title_ar' => 'التجارة العامة', 'title_en' => 'General Trading'],
            ['title_ar' => 'الزراعة والصناعات الغذائية', 'title_en' => 'Agriculture & Food Industries'],
            ['title_ar' => 'الخدمات اللوجستية و النقل', 'title_en' => 'Logistics & Transportation'],
            ['title_ar' => 'الصناعات التحويلية', 'title_en' => 'Manufacturing'],
            ['title_ar' => 'خدمات تقنية و برمجية', 'title_en' => 'IT & Software Services'],
            ['title_ar' => 'الإستيراد والتصدير', 'title_en' => 'Import & Export'],
            ['title_ar' => 'الخدمات المالية والمحاسبية', 'title_en' => 'Financial & Accounting Services'],
            ['title_ar' => 'الخدمات التعليمية والتدريبية', 'title_en' => 'Education & Training'],
            ['title_ar' => 'الصحة والخدمات الطبية', 'title_en' => 'Healthcare & Medical Services'],
            ['title_ar' => 'العقارات والتطوير العقاري', 'title_en' => 'Real Estate & Development'],
            ['title_ar' => 'الإعلام والتسويق والإعلان', 'title_en' => 'Media, Marketing & Advertising'],
            ['title_ar' => 'السياحة والسفر', 'title_en' => 'Tourism & Travel'],
            ['title_ar' => 'الطاقة والبيئة', 'title_en' => 'Energy & Environment'],
            ['title_ar' => 'الأمن والحراسات', 'title_en' => 'Security & Guarding'],
            ['title_ar' => 'خدمات أخرى', 'title_en' => 'Other Services'],
        ];

        DB::table('company_types')->insert($types);
    }
}
