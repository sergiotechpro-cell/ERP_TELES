<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'latitude',
        'longitude',
        'speed',
        'heading',
        'accuracy',
        'is_active',
        'location_timestamp',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'speed' => 'decimal:2',
        'heading' => 'decimal:2',
        'accuracy' => 'decimal:2',
        'is_active' => 'boolean',
        'location_timestamp' => 'datetime',
    ];

    /**
     * Relación con el usuario (chofer)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el pedido
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope para obtener solo ubicaciones activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para obtener la última ubicación de un chofer
     */
    public function scopeLatestForDriver($query, $userId)
    {
        return $query->where('user_id', $userId)
            ->where('is_active', true)
            ->latest('created_at');
    }

    /**
     * Scope para obtener ubicaciones recientes (últimos 5 minutos)
     */
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subMinutes(5));
    }
}
