<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The order instance.
     */
    public Order $order;

    /**
     * Create a new event instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order->load(['items.dish', 'table']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('tenant.' . $this->order->tenant_id),
            new PrivateChannel('kds.' . $this->order->tenant_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'order.created';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'status' => $this->order->status,
            'status_label' => $this->order->getStatusLabel(),
            'status_color' => $this->order->getStatusColor(),
            'total' => $this->order->total,
            'notes' => $this->order->notes,
            'table' => $this->order->table ? [
                'id' => $this->order->table->id,
                'code' => $this->order->table->code,
                'label' => $this->order->table->label,
            ] : null,
            'items' => $this->order->items->map(fn($item) => [
                'id' => $item->id,
                'dish_name' => $item->dish?->name ?? 'N/A',
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'variant_name' => $item->variant_name,
                'options' => $item->options,
                'notes' => $item->notes,
            ])->toArray(),
            'items_count' => $this->order->items->sum('quantity'),
            'created_at' => $this->order->created_at->toIso8601String(),
            'created_at_human' => $this->order->created_at->diffForHumans(),
        ];
    }
}
