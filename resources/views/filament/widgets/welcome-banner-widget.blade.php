<div class="relative overflow-hidden rounded-xl p-6 bg-gradient-to-l from-blue-600 via-indigo-600 to-purple-700 text-white shadow-lg">
    {{-- خلفية زخرفية --}}
    <div class="absolute top-0 left-0 w-full h-full opacity-10">
        <svg class="absolute -top-12 -left-12 w-48 h-48" viewBox="0 0 200 200"><circle cx="100" cy="100" r="80" fill="white"/></svg>
        <svg class="absolute -bottom-16 -right-16 w-64 h-64" viewBox="0 0 200 200"><circle cx="100" cy="100" r="90" fill="white"/></svg>
    </div>

    <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold mb-1">{{ $greeting }}، مدير أمان 👋</h2>
            <p class="text-blue-100 text-sm">
                لوحة التحكم — {{ now()->format('Y-m-d') }}
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            <div class="bg-white/15 backdrop-blur-sm rounded-lg px-4 py-2 text-center min-w-[80px]">
                <div class="text-xl font-bold">{{ $todayTrips }}</div>
                <div class="text-xs text-blue-100">رحلات اليوم</div>
            </div>
            <div class="bg-white/15 backdrop-blur-sm rounded-lg px-4 py-2 text-center min-w-[80px]">
                <div class="text-xl font-bold {{ $activeTrips > 0 ? 'animate-pulse' : '' }}">{{ $activeTrips }}</div>
                <div class="text-xs text-blue-100">جارية الآن</div>
            </div>
            <div class="bg-white/15 backdrop-blur-sm rounded-lg px-4 py-2 text-center min-w-[80px]">
                <div class="text-xl font-bold">{{ $onlineDrivers }}</div>
                <div class="text-xs text-blue-100">سائق نشط</div>
            </div>
            @if($urgentComplaints > 0)
                <div class="bg-red-500/40 backdrop-blur-sm rounded-lg px-4 py-2 text-center min-w-[80px] animate-pulse border border-red-400/50">
                    <div class="text-xl font-bold">{{ $urgentComplaints }}</div>
                    <div class="text-xs text-red-100">⚠️ عاجل</div>
                </div>
            @endif
        </div>
    </div>
</div>
