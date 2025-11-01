<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// Tambahkan ini
use Illuminate\Foundation\Auth\User as Authenticatable; 
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// Ubah 'extends Model' menjadi 'extends Authenticatable'
class Customer extends Authenticatable
{
    // Tambahkan HasApiTokens dan Notifiable
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
        'password',
        'points_balance',
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
        'password' => 'hashed', // Otomatis hash password saat dibuat
    ];

    // RELASI
    
    /**
     * Mendapatkan semua order yang dimiliki oleh customer.
     */
    public function orders()
    {
        // 'customer_id' adalah foreign key di tabel 'orders'
        return $this->hasMany(Order::class, 'customer_id');
    }

    /**
     * Mendapatkan semua riwayat reward yang di-redeem oleh customer.
     */
    public function customerRewards()
    {
        // 'customer_id' adalah foreign key di tabel 'customer_rewards'
        return $this->hasMany(CustomerReward::class, 'customer_id');
    }
}