<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class DriverLocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $driver;
    public $tripId;

    /**
     * Create a new event instance.
     */
    public function __construct(User $driver, $tripId = null)
    {
        $this->driver = $driver;
        $this->tripId = $tripId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Broadcast specifically to the trip channel so the customer tracking this trip gets the update
        if ($this->tripId) {
            return [
                new PrivateChannel('trip.' . $this->tripId),
            ];
        }
        
        // Or broadly to tracking channel if needed
        return [
            new PrivateChannel('driver.' . $this->driver->id . '.location'),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'driver_id' => $this->driver->id,
            'latitude' => $this->driver->latitude,
            'longitude' => $this->driver->longitude,
        ];
    }
}
