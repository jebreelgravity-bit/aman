<x-filament-panels::page>
    <div class="space-y-6" style="direction:rtl">
        {{-- Retention & Churn --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-filament::section>
                <x-slot name="heading">📊 معدل الاحتفاظ (Retention) — آخر 8 أسابيع</x-slot>
                <div id="retention-chart" style="height:300px"></div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">📉 معدل التسرب (Churn Rate)</x-slot>
                <div class="grid grid-cols-2 gap-4 text-center">
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <div class="text-3xl font-bold text-primary-500">{{ $churn_rate['active_last_30'] }}</div>
                        <div class="text-sm text-gray-500">نشط آخر 30 يوم</div>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <div class="text-3xl font-bold text-warning-500">{{ $churn_rate['active_30_60'] }}</div>
                        <div class="text-sm text-gray-500">نشط 30-60 يوم</div>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <div class="text-3xl font-bold text-danger-500">{{ $churn_rate['churned'] }}</div>
                        <div class="text-sm text-gray-500">متسربين</div>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <div class="text-3xl font-bold {{ $churn_rate['churn_rate'] > 20 ? 'text-danger-500' : 'text-success-500' }}">{{ $churn_rate['churn_rate'] }}%</div>
                        <div class="text-sm text-gray-500">معدل التسرب</div>
                    </div>
                </div>
            </x-filament::section>
        </div>

        {{-- Conversion Funnel --}}
        <x-filament::section>
            <x-slot name="heading">🔄 قمع التحويل (Conversion Funnel)</x-slot>
            <div class="space-y-3">
                @foreach($conversion_funnel as $index => $stage)
                    @php $percentage = $index === 0 ? 100 : ($conversion_funnel[0]['count'] > 0 ? round(($stage['count'] / $conversion_funnel[0]['count']) * 100, 1) : 0) @endphp
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="font-bold">{{ $stage['stage'] }}</span>
                            <span>{{ $stage['count'] }} ({{ $percentage }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-8" style="direction:ltr">
                            <div class="h-8 rounded-full flex items-center justify-end px-3 text-white font-bold text-sm"
                                 style="width:{{ max($percentage, 2) }}%;background:{{ $stage['color'] }}">
                                {{ $stage['count'] }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>

        {{-- Revenue Trend --}}
        <x-filament::section>
            <x-slot name="heading">💰 اتجاه الإيرادات — آخر 30 يوم</x-slot>
            <div id="revenue-chart" style="height:300px"></div>
        </x-filament::section>

        {{-- Category Performance --}}
        <x-filament::section>
            <x-slot name="heading">🚗 أداء الفئات</x-slot>
            @if(count($category_performance) > 0)
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b text-gray-500">
                        <th class="py-2 px-3 text-right">الفئة</th>
                        <th class="py-2 px-3 text-right">عدد الرحلات</th>
                        <th class="py-2 px-3 text-right">متوسط السعر</th>
                        <th class="py-2 px-3 text-right">متوسط المسافة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($category_performance as $cat)
                    <tr class="border-b">
                        <td class="py-2 px-3 font-bold">{{ $cat['category'] }}</td>
                        <td class="py-2 px-3">{{ $cat['trips'] }}</td>
                        <td class="py-2 px-3">{{ number_format($cat['avg_price']) }} ر.ي</td>
                        <td class="py-2 px-3">{{ $cat['avg_distance'] }} كم</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="text-center text-gray-400 py-4">لا توجد بيانات بعد</div>
            @endif
        </x-filament::section>

        {{-- Customer LTV --}}
        <x-filament::section>
            <x-slot name="heading">💎 أعلى 10 عملاء حسب القيمة الدائمة (LTV)</x-slot>
            @if(count($customer_ltv) > 0)
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b text-gray-500">
                        <th class="py-2 px-3 text-right">#</th>
                        <th class="py-2 px-3 text-right">العميل</th>
                        <th class="py-2 px-3 text-right">الرحلات</th>
                        <th class="py-2 px-3 text-right">إجمالي الإنفاق</th>
                        <th class="py-2 px-3 text-right">متوسط/رحلة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customer_ltv as $i => $customer)
                    <tr class="border-b">
                        <td class="py-2 px-3">{{ $i + 1 }}</td>
                        <td class="py-2 px-3 font-bold">{{ $customer['name'] }}</td>
                        <td class="py-2 px-3">{{ $customer['total_trips'] }}</td>
                        <td class="py-2 px-3 text-success-500 font-bold">{{ number_format($customer['total_spent']) }} ر.ي</td>
                        <td class="py-2 px-3">{{ number_format($customer['avg_per_trip']) }} ر.ي</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="text-center text-gray-400 py-4">لا توجد بيانات بعد</div>
            @endif
        </x-filament::section>
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Retention Chart
            const retentionData = @js($retention_data);
            new Chart(document.getElementById('retention-chart'), {
                type: 'line',
                data: {
                    labels: retentionData.map(d => d.label),
                    datasets: [{
                        label: 'مستخدمين جدد',
                        data: retentionData.map(d => d.new_users),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59,130,246,0.1)',
                        fill: true,
                        tension: 0.4,
                    }, {
                        label: 'مستخدمين نشطين',
                        data: retentionData.map(d => d.active_users),
                        borderColor: '#22c55e',
                        backgroundColor: 'rgba(34,197,94,0.1)',
                        fill: true,
                        tension: 0.4,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'top' } },
                    scales: { y: { beginAtZero: true } }
                }
            });

            // Revenue Chart
            const revenueData = @js($revenue_trend);
            new Chart(document.getElementById('revenue-chart'), {
                type: 'bar',
                data: {
                    labels: revenueData.map(d => d.date.slice(5)),
                    datasets: [{
                        label: 'إيرادات العمولة (ر.ي)',
                        data: revenueData.map(d => d.revenue),
                        backgroundColor: 'rgba(59,130,246,0.7)',
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'top' } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        });
    </script>
</x-filament-panels::page>
