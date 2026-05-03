<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebBanner extends Model
{
    protected $fillable = [
        'title', 'subtitle', 'image', 'link', 'bg_color', 'text_color',
        'type', 'position', 'is_active', 'start_date', 'end_date', 'sort_order',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(fn($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', now()))
            ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
            ->orderBy('sort_order');
    }
}
