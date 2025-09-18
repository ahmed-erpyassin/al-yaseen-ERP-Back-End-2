<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['title_ar' => 'شركة فردية', 'title_en' => 'Sole Proprietorship'],
            ['title_ar' => 'شركة تضامن', 'title_en' => 'Partnership'],
            ['title_ar' => 'شركة قابضة', 'title_en' => 'Holding Company'],
            ['title_ar' => 'فرع شركة أجنبية', 'title_en' => 'Branch of Foreign Company'],
            ['title_ar' => 'شركة مساهمة عامة', 'title_en' => 'Public Joint Stock Company'],
            ['title_ar' => 'شركة ذات مسؤولية محدودة (ذ.م.م)', 'title_en' => 'Limited Liability Company (LLC)'],
            ['title_ar' => 'شركة توصية بسيطة', 'title_en' => 'Simple Limited Partnership'],
            ['title_ar' => 'شركة مساهمة مقفلة', 'title_en' => 'Closed Joint Stock Company'],
            ['title_ar' => 'شركة مهنية', 'title_en' => 'Professional Company'],
        ];

        foreach ($types as $type) {
            DB::table('work_types')->insert([
                'title_en' => $type['title_en'],
                'title_ar' => $type['title_ar'],
                'status'  => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
