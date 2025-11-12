<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image_url',
        'points_required',
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
     * Mendapatkan semua riwayat redeem yang terkait dengan reward ini.
     */
    public function customerRewards()
    {
        // 'reward_id' adalah foreign key di tabel 'customer_rewards'
        return $this->hasMany(CustomerReward::class, 'reward_id');
    }
}