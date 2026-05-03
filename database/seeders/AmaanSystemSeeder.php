<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;


class AmaanSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@aman.com'],
            [
                'name' => 'Admin',
                'password' => 'admin123456',
                'role' => 'admin',
                'phone' => '+9647700000000',
                'is_active' => true,
            ]
        );

        // 2. Create Sample Drivers
        $drivers = [];
        for ($i = 1; $i <= 5; $i++) {
            $drivers[] = User::firstOrCreate(
                ['email' => "driver{$i}@amaan.com"],
                [
                    'name' => "سائق {$i}",
                    'password' => 'password',
                    'role' => 'driver',
                    'phone' => '+96477000000' . $i,
                    'is_active' => true,
                ]
            );
        }

        // 3. Create Sample Customers
        $customers = [];
        for ($i = 1; $i <= 10; $i++) {
            $customers[] = User::firstOrCreate(
                ['email' => "customer{$i}@amaan.com"],
                [
                    'name' => "عميل {$i}",
                    'password' => 'password',
                    'role' => 'customer',
                    'phone' => '+96477100000' . $i,
                    'is_active' => true,
                ]
            );
        }

        // 4. Create Subscription Plans
        if (\App\Models\SubscriptionPlan::count() == 0) {
            \App\Models\SubscriptionPlan::create([
                'name' => 'باقة الطلاب الشهرية',
                'description' => 'باقة مخفضة للطلاب.',
                'type' => 'student',
                'monthly_price' => 15000,
                'discount_percentage' => 30,
                'free_trips_per_month' => 4,
                'features' => 'خصم 30% على كل الرحلات.',
                'priority_booking' => false,
                'is_active' => true,
            ]);

            \App\Models\SubscriptionPlan::create([
                'name' => 'باقة الموظفين المتميزة',
                'description' => 'باقة لراغبي أولوية الحجز.',
                'type' => 'employee',
                'monthly_price' => 25000,
                'discount_percentage' => 15,
                'free_trips_per_month' => 2,
                'features' => 'أولوية في الحجز.',
                'priority_booking' => true,
                'is_active' => true,
            ]);
        }

        // 5. Create Popups
        if (\App\Models\Popup::count() == 0) {
            \App\Models\Popup::create([
                'title' => 'عرض نهاية الأسبوع!',
                'content' => 'خصم 20% على جميع الرحلات يوم الجمعة.',
                'button_text' => 'احجز الآن',
                'target_audience' => 'customers',
                'display_frequency' => 'daily',
                'priority' => 1,
                'is_active' => true,
                'starts_at' => now(),
                'expires_at' => now()->addDays(7),
                'views_count' => 140,
                'clicks_count' => 52,
            ]);
        }

        // 6. Create Trips and Related Transactions/Ratings/Complaints
        if (\App\Models\Trip::count() == 0) {
            foreach (range(1, 15) as $i) {
                $driver = $drivers[array_rand($drivers)];
                $customer = $customers[array_rand($customers)];

                $price = rand(3000, 15000);
                $trip = \App\Models\Trip::create([
                    'customer_id' => $customer->id,
                    'driver_id' => $driver->id,
                    'category' => 'economy',
                    'status' => 'completed',
                    'pickup_latitude' => 33.3128 + (rand(-100, 100) / 10000),
                    'pickup_longitude' => 44.3615 + (rand(-100, 100) / 10000),
                    'dropoff_latitude' => 33.3128 + (rand(-100, 100) / 10000),
                    'dropoff_longitude' => 44.3615 + (rand(-100, 100) / 10000),
                    'pickup_address' => 'شارع المتنبي, بغداد',
                    'dropoff_address' => 'المنصور, بغداد',
                    'distance_km' => rand(2, 10),
                    'estimated_price' => $price,
                    'final_price' => $price,
                    'created_at' => now()->subDays(rand(0, 10))->subHours(rand(0, 23)),
                ]);

                // Transaction
                \App\Models\Transaction::create([
                    'trip_id' => $trip->id,
                    'driver_id' => $driver->id,
                    'trip_price' => $price,
                    'app_commission_rate' => 20,
                    'app_commission' => $price * 0.20,
                    'driver_earnings' => $price * 0.80,
                    'payment_method' => rand(0, 1) ? 'cash' : 'wallet',
                    'payment_status' => 'completed',
                ]);

                // Rating
                if (rand(0, 1)) {
                    \App\Models\Rating::create([
                        'trip_id' => $trip->id,
                        'customer_id' => $customer->id,
                        'driver_id' => $driver->id,
                        'driver_rating' => rand(3, 5),
                        'service_rating' => rand(3, 5),
                        'comment' => 'خدمة ممتازة وسائق محترم.',
                    ]);
                }
            }
        }

        // 7. Create Driver Rewards
        if (\App\Models\DriverReward::count() == 0) {
            \App\Models\DriverReward::create([
                'driver_id' => $drivers[0]->id,
                'week_number' => now()->weekOfYear,
                'year' => now()->year,
                'week_start_date' => now()->startOfWeek(),
                'week_end_date' => now()->endOfWeek(),
                'total_trips' => 55,
                'total_earnings' => 120000,
                'calculated_reward' => 50000,
                'actual_reward' => 50000,
                'is_distributed' => true,
                'distributed_at' => now(),
                'notes' => 'حقق الهدف الأسبوعي',
            ]);

            \App\Models\DriverReward::create([
                'driver_id' => $drivers[1]->id,
                'week_number' => now()->subWeek()->weekOfYear,
                'year' => now()->year,
                'week_start_date' => now()->subWeek()->startOfWeek(),
                'week_end_date' => now()->subWeek()->endOfWeek(),
                'total_trips' => 30,
                'total_earnings' => 80000,
                'calculated_reward' => 25000,
                'actual_reward' => 25000,
                'is_distributed' => false,
                'notes' => 'تحقيق تصنيف عالي خلال الأسبوع',
            ]);
        }

        // 8. Create Activity Logs
        if (\App\Models\ActivityLog::count() == 0) {
            \App\Models\ActivityLog::create([
                'user_id' => $admin->id,
                'action_type' => 'login',
                'description' => 'تسجيل دخول للنظام.',
                'ip_address' => '127.0.0.1',
                'created_at' => now(),
            ]);
            \App\Models\ActivityLog::create([
                'user_id' => $admin->id,
                'action_type' => 'update_setting',
                'description' => 'تعديل سياسة التسعير.',
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subHours(2),
            ]);
        }

        $this->command->info('✓ تم تهيئة قاعدة البيانات بنجاح بمعلومات ضخمة (رحلات، تقييمات، معاملات، ومكافآت)');
    }
}
