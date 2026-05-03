<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;

use Filament\Models\Contracts\HasAvatar;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'avatar_url',
        'password',
        'role',
        'phone',
        'is_active',
        'latitude',
        'longitude',
        // حقول الهوية والبيانات الشخصية
        'national_id',
        'national_id_front',
        'national_id_back',
        'date_of_birth',
        'address',
        'city',
        'gender',
        // حقول المركبة (للسائقين)
        'vehicle_plate',
        'vehicle_type',
        'vehicle_grade',
        'vehicle_model',
        'vehicle_color',
        'vehicle_year',
        'vehicle_photo',
    ];

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url ? Storage::url($this->avatar_url) : null;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
            'latitude'          => 'decimal:8',
            'longitude'         => 'decimal:8',
            'date_of_birth'     => 'date',
        ];
    }

    /**
     * Get trips as customer
     */
    public function customerTrips()
    {
        return $this->hasMany(Trip::class, 'customer_id');
    }

    /**
     * Alias for customerTrips (used by analytics)
     */
    public function tripsAsCustomer()
    {
        return $this->hasMany(Trip::class, 'customer_id');
    }

    /**
     * Get trips as driver
     */
    public function driverTrips()
    {
        return $this->hasMany(Trip::class, 'driver_id');
    }

    /**
     * Alias for driverTrips (used by analytics)
     */
    public function tripsAsDriver()
    {
        return $this->hasMany(Trip::class, 'driver_id');
    }

    /**
     * Get transactions as driver
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'driver_id');
    }

    /**
     * Get driver rewards
     */
    public function rewards()
    {
        return $this->hasMany(DriverReward::class, 'driver_id');
    }

    /**
     * Get activity logs
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Get user wallet
     */
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * Get ratings as driver
     */
    public function driverRatings()
    {
        return $this->hasMany(Rating::class, 'driver_id');
    }

    /**
     * Get driver documents
     */
    public function driverDocuments()
    {
        return $this->hasMany(DriverDocument::class, 'driver_id');
    }

    /**
     * Get complaints as customer
     */
    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'user_id');
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is driver
     */
    public function isDriver(): bool
    {
        return $this->role === 'driver';
    }

    /**
     * Check if user is customer
     */
    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    /**
     * Scope: Filter by role
     */
    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope: Active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
