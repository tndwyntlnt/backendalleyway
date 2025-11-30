<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Reward;
use App\Models\CustomerReward;
use Carbon\Carbon;

class PointController extends Controller
{
    public function redeemCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_code' => 'required|string|exists:orders,transaction_code',
        ], [
            'transaction_code.exists' => 'Transaction code not found.'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $customer = Auth::user();
        $code = $request->input('transaction_code');

        try {
            $response = DB::transaction(function () use ($code, $customer) {
                
                $order = Order::where('transaction_code', $code)
                              ->lockForUpdate()
                              ->first();

                if ($order->status == 'claimed') {
                    return response()->json([
                        'message' => 'This code has already been claimed.'
                    ], 400);
                }
                if ($order->customer_id != null && $order->customer_id != $customer->id) {
                    return response()->json([
                        'message' => 'This code belongs to another user.'
                    ], 403);
                }

                $pointsEarned = $order->points_earned;

                $customer->points_balance += $pointsEarned;
                $customer->save();

                $order->status = 'claimed';
                $order->customer_id = $customer->id;
                $order->claimed_at = now();
                $order->save();

                $customer->upgradeMemberStatus();

                return response()->json([
                    'message' => 'Code redeemed successfully!',
                    'points_earned' => $pointsEarned,
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

    public function listRewards(Request $request)
    {
        $rewards = Reward::where('is_active', true)
                         ->orderBy('points_required', 'asc')
                         ->get();

        return response()->json([
            'message' => 'Rewards fetched successfully',
            'rewards' => $rewards
        ], 200);
    }

    public function redeemReward(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reward_id' => 'required|integer|exists:rewards,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $customer = Auth::user();
        $rewardId = $request->input('reward_id');

        try {
            $response = DB::transaction(function () use ($customer, $rewardId) {

                $reward = Reward::find($rewardId);
                $customer->lockForUpdate();

                if (!$reward || !$reward->is_active) {
                    return response()->json(['message' => 'Reward not found or is inactive.'], 404);
                }

                $pointsRequired = $reward->points_required;
                if ($customer->points_balance < $pointsRequired) {
                    return response()->json([
                        'message' => 'Not enough points to redeem this reward.'
                    ], 422);
                }

                $customer->points_balance -= $pointsRequired;
                $customer->save();
                $customer->upgradeMemberStatus();

                $customer->customerRewards()->create([
                    'reward_id' => $rewardId,
                    'status' => 'unclaimed',
                    'expires_at' => now()->addHours(48), 
                ]);

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

        $earnedPoints = DB::table('orders')
            ->select(
                DB::raw("'Kode Transaksi Di-redeem' as description"),
                'points_earned as points',
                'claimed_at as created_at'
            )
            ->where('customer_id', $customerId)
            ->where('status', 'claimed');

        $spentPoints = DB::table('customer_rewards')
            ->join('rewards', 'customer_rewards.reward_id', '=', 'rewards.id')
            ->select(
                DB::raw("CONCAT('Menukar Hadiah: ', rewards.name) as description"),
                DB::raw('rewards.points_required * -1 as points'),
                'customer_rewards.created_at as created_at'
            )
            ->where('customer_rewards.customer_id', $customerId);

        $history = $earnedPoints
            ->union($spentPoints)
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return response()->json([
            'message' => 'Activity history fetched successfully',
            'data' => $history
        ], 200);
    }

    public function myRewards(Request $request)
    {
        $user = $request->user();

        $myRewards = CustomerReward::with('reward')
            ->where('customer_id', $user->id)
            ->where('status', 'unclaimed') 
            ->where('expires_at', '>', Carbon::now())
            ->orderBy('expires_at', 'asc') 
            ->get();

        return response()->json([
            'message' => 'My rewards fetched successfully',
            'data' => $myRewards
        ], 200);
    }
}