<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Channel for available drivers to listen to new trip requests
Broadcast::channel('drivers.available', function ($user) {
    return $user->role === 'driver' && $user->is_active;
});

// Channel for a specific customer to get updates on their trips
Broadcast::channel('customer.{customerId}', function ($user, $customerId) {
    return (int) $user->id === (int) $customerId;
});

// Channel for a specific trip (used for live location tracking)
Broadcast::channel('trip.{tripId}', function ($user, $tripId) {
    $trip = \App\Models\Trip::find($tripId);
    if (!$trip) return false;
    
    // Both the customer and the driver of this trip can access it
    return (int) $user->id === (int) $trip->customer_id || (int) $user->id === (int) $trip->driver_id;
});
