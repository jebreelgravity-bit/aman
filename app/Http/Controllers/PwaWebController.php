<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PwaWebController extends Controller
{
    public function home()      { return view('pwa.app'); }
    public function login()     { return view('pwa.app'); }
    public function tracking($tripId)  { return view('pwa.app', ['initialTrip' => $tripId]); }
    public function subscriptions()    { return view('pwa.app'); }
    public function longDistance()     { return view('pwa.app'); }
    public function account()          { return view('pwa.app'); }
    public function offline()          { return view('pwa.offline'); }

    public function manifest()
    {
        $manifest = [
            'name'             => 'أمان - خدمة النقل',
            'short_name'       => 'أمان',
            'description'      => 'احجز رحلتك بأمان وسرعة',
            'start_url'        => '/app/',
            'display'          => 'standalone',
            'background_color' => '#0d0d0d',
            'theme_color'      => '#FFD700',
            'orientation'      => 'portrait-primary',
            'lang'             => 'ar',
            'dir'              => 'rtl',
            'icons'            => [
                ['src' => '/images/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png'],
                ['src' => '/images/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png'],
            ],
            'screenshots' => [],
            'categories'  => ['transportation', 'travel'],
        ];

        return response()->json($manifest)
            ->header('Content-Type', 'application/manifest+json');
    }

    public function serviceWorker()
    {
        $sw = <<<'JS'
const CACHE_NAME = 'aman-pwa-v1';
const STATIC_ASSETS = [
    '/app/',
    '/app/offline',
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(STATIC_ASSETS))
    );
    self.skipWaiting();
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') return;
    if (event.request.url.includes('/api/')) {
        // للـ API: network first
        event.respondWith(
            fetch(event.request).catch(() => new Response(JSON.stringify({error: 'offline'}), {
                headers: {'Content-Type': 'application/json'}
            }))
        );
        return;
    }
    // باقي الملفات: cache first
    event.respondWith(
        caches.match(event.request).then(cached => {
            return cached || fetch(event.request).catch(() => caches.match('/app/offline'));
        })
    );
});
JS;

        return response($sw)->header('Content-Type', 'application/javascript');
    }
}
