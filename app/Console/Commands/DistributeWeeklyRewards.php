<?php

namespace App\Console\Commands;

use App\Services\ActivityLogger;
use App\Services\DriverRewardService;
use Illuminate\Console\Command;

class DistributeWeeklyRewards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rewards:distribute {week?} {year?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'توزيع المكافآت الأسبوعية للسائقين / Distribute weekly rewards to drivers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $week = $this->argument('week');
        $year = $this->argument('year');

        $this->info('🚀 بدء عملية توزيع المكافآت الأسبوعية...');
        $this->info('Starting weekly rewards distribution...');
        $this->newLine();

        try {
            $rewardService = new DriverRewardService();
            $distribution = $rewardService->distributeWeeklyRewards($week, $year);

            // Log the distribution
            ActivityLogger::logRewardDistribution($distribution);

            // Display results
            $this->displayResults($distribution);

            $this->newLine();
            $this->info('✅ تم توزيع المكافآت بنجاح!');
            $this->info('✅ Rewards distributed successfully!');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ خطأ في توزيع المكافآت: ' . $e->getMessage());
            $this->error('❌ Error distributing rewards: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * Display distribution results
     */
    private function displayResults(array $distribution): void
    {
        $this->table(
            ['المعلومات / Info', 'القيمة / Value'],
            [
                ['الأسبوع / Week', $distribution['week_number']],
                ['السنة / Year', $distribution['year']],
                ['من / From', $distribution['week_start']],
                ['إلى / To', $distribution['week_end']],
                ['أرباح التطبيق / App Profits', number_format($distribution['total_app_profits'], 2) . ' YER'],
                ['الحد الأقصى للمكافآت / Max Reward Pool', number_format($distribution['max_reward_pool'], 2) . ' YER'],
                ['المكافآت المحسوبة / Calculated Rewards', number_format($distribution['total_calculated_rewards'], 2) . ' YER'],
                ['نسبة التوزيع / Distribution Ratio', $distribution['distribution_ratio']],
                ['المكافآت الموزعة / Distributed', number_format($distribution['total_distributed'], 2) . ' YER'],
                ['عدد السائقين / Drivers Count', $distribution['drivers_count']],
            ]
        );

        if (!empty($distribution['rewards'])) {
            $this->newLine();
            $this->info('📊 تفاصيل مكافآت السائقين / Driver Rewards Details:');
            $this->newLine();

            $rewardsTable = [];
            foreach ($distribution['rewards'] as $reward) {
                $rewardsTable[] = [
                    $reward['driver_id'],
                    $reward['total_trips'],
                    number_format($reward['calculated_reward'], 2) . ' YER',
                    number_format($reward['actual_reward'], 2) . ' YER',
                ];
            }

            $this->table(
                ['السائق / Driver ID', 'الرحلات / Trips', 'المحسوبة / Calculated', 'الفعلية / Actual'],
                $rewardsTable
            );
        }
    }
}
