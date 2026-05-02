<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Popup extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'image_url',
        'button_text',
        'button_url',
        'target_audience',
        'target_cities',
        'display_frequency',
        'priority',
        'is_active',
        'starts_at',
        'expires_at',
        'views_count',
        'clicks_count',
    ];

    protected $casts = [
        'target_cities' => 'array',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function interactions()
    {
        return $this->hasMany(PopupInteraction::class);
    }

    public function getClickThroughRateAttribute(): float
    {
        if ($this->views_count === 0) return 0;
        return round(($this->clicks_count / $this->views_count) * 100, 2);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            });
    }
}
