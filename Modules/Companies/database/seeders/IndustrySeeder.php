<?php

namespace Modules\Companies\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Companies\Models\BusinessType;
use Modules\Companies\Models\Industry;

class IndustrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $industries = [
            [
                'name' => 'تكنولوجيا المعلومات',
                'name_en' => 'Information Technology',
                'description' => 'شركات تعمل في مجال تطوير البرمجيات، خدمات الإنترنت، تكنولوجيا المعلومات، إلخ.'
            ],
            [
                'name' => 'الرعاية الصحية',
                'name_en' => 'Healthcare',
                'description' => 'مستشفيات، عيادات، شركات أدوية، وشركات تكنولوجيا طبية.'
            ],
            [
                'name' => 'التعليم',
                'name_en' => 'Education',
                'description' => 'مدارس، جامعات، منصات تعليمية، ومؤسسات تدريبية.'
            ],
            [
                'name' => 'التمويل',
                'name_en' => 'Finance',
                'description' => 'بنوك، شركات تأمين، شركات استثمار، وشركات خدمات مالية.'
            ],
            [
                'name' => 'التصنيع',
                'name_en' => 'Manufacturing',
                'description' => 'شركات تصنيع المنتجات الصناعية، الإلكترونيات، السيارات، والسلع الاستهلاكية.'
            ],
            [
                'name' => 'التجزئة',
                'name_en' => 'Retail',
                'description' => 'محلات بيع بالتجزئة، متاجر إلكترونية، وسلاسل متاجر.'
            ],
            [
                'name' => 'الضيافة والسياحة',
                'name_en' => 'Hospitality and Tourism',
                'description' => 'فنادق، وكالات سفر، وشركات تنظيم الفعاليات.'
            ],
            [
                'name' => 'البناء والعقارات',
                'name_en' => 'Construction and Real Estate',
                'description' => 'شركات تطوير عقاري، شركات بناء، ووكلاء عقارات.'
            ],
            [
                'name' => 'الطاقة والموارد الطبيعية',
                'name_en' => 'Energy and Natural Resources',
                'description' => 'شركات نفط وغاز، شركات طاقة متجددة، وشركات تعدين.'
            ],
            [
                'name' => 'الفنون والترفيه',
                'name_en' => 'Arts and Entertainment',
                'description' => 'استوديوهات إنتاج أفلام، شركات موسيقية، ومنصات بث المحتوى.'
            ],
        ];

        // business_types table
        // industry_id
        // name
        // description
        // status

        $business_types = [
            [
                'industry' => 'تكنولوجيا المعلومات',
                'name' => 'تطوير البرمجيات',
                'name_en' => 'Software Development',
                'description' => 'شركات تطوير تطبيقات الويب، تطبيقات الهواتف المحمولة، والبرمجيات المخصصة.'
            ],
            [
                'industry' => 'تكنولوجيا المعلومات',
                'name' => 'خدمات الإنترنت',
                'name_en' => 'Internet Services',
                'description' => 'مزودو خدمات الإنترنت، استضافة المواقع، وخدمات السحابة.'
            ],
            [
                'industry' => 'الرعاية الصحية',
                'name' => 'المستشفيات والعيادات',
                'name_en' => 'Hospitals and Clinics',
                'description' => 'مؤسسات تقديم الرعاية الصحية والخدمات الطبية.'
            ],
            [
                'industry' => 'الرعاية الصحية',
                'name' => 'الشركات الدوائية',
                'name_en' => 'Pharmaceutical Companies',
                'description' => 'شركات تصنيع وتوزيع الأدوية والمستلزمات الطبية.'
            ],
            [
                'industry' => 'التعليم',
                'name' => 'المدارس والجامعات',
                'name_en' => 'Schools and Universities',
                'description' => 'مؤسسات التعليم الأساسي والعالي.'
            ],
            [
                'industry' => 'التعليم',
                'name' => 'المنصات التعليمية',
                'name_en' => 'Educational Platforms',
                'description' => 'منصات التعلم الإلكتروني والتدريب عبر الإنترنت.'
            ],
            [
                'industry' => 'التمويل',
                'name' => 'البنوك والمؤسسات المالية',
                'name_en' => 'Banks and Financial Institutions',
                'description' => 'مؤسسات تقديم الخدمات المصرفية والمالية.'
            ],
            [
                'industry' => 'التمويل',
                'name' => 'شركات التأمين',
                'name_en' => 'Insurance Companies',
                'description' => 'شركات تقديم خدمات التأمين المختلفة.'
            ],
            [
                'industry' => 'التصنيع',
                'name' => 'تصنيع الإلكترونيات',
                'name_en' => 'Electronics Manufacturing',
                'description' => 'شركات تصنيع الأجهزة الإلكترونية والرقمية.'
            ],
            [
                'industry' => 'التصنيع',
                'name' => 'تصنيع السيارات',
                'name_en' =>  'Automobile Manufacturing',
                'description'  =>  'شركات تصنيع المركبات وقطع الغيار.'
            ],
            [
                'industry' =>  'التجزئة',
                'name'  =>  'المتاجر الإلكترونية',
                'name_en'  =>  'E-commerce Stores',
                'description'  =>  'منصات بيع المنتجات عبر الإنترنت.'
            ],
            [
                'industry' =>  'التجزئة',
                'name'  =>  'محلات البيع بالتجزئة',
                'name_en'  =>  'Retail Stores',
                'description'  =>  'محلات بيع المنتجات للمستهلكين مباشرة.'
            ],
        ];

        foreach ($industries as $industryData) {
            $industry = Industry::create([
                'name' => $industryData['name'],
                'name_en' => $industryData['name_en'],
                'description' => $industryData['description'],
            ]);

            // Create associated business types
            foreach ($business_types as $businessTypeData) {
                if ($businessTypeData['industry'] === $industryData['name']) {
                    BusinessType::create([
                        'industry_id' => $industry->id,
                        'name' => $businessTypeData['name'],
                        'description' => $businessTypeData['description'],
                        'status' => 'active',
                    ]);
                }
            }
        }
    }
}
