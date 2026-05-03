<?php

namespace Database\Seeders;

use App\Models\BudgetCategory;
use Illuminate\Database\Seeder;

class AccountingSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // فئات المصروفات
            ['name' => 'إعلانات',        'type' => 'expense', 'description' => 'حملات تسويقية، سوشيال ميديا، جوجل أدز',           'icon' => 'heroicon-o-megaphone',          'color' => '#ef4444', 'sort_order' => 1],
            ['name' => 'لواصق',          'type' => 'expense', 'description' => 'لواصق السيارات، مواد التمييز البصري',              'icon' => 'heroicon-o-tag',                'color' => '#f97316', 'sort_order' => 2],
            ['name' => 'لوحات',          'type' => 'expense', 'description' => 'لوحات إعلانية، فلكسات، بانرات خارجية',            'icon' => 'heroicon-o-presentation-chart-bar','color' => '#eab308', 'sort_order' => 3],
            ['name' => 'مصاريف المكتب', 'type' => 'expense', 'description' => 'إيجار، كهرباء، إنترنت، قرطاسية، مستلزمات',       'icon' => 'heroicon-o-building-office-2',  'color' => '#3b82f6', 'sort_order' => 4],
            ['name' => 'الرواتب',        'type' => 'expense', 'description' => 'رواتب الموظفين والمدراء',                         'icon' => 'heroicon-o-users',              'color' => '#6366f1', 'sort_order' => 5],
            ['name' => 'صيانة تقنية',   'type' => 'expense', 'description' => 'صيانة الخوادم، الدومين، الاستضافة السحابية',      'icon' => 'heroicon-o-wrench-screwdriver', 'color' => '#8b5cf6', 'sort_order' => 6],
            ['name' => 'شؤون قانونية', 'type' => 'expense', 'description' => 'رسوم الترخيص، المستشار القانوني، التراخيص',       'icon' => 'heroicon-o-scale',              'color' => '#ec4899', 'sort_order' => 7],
            ['name' => 'تدريب وتطوير', 'type' => 'expense', 'description' => 'دورات تدريبية، ورش عمل للموظفين',                 'icon' => 'heroicon-o-academic-cap',       'color' => '#14b8a6', 'sort_order' => 8],
            ['name' => 'مصاريف أخرى',  'type' => 'expense', 'description' => 'مصروفات متنوعة لا تندرج ضمن الفئات الأخرى',      'icon' => 'heroicon-o-ellipsis-horizontal', 'color' => '#6b7280', 'sort_order' => 9],

            // فئات الإيرادات
            ['name' => 'عمولة الرحلات', 'type' => 'income', 'description' => 'عمولة التطبيق 20% من كل رحلة مكتملة',             'icon' => 'heroicon-o-truck',              'color' => '#22c55e', 'sort_order' => 10],
            ['name' => 'الاشتراكات',    'type' => 'income', 'description' => 'رسوم الاشتراكات الشهرية للعملاء',                 'icon' => 'heroicon-o-credit-card',        'color' => '#10b981', 'sort_order' => 11],
            ['name' => 'إيرادات أخرى', 'type' => 'income', 'description' => 'مصادر إيرادات إضافية متنوعة',                     'icon' => 'heroicon-o-banknotes',          'color' => '#059669', 'sort_order' => 12],
        ];

        foreach ($categories as $cat) {
            BudgetCategory::firstOrCreate(['name' => $cat['name']], $cat);
        }

        $this->command->info('✓ تم إنشاء فئات الميزانية الافتراضية (' . count($categories) . ' فئة)');
    }
}
