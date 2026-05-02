<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'user_id',
        'trip_id',
        'driver_id',
        'category',
        'priority',
        'subject',
        'description',
        'attachments',
        'status',
        'assigned_to',
        'admin_notes',
        'resolution',
        'first_response_at',
        'resolved_at',
        'response_time_minutes',
    ];

    protected $casts = [
        'attachments' => 'array',
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($complaint) {
            $complaint->ticket_number = 'TKT-' . strtoupper(uniqid());
        });

        static::updating(function ($complaint) {
            if ($complaint->isDirty('status') && $complaint->status === 'resolved') {
                $complaint->resolved_at = now();
                $complaint->response_time_minutes = $complaint->created_at->diffInMinutes(now());
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
