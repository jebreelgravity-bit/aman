<x-filament-widgets::widget>
    <x-filament::section>
        {{-- العنوان الرئيسي --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    ⭐ لوحة تقييمات السائقين
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ $totalRatings }} تقييم إجمالي • {{ $todayRatings }} اليوم
                </p>
            </div>
            <div class="text-center px-4 py-2 rounded-xl {{ $overallAvg >= 4 ? 'bg-green-50 dark:bg-green-900/20' : ($overallAvg >= 3 ? 'bg-yellow-50 dark:bg-yellow-900/20' : 'bg-red-50 dark:bg-red-900/20') }}">
                <div class="text-3xl font-bold {{ $overallAvg >= 4 ? 'text-green-600' : ($overallAvg >= 3 ? 'text-yellow-600' : 'text-red-600') }}">
                    {{ $overallAvg }}
                </div>
                <div class="text-xs text-gray-500">من 5.0</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- توزيع التقييمات --}}
            <div class="space-y-2">
                <h3 class="font-semibold text-gray-700 dark:text-gray-300 text-sm mb-3">📊 توزيع التقييمات</h3>
                @foreach($distribution as $stars => $info)
                    <div class="flex items-center gap-2 text-sm">
                        <span class="w-6 text-center font-medium">{{ $stars }}</span>
                        <span>⭐</span>
                        <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500
                                {{ $stars >= 4 ? 'bg-green-500' : ($stars == 3 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                style="width: {{ $info['percentage'] }}%">
                            </div>
                        </div>
                        <span class="w-12 text-left text-gray-500 text-xs">{{ $info['count'] }} ({{ $info['percentage'] }}%)</span>
                    </div>
                @endforeach
            </div>

            {{-- أفضل 5 سائقين --}}
            <div>
                <h3 class="font-semibold text-gray-700 dark:text-gray-300 text-sm mb-3">🏆 أفضل السائقين</h3>
                @forelse($topDrivers as $index => $driver)
                    <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-700' : '' }}">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold
                                {{ $index === 0 ? 'bg-yellow-100 text-yellow-700' : ($index === 1 ? 'bg-gray-100 text-gray-700' : 'bg-orange-50 text-orange-700') }}">
                                {{ $index + 1 }}
                            </span>
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $driver->name }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="text-sm font-bold text-green-600">{{ $driver->avg_rating }}</span>
                            <span class="text-xs text-gray-400">({{ $driver->total_ratings }})</span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">لا توجد بيانات كافية</p>
                @endforelse
            </div>

            {{-- الوسوم + التقييمات المنخفضة --}}
            <div class="space-y-4">
                {{-- وسوم --}}
                <div>
                    <h3 class="font-semibold text-gray-700 dark:text-gray-300 text-sm mb-3">🏷️ الوسوم الأكثر تكراراً</h3>
                    <div class="flex flex-wrap gap-2">
                        @forelse($topTags as $tag => $count)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium
                                {{ $count > 3 ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                                {{ $tag }}
                                <span class="text-[10px] opacity-70">({{ $count }})</span>
                            </span>
                        @empty
                            <p class="text-sm text-gray-400">لا توجد وسوم بعد</p>
                        @endforelse
                    </div>
                </div>

                {{-- تقييمات منخفضة --}}
                @if($lowCount > 0)
                    <div>
                        <h3 class="font-semibold text-red-600 text-sm mb-2">⚠️ تقييمات منخفضة ({{ $lowCount }})</h3>
                        @foreach($lowRatings as $rating)
                            <div class="text-xs py-1.5 {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-700' : '' }}">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">{{ $rating->driver?->name ?? '—' }}</span>
                                    <span class="text-red-500 font-bold">{{ str_repeat('⭐', $rating->driver_rating) }}</span>
                                </div>
                                @if($rating->comment)
                                    <p class="text-gray-400 mt-0.5 truncate">{{ Str::limit($rating->comment, 40) }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
