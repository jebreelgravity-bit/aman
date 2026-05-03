<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebAd extends Model
{
    protected $fillable = [
        'title', 'subtitle', 'description', 'image', 'offer_code',
        'discount_percentage', 'bg_color', 'btn_text', 'btn_link',
        'target', 'show_once', 'is_active', 'start_date', 'end_date',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'show_once'  => 'boolean',
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(fn($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', now()))
            ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()));
    }
}
