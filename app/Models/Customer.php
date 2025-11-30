<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; 
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'birthday',
        'password',
        'points_balance',
        'profile_photo_path',
        'member_status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function customerRewards()
    {
        return $this->hasMany(CustomerReward::class, 'customer_id');
    }

    public function upgradeMemberStatus()
    {
        $points = $this->points_balance;
        $newStatus = 'Bronze';

        if ($points >= 1500) {
            $newStatus = 'Gold';
        } elseif ($points >= 500) {
            $newStatus = 'Silver';
        } else {
            $newStatus = 'Bronze';
        }

        if ($this->member_status !== $newStatus) {
            $this->update(['member_status' => $newStatus]);
        }
    }
}