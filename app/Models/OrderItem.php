<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price_per_item',
    ];

    // RELASI

    /**
     * Mendapatkan order induk dari item ini.
     */
    public function order()
    {
        // 'order_id' adalah foreign key di tabel ini
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Mendapatkan data produk dari item ini.
     */
    public function product()
    {
        // 'product_id' adalah foreign key di tabel ini
        return $this->belongsTo(Product::class, 'product_id');
    }
}