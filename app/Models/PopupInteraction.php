<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PopupInteraction extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'popup_id',
        'user_id',
        'action',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function popup()
    {
        return $this->belongsTo(Popup::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
