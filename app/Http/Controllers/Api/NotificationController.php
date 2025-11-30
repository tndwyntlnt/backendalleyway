<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\CustomerReward;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $orders = Order::with('orderItems.product')
            ->where('customer_id', $user->id)
            ->whereIn('status', ['claimed', 'unclaimed'])
            ->orderBy('created_at', 'desc') 
            ->get()
            ->map(function ($order) {
                
                $itemsList = $order->orderItems->map(function ($item) {
                    $productName = $item->product->name ?? 'Item Dihapus';
                    return "{$item->quantity}x {$productName}";
                })->join(', ');

                $totalRupiah = number_format($order->total_amount, 0, ',', '.');
                $isClaimed = $order->status === 'claimed';

                if ($isClaimed) {
                    $title = 'Struk Pembelian';
                    $message = "Detail: $itemsList.\nTotal: Rp $totalRupiah";
                    $amount = "+{$order->points_earned}";
                    $date = $order->claimed_at;
                } else {
                    $title = 'Menunggu Klaim';
                    $message = "Kode Transaksi: {$order->transaction_code}\nSilakan 'Redeem' kode ini untuk mendapatkan poin.";
                    $amount = "Pending"; 
                    $date = $order->created_at;
                }

                return [
                    'id' => 'order_' . $order->id,
                    'type' => 'earn', 
                    'status' => $order->status,
                    'title' => $title,
                    'message' => $message,
                    'date' => $date,
                    'amount' => $amount,
                ];
            });

        $rewards = CustomerReward::with('reward')
            ->where('customer_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                $rewardName = $item->reward->name ?? 'Unknown Item';
                $points = $item->reward->points_required ?? 0;
                
                return [
                    'id' => 'reward_' . $item->id,
                    'type' => 'spend',
                    'title' => 'Reward Ditukar',
                    'message' => "Anda menukar {$points} poin untuk voucher {$rewardName}.",
                    'date' => $item->created_at,
                    'amount' => "-{$points}",
                ];
            });

        $notifications = $orders->merge($rewards);

        $sortedNotifications = $notifications->sortByDesc('date')->values();

        return response()->json([
            'message' => 'Notifications fetched successfully',
            'data' => $sortedNotifications
        ], 200);
    }
}