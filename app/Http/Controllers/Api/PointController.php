<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Reward;

class PointController extends Controller
{
    /**
     * Handle redeeming a transaction code for points.
     */
    public function redeemCode(Request $request)
    {
        // 1. Validasi input (memastikan 'transaction_code' dikirim)
        $validator = Validator::make($request->all(), [
            'transaction_code' => 'required|string|exists:orders,transaction_code',
        ], [
            // Pesan error kustom jika kode tidak ditemukan
            'transaction_code.exists' => 'Transaction code not found.'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // 2. Ambil data customer yang sedang login
        $customer = Auth::user();
        $code = $request->input('transaction_code');

        // Kita gunakan DB Transaction, agar aman.
        // Jika salah satu gagal (misal update customer gagal),
        // update order juga akan dibatalkan.
        try {
            $response = DB::transaction(function () use ($code, $customer) {
                
                // 3. Cari order dan 'kunci' order tersebut agar tidak bisa di-redeem
                //    oleh 2 orang sekaligus (mencegah race condition)
                $order = Order::where('transaction_code', $code)
                              ->lockForUpdate() // <-- Penting untuk keamanan
                              ->first();

                // 4. Cek apakah order sudah di-klaim
                if ($order->status == 'claimed' || $order->customer_id != null) {
                    return response()->json([
                        'message' => 'This code has already been claimed.'
                    ], 400); // 400 Bad Request
                }

                // 5. Jika lolos, proses poinnya
                $pointsEarned = $order->points_earned;

                $customer->points_balance += $pointsEarned;
                $customer->save();

                // 6. Update order agar tidak bisa dipakai lagi
                $order->status = 'claimed';
                $order->customer_id = $customer->id;
                $order->claimed_at = now();
                $order->save();

                // 7. Kembalikan respons sukses
                return response()->json([
                    'message' => 'Code redeemed successfully!',
                    'points_earned' => $pointsEarned,
                    'new_points_balance' => $customer->points_balance
                ], 200);

            });
            
            return $response;

        } catch (\Exception $e) {
            // Jika terjadi error, kirim respons server error
            return response()->json([
                'message' => 'An error occurred during redemption. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function listRewards(Request $request)
    {
        // 1. Ambil semua reward yang 'is_active' = true
        $rewards = Reward::where('is_active', true)
                         ->orderBy('points_required', 'asc') // Urutkan dari poin terendah
                         ->get();

        // 2. Kembalikan sebagai JSON
        return response()->json([
            'message' => 'Rewards fetched successfully',
            'rewards' => $rewards
        ], 200);
    }

    public function redeemReward(Request $request)
    {
        // 1. Validasi input (memastikan reward_id dikirim & valid)
        $validator = Validator::make($request->all(), [
            'reward_id' => 'required|integer|exists:rewards,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // 2. Ambil data customer dan reward
        $customer = Auth::user();
        $rewardId = $request->input('reward_id');

        try {
            // Gunakan DB Transaction untuk keamanan
            $response = DB::transaction(function () use ($customer, $rewardId) {

                // Ambil reward dan 'kunci' data customer
                $reward = Reward::find($rewardId);
                $customer->lockForUpdate(); // Kunci customer agar poin tidak minus

                // 3. Cek apakah reward aktif
                if (!$reward || !$reward->is_active) {
                    return response()->json(['message' => 'Reward not found or is inactive.'], 404);
                }

                // 4. Cek Poin Customer (INI KUNCINYA)
                $pointsRequired = $reward->points_required;
                if ($customer->points_balance < $pointsRequired) {
                    return response()->json([
                        'message' => 'Not enough points to redeem this reward.'
                    ], 422); // 422 Unprocessable Entity
                }

                // 5. Poin cukup, proses redeem!
                // Kurangi poin customer
                $customer->points_balance -= $pointsRequired;
                $customer->save();

                // 6. Buat "voucher" (entri CustomerReward)
                // Ini adalah bukti customer sudah menukar poin
                $customer->customerRewards()->create([
                    'reward_id' => $rewardId,
                    'status' => 'unclaimed', // Status 'belum dipakai'
                    'expires_at' => now()->addHours(48), 
                ]);

                // 7. Kembalikan respons sukses
                return response()->json([
                    'message' => 'Reward redeemed successfully!',
                    'new_points_balance' => $customer->points_balance
                ], 200);

            });

            return $response;

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during redemption. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getActivityHistory(Request $request)
    {
        $customerId = Auth::id();

        // 1. Ambil data POIN MASUK (dari tabel orders)
        $earnedPoints = DB::table('orders')
            ->select(
                DB::raw("'Kode Transaksi Di-redeem' as description"), // Deskripsi statis
                'points_earned as points', // Ambil poin
                'claimed_at as created_at' // Ambil tanggal klaim
            )
            ->where('customer_id', $customerId)
            ->where('status', 'claimed'); // Hanya yang sudah diklaim

        // 2. Ambil data POIN KELUAR (dari tabel customer_rewards)
        // Kita perlu JOIN ke tabel 'rewards' untuk dapat nama & poinnya
        $spentPoints = DB::table('customer_rewards')
            ->join('rewards', 'customer_rewards.reward_id', '=', 'rewards.id')
            ->select(
                // Gabungkan string untuk deskripsi
                DB::raw("CONCAT('Menukar Hadiah: ', rewards.name) as description"),
                // Ambil poin dari reward dan JADIKAN NEGATIF
                DB::raw('rewards.points_required * -1 as points'),
                'customer_rewards.created_at as created_at' // Ambil tanggal redeem
            )
            ->where('customer_rewards.customer_id', $customerId);

        // 3. Gabungkan kedua query (POIN MASUK & POIN KELUAR)
        $history = $earnedPoints
            ->union($spentPoints)
            ->orderBy('created_at', 'desc') // Urutkan berdasarkan tanggal terbaru
            ->take(20) // Ambil 20 aktivitas terakhir
            ->get();

        // 4. Kembalikan sebagai JSON
        return response()->json([
            'message' => 'Activity history fetched successfully',
            'data' => $history // 'data' ini penting untuk Flutter
        ], 200);
    }
}