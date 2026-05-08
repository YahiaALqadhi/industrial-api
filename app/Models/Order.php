<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'shipping_per_item',
        'shipping_cost',
        'discount_amount',
        'currency',
        'status',
        'payment_status',
        'payment_receipt',
        'customer_name',
        'customer_email',
        'customer_phone',
        'country',
        'city',
        'shipping_address',
        'notes',
        'ordered_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_per_item' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'ordered_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($order) {
            if (blank($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(Str::random(10));
            }

            if (blank($order->ordered_at)) {
                $order->ordered_at = now();
            }

            $order->calculateTotals();
        });

        static::updating(function ($order) {
            $order->calculateTotals();
        });
    }

    public function calculateTotals(): void
    {
        $totalQuantity = $this->items()->sum('quantity');
        $subtotal = $this->items()->sum('total_price');

        $shippingPerItem = (float) ($this->shipping_per_item ?? 0);
        $discountAmount = (float) ($this->discount_amount ?? 0);

        $shippingCost = $shippingPerItem * $totalQuantity;
        $totalAmount = $subtotal + $shippingCost - $discountAmount;

        $this->subtotal = $subtotal;
        $this->shipping_cost = $shippingCost;
        $this->total_amount = max($totalAmount, 0);
    }

    public function canBeCancelledByCustomer(): bool
    {
        return ($this->status ?? 'pending') === 'pending';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}