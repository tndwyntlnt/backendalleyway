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


    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function reward()
    {
        return $this->belongsTo(Reward::class, 'reward_id');
    }
}