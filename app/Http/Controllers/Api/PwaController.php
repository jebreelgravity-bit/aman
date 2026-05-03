<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebBanner;
use App\Models\WebAd;
use App\Models\LongDistanceTrip;
use App\Models\PricingSetting;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PwaController extends Controller
{
    /**
     * جلب الإعلانات والبانرات النشطة
     * GET /api/pwa/banners
     */
    public function banners()
    {
        $banners = Cache::remember('web_banners_active', 300, function () {
            return WebBanner::active()->get()->map(fn($b) => [
                'id'         => $b->id,
                'title'      => $b->title,
                'subtitle'   => $b->subtitle,
                'image'      => $b->image ? url('storage/' . $b->image) : null,
                'link'       => $b->link,
                'bg_color'   => $b->bg_color,
                'text_color' => $b->text_color,
                'type'       => $b->type,
                'position'   => $b->position,
            ]);
        });

        return response()->json($banners);
    }

    /**
     * جلب الإعلانات المنبثقة (Popup)
     * GET /api/pwa/popup-ad
     */
    public function popupAd(Request $request)
    {
        $ad = WebAd::active()
            ->where(fn($q) => $q->where('target', 'all')
                ->when(auth()->check(), fn($q2) => $q2->orWhere('target', 'loyal'))
            )
            ->inRandomOrder()
            ->first();

        if (! $ad) {
            return response()->json(null);
        }

        return response()->json([
            'id'                  => $ad->id,
            'title'               => $ad->title,
            'subtitle'            => $ad->subtitle,
            'description'         => $ad->description,
            'image'               => $ad->image ? url('storage/' . $ad->image) : null,
            'offer_code'          => $ad->offer_code,
            'discount_percentage' => $ad->discount_percentage,
            'bg_color'            => $ad->bg_color,
            'btn_text'            => $ad->btn_text,
            'btn_link'            => $ad->btn_link,
            'show_once'           => $ad->show_once,
        ]);
    }

    /**
     * جلب الأسعار من لوحة التحكم
     * GET /api/pwa/pricing
     */
    public function pricing()
    {
        $settings = Cache::remember('pricing_settings_pwa', 600, function () {
            return PricingSetting::all()->keyBy('key');
        });

        return response()->json([
            'economy' => [
                'label'        => 'توفير',
                'icon'         => '🚕',
                'base_fare'    => (float) ($settings['economy_base_fare']->value ?? 300),
                'per_km'       => (float) ($settings['economy_per_km']->value ?? 150),
                'description'  => 'السيارة الاقتصادية المريحة',
            ],
            'vip' => [
                'label'        => 'VIP',
                'icon'         => '🌟',
                'base_fare'    => (float) ($settings['vip_base_fare']->value ?? 500),
                'per_km'       => (float) ($settings['vip_per_km']->value ?? 220),
                'description'  => 'تجربة فاخرة مع سائق محترف',
            ],
            'bus' => [
                'label'        => 'باص',
                'icon'         => '🚌',
                'base_fare'    => (float) ($settings['bus_base_fare']->value ?? 400),
                'per_km'       => (float) ($settings['bus_per_km']->value ?? 180),
                'description'  => 'مناسب للمجموعات والعائلات',
            ],
        ]);
    }

    /**
     * جلب الرحلات الطويلة
     * GET /api/pwa/long-distance-trips
     */
    public function longDistanceTrips(Request $request)
    {
        $origin = $request->query('origin');
        $dest   = $request->query('destination');

        $trips = LongDistanceTrip::active()
            ->when($origin, fn($q) => $q->where('origin_city', 'like', "%{$origin}%"))
            ->when($dest,   fn($q) => $q->where('destination_city', 'like', "%{$dest}%"))
            ->with(['drivers' => fn($q) => $q->select('users.id', 'users.name', 'users.vehicle_model', 'users.vehicle_plate')])
            ->get()
            ->map(fn($t) => [
                'id'               => $t->id,
                'name'             => $t->name,
                'origin_city'      => $t->origin_city,
                'destination_city' => $t->destination_city,
                'base_price'       => (float) $t->base_price,
                'vehicle_type'     => $t->vehicle_type,
                'max_passengers'   => $t->max_passengers,
                'departure_time'   => $t->departure_time,
                'frequency'        => $t->frequency,
                'drivers'          => $t->drivers->map(fn($d) => [
                    'id'            => $d->id,
                    'name'          => $d->name,
                    'vehicle_model' => $d->vehicle_model,
                    'vehicle_plate' => $d->vehicle_plate,
                    'is_primary'    => (bool) $d->pivot->is_primary,
                ]),
            ]);

        return response()->json($trips);
    }

    /**
     * رحلات المستخدم السابقة
     * GET /api/pwa/my-trips
     */
    public function myTrips(Request $request)
    {
        $user = $request->user();
        if (! $user) return response()->json(['error' => 'غير مصرح'], 401);

        $trips = Trip::where('customer_id', $user->id)
            ->with(['driver:id,name,vehicle_model,vehicle_plate,vehicle_grade'])
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn($t) => [
                'id'           => $t->id,
                'status'       => $t->status,
                'trip_type'    => $t->trip_type ?? 'economy',
                'fare'         => (float) $t->fare,
                'pickup'       => $t->pickup_address ?? '',
                'destination'  => $t->destination_address ?? '',
                'created_at'   => $t->created_at?->toDateTimeString(),
                'driver'       => $t->driver ? [
                    'name'          => $t->driver->name,
                    'vehicle_model' => $t->driver->vehicle_model,
                ] : null,
            ]);

        return response()->json($trips);
    }
}
