<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'reward_id',
        'status',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'expires_at' => 'datetime',
    ];


    // RELASI

    /**
     * Mendapatkan customer pemilik redeem ini.
     */
    public function customer()
    {
        // 'customer_id' adalah foreign key di tabel ini
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Mendapatkan data reward yang di-redeem.
     */
    public function reward()
    {
        // 'reward_id' adalah foreign key di tabel ini
        return $this->belongsTo(Reward::class, 'reward_id');
    }
}