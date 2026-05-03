<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverDocument extends Model
{
    protected $fillable = [
        'driver_id',
        'document_type',
        'file_path',
        'expiry_date',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
