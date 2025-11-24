<?php

namespace App\Events;

use App\Models\DriverLocation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $location;

    /**
     * Create a new event instance.
     */
    public function __construct(DriverLocation $location)
    {
        $this->location = $location;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            // Canal público para administradores que vean el mapa general
            new Channel('driver-tracking'),
            // Canal privado para el pedido específico (para el cliente)
            new PrivateChannel('order.' . $this->location->order_id),
        ];
    }

    /**
     * Nombre del evento en el cliente
     */
    public function broadcastAs(): string
    {
        return 'location.updated';
    }

    /**
     * Datos que se enviarán al cliente
     */
    public function broadcastWith(): array
    {
        $order = $this->location->order;
        
        return [
            'driver_id' => $this->location->user_id,
            'driver_name' => $this->location->user->name,
            'order_id' => $this->location->order_id,
            'order_lat' => $order ? (float) $order->lat : null,
            'order_lng' => $order ? (float) $order->lng : null,
            'order_address' => $order ? $order->direccion_entrega : null,
            'latitude' => (float) $this->location->latitude,
            'longitude' => (float) $this->location->longitude,
            'speed' => $this->location->speed ? (float) $this->location->speed : null,
            'heading' => $this->location->heading ? (float) $this->location->heading : null,
            'accuracy' => $this->location->accuracy ? (float) $this->location->accuracy : null,
            'timestamp' => $this->location->created_at->toIso8601String(),
        ];
    }
}
