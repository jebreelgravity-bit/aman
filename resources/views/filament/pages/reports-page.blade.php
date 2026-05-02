<x-filament-panels::page>
    {{-- ═══════════ ملخص سريع ═══════════ --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="text-3xl font-bold text-blue-600">{{ number_format($totalTrips) }}</div>
            <div class="text-sm text-gray-500 mt-1">إجمالي الرحلات</div>
            <div class="text-xs text-green-500 mt-1">✅ {{ $completionRate }}% مكتملة</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="text-3xl font-bold text-green-600">{{ number_format($totalCommission) }}</div>
            <div class="text-sm text-gray-500 mt-1">إجمالي العمولات (ر.ي)</div>
            <div class="text-xs text-yellow-500 mt-1">⏳ {{ number_format($pendingPayments) }} معلق</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="text-3xl font-bold text-purple-600">{{ $activeDrivers }}/{{ $totalDrivers }}</div>
            <div class="text-sm text-gray-500 mt-1">السائقين (نشط/إجمالي)</div>
            <div class="text-xs text-blue-500 mt-1">👥 {{ number_format($totalCustomers) }} عميل</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="text-3xl font-bold {{ $avgRating >= 4 ? 'text-green-600' : ($avgRating >= 3 ? 'text-yellow-600' : 'text-red-600') }}">{{ $avgRating }} ⭐</div>
            <div class="text-sm text-gray-500 mt-1">متوسط التقييم ({{ $totalRatings }})</div>
            <div class="text-xs {{ $openComplaints > 0 ? 'text-red-500' : 'text-green-500' }} mt-1">
                📝 {{ $openComplaints }} شكوى مفتوحة | {{ $resolutionRate }}% حل
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        {{-- ═══════════ التقرير المالي ═══════════ --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">💰 التقرير المالي</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                    <span class="text-gray-600 dark:text-gray-400">إجمالي الإيرادات</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ number_format($totalRevenue) }} ر.ي</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                    <span class="text-gray-600 dark:text-gray-400">عمولة التطبيق (20%)</span>
                    <span class="font-bold text-green-600">{{ number_format($totalCommission) }} ر.ي</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                    <span class="text-gray-600 dark:text-gray-400">مدفوعات السائقين</span>
                    <span class="font-bold text-blue-600">{{ number_format($totalPayouts) }} ر.ي</span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="text-gray-600 dark:text-gray-400">مدفوعات معلقة</span>
                    <span class="font-bold {{ $pendingPayments > 0 ? 'text-yellow-600' : 'text-green-600' }}">{{ number_format($pendingPayments) }} ر.ي</span>
                </div>
            </div>
        </div>

        {{-- ═══════════ توزيع طرق الدفع ═══════════ --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">💳 طرق الدفع</h3>
            @forelse($paymentMethods as $method => $data)
                <div class="flex justify-between items-center py-2 {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-700' : '' }}">
                    <span class="text-gray-600 dark:text-gray-400">{{ $method }}</span>
                    <div class="text-left">
                        <span class="font-bold text-gray-900 dark:text-white">{{ $data['total'] }} ر.ي</span>
                        <span class="text-xs text-gray-400 mr-2">({{ $data['count'] }} معاملة)</span>
                    </div>
                </div>
            @empty
                <p class="text-gray-400 text-center py-4">لا توجد معاملات مكتملة بعد</p>
            @endforelse
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        {{-- ═══════════ تقرير الرحلات ═══════════ --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">🚗 تقرير الرحلات</h3>
            <div class="grid grid-cols-3 gap-4 mb-4">
                <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                    <div class="text-2xl font-bold text-green-600">{{ $completedTrips }}</div>
                    <div class="text-xs text-gray-500">مكتملة</div>
                </div>
                <div class="text-center p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                    <div class="text-2xl font-bold text-red-600">{{ $cancelledTrips }}</div>
                    <div class="text-xs text-gray-500">ملغاة</div>
                </div>
                <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600">{{ $totalTrips - $completedTrips - $cancelledTrips }}</div>
                    <div class="text-xs text-gray-500">أخرى</div>
                </div>
            </div>

            <h4 class="font-semibold text-sm text-gray-600 dark:text-gray-400 mb-2">توزيع الفئات</h4>
            @foreach($categoryDistribution as $category => $count)
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400 w-16">{{ $category }}</span>
                    <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                        <div class="h-full bg-blue-500 rounded-full" style="width: {{ $totalTrips > 0 ? round(($count / $totalTrips) * 100) : 0 }}%"></div>
                    </div>
                    <span class="text-xs text-gray-500 w-8">{{ $count }}</span>
                </div>
            @endforeach
        </div>

        {{-- ═══════════ أفضل 10 سائقين ═══════════ --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">🏆 أفضل 10 سائقين بالأرباح</h3>
            @forelse($topEarningDrivers as $index => $driver)
                <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-700' : '' }}">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold
                            {{ $index === 0 ? 'bg-yellow-100 text-yellow-700' : ($index === 1 ? 'bg-gray-200 text-gray-700' : ($index === 2 ? 'bg-orange-100 text-orange-700' : 'bg-gray-50 text-gray-500')) }}">
                            {{ $index + 1 }}
                        </span>
                        <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $driver->name }}</span>
                    </div>
                    <div class="text-left">
                        <span class="text-sm font-bold text-green-600">{{ number_format($driver->total_earnings) }} ر.ي</span>
                        <span class="text-xs text-gray-400 mr-1">({{ $driver->total_trips }} رحلة)</span>
                    </div>
                </div>
            @empty
                <p class="text-gray-400 text-center py-4">لا توجد بيانات بعد</p>
            @endforelse
        </div>
    </div>

    {{-- ═══════════ إيرادات آخر 30 يوم (جدول) ═══════════ --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">📈 العمولات اليومية — آخر 30 يوم</h3>
        <div class="flex items-end gap-1 h-32">
            @php
                $maxRevenue = collect($dailyRevenue)->max('revenue') ?: 1;
            @endphp
            @foreach($dailyRevenue as $day)
                <div class="flex-1 flex flex-col items-center group relative">
                    <div class="w-full bg-blue-500 rounded-t opacity-70 hover:opacity-100 transition-opacity cursor-pointer"
                        style="height: {{ ($day['revenue'] / $maxRevenue) * 100 }}%"
                        title="{{ $day['date'] }}: {{ number_format($day['revenue']) }} ر.ي">
                    </div>
                </div>
            @endforeach
        </div>
        <div class="flex justify-between text-xs text-gray-400 mt-2">
            <span>{{ $dailyRevenue[0]['date'] ?? '' }}</span>
            <span>{{ $dailyRevenue[14]['date'] ?? '' }}</span>
            <span>{{ $dailyRevenue[29]['date'] ?? '' }}</span>
        </div>
    </div>
</x-filament-panels::page>
