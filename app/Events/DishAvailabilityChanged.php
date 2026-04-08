<?php

namespace App\Events;

use App\Models\Dish;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DishAvailabilityChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The dish instance.
     */
    public Dish $dish;

    /**
     * Whether the dish is now available.
     */
    public bool $isAvailable;

    /**
     * Create a new event instance.
     */
    public function __construct(Dish $dish, bool $isAvailable)
    {
        $this->dish = $dish;
        $this->isAvailable = $isAvailable;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('tenant.' . $this->dish->tenant_id),
            // Public channel for menu clients
            new Channel('menu.' . $this->dish->tenant_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'dish.availability.changed';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->dish->id,
            'name' => $this->dish->name,
            'category_id' => $this->dish->category_id,
            'is_available' => $this->isAvailable,
            'stock_quantity' => $this->dish->stock_quantity,
            'active' => $this->dish->active,
            'updated_at' => $this->dish->updated_at->toIso8601String(),
        ];
    }
}
