<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code',
        'total_amount',
        'points_earned',
        'status',
        'customer_id',
        'claimed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'claimed_at' => 'datetime',
    ];

    // RELASI

    /**
     * Mendapatkan customer yang meng-klaim order ini.
     */
    public function customer()
    {
        // 'customer_id' adalah foreign key di tabel ini ('orders')
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Mendapatkan semua item di dalam order ini.
     */
    public function orderItems()
    {
        // 'order_id' adalah foreign key di tabel 'order_items'
        return $this->hasMany(OrderItem::class, 'order_id');
    }
}