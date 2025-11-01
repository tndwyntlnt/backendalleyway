<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'image_url',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];


    // RELASI

    /**
     * Mendapatkan semua riwayat item order yang terkait dengan produk ini.
     */
    public function orderItems()
    {
        // 'product_id' adalah foreign key di tabel 'order_items'
        return $this->hasMany(OrderItem::class, 'product_id');
    }
}