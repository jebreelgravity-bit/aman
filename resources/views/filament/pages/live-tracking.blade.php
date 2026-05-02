<x-filament-panels::page>
    <div class="space-y-4">
        {{-- Header Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-success-500">{{ $drivers->where('status', 'available')->count() }}</div>
                    <div class="text-sm text-gray-500">متاح الآن</div>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-info-500">{{ $drivers->where('status', 'in_trip')->count() }}</div>
                    <div class="text-sm text-gray-500">في رحلة</div>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-warning-500">{{ $trips->count() }}</div>
                    <div class="text-sm text-gray-500">رحلات جارية</div>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-primary-500">{{ $drivers->count() }}</div>
                    <div class="text-sm text-gray-500">إجمالي السائقين النشطين</div>
                </div>
            </x-filament::section>
        </div>

        {{-- Map Container --}}
        <x-filament::section>
            <x-slot name="heading">
                🗺️ خريطة التتبع الحي — صنعاء
            </x-slot>
            <div id="map" style="height: 600px; width: 100%; border-radius: 12px; z-index: 1;"></div>
        </x-filament::section>

        {{-- Active Trips Table --}}
        <x-filament::section>
            <x-slot name="heading">
                🚗 الرحلات الجارية الآن
            </x-slot>
            @if($trips->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b text-gray-500">
                            <th class="py-2 px-3 text-right">#</th>
                            <th class="py-2 px-3 text-right">السائق</th>
                            <th class="py-2 px-3 text-right">العميل</th>
                            <th class="py-2 px-3 text-right">من</th>
                            <th class="py-2 px-3 text-right">إلى</th>
                            <th class="py-2 px-3 text-right">الحالة</th>
                            <th class="py-2 px-3 text-right">الفئة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trips as $trip)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-3">{{ $trip['id'] }}</td>
                            <td class="py-2 px-3">{{ $trip['driver_name'] }}</td>
                            <td class="py-2 px-3">{{ $trip['customer_name'] }}</td>
                            <td class="py-2 px-3">{{ $trip['pickup_address'] }}</td>
                            <td class="py-2 px-3">{{ $trip['dropoff_address'] }}</td>
                            <td class="py-2 px-3">
                                @if($trip['status'] === 'started')
                                    <span class="text-info-500 font-bold">🚗 جارية</span>
                                @else
                                    <span class="text-warning-500 font-bold">⏳ مقبولة</span>
                                @endif
                            </td>
                            <td class="py-2 px-3">
                                @if($trip['category'] === 'economy') 🚗 توفير
                                @elseif($trip['category'] === 'vip') 👑 VIP
                                @elseif($trip['category'] === 'bus') 🚌 باص
                                @else {{ $trip['category'] }}
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center text-gray-400 py-8">
                <div class="text-4xl mb-2">🛑</div>
                <div>لا توجد رحلات جارية حالياً</div>
            </div>
            @endif
        </x-filament::section>
    </div>

    {{-- Leaflet CSS/JS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const map = L.map('map').setView([{{ $default_lat }}, {{ $default_lng }}], {{ $default_zoom }});

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(map);

            // Driver markers
            const drivers = @js($drivers);
            drivers.forEach(driver => {
                const color = driver.status === 'available' ? '#22c55e' : '#3b82f6';
                const statusText = driver.status === 'available' ? '✅ متاح' : '🚗 في رحلة';

                const icon = L.divIcon({
                    className: 'custom-marker',
                    html: `<div style="background:${color};color:white;border-radius:50%;width:36px;height:36px;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:bold;box-shadow:0 2px 8px rgba(0,0,0,0.3);border:2px solid white;">🚕</div>`,
                    iconSize: [36, 36],
                    iconAnchor: [18, 18],
                });

                L.marker([driver.latitude, driver.longitude], { icon })
                    .addTo(map)
                    .bindPopup(`
                        <div style="direction:rtl;text-align:right;min-width:180px">
                            <strong>${driver.name}</strong><br/>
                            <span>${statusText}</span><br/>
                            <span style="color:#888">${driver.phone}</span>
                        </div>
                    `);
            });

            // Trip routes
            const trips = @js($trips);
            trips.forEach(trip => {
                if (trip.pickup_latitude && trip.dropoff_latitude) {
                    // Pickup marker
                    L.marker([trip.pickup_latitude, trip.pickup_longitude], {
                        icon: L.divIcon({
                            className: 'custom-marker',
                            html: `<div style="background:#f59e0b;color:white;border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-size:14px;box-shadow:0 2px 6px rgba(0,0,0,0.3);">📍</div>`,
                            iconSize: [28, 28],
                            iconAnchor: [14, 14],
                        })
                    }).addTo(map).bindPopup(`<div style="direction:rtl"><strong>من:</strong> ${trip.pickup_address}</div>`);

                    // Dropoff marker
                    L.marker([trip.dropoff_latitude, trip.dropoff_longitude], {
                        icon: L.divIcon({
                            className: 'custom-marker',
                            html: `<div style="background:#ef4444;color:white;border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-size:14px;box-shadow:0 2px 6px rgba(0,0,0,0.3);">🏁</div>`,
                            iconSize: [28, 28],
                            iconAnchor: [14, 14],
                        })
                    }).addTo(map).bindPopup(`<div style="direction:rtl"><strong>إلى:</strong> ${trip.dropoff_address}</div>`);

                    // Route line
                    L.polyline([
                        [trip.pickup_latitude, trip.pickup_longitude],
                        [trip.dropoff_latitude, trip.dropoff_longitude]
                    ], { color: '#3b82f6', weight: 3, dashArray: '10, 5' }).addTo(map);
                }
            });
        });
    </script>
</x-filament-panels::page>
