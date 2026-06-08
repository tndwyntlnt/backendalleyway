<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use App\Models\Order;
use App\Models\CustomerReward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->user();

        $customer->profile_photo_path = $this->resolveImageUrl($customer->profile_photo_path);

        $promos = Promo::where('is_active', true)
            ->select('id', 'title', 'description', 'image_url')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($promo) {
                $promo->image_url = $this->resolveImageUrl($promo->image_url);
                return $promo;
            });

        $orders = Order::where('customer_id', $customer->id)
            ->whereIn('status', ['claimed', 'unclaimed'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => 'order_' . $order->id,
                    'type' => 'earn',
                    'title' => $order->status === 'claimed' ? 'Struk Pembelian' : 'Menunggu Klaim',
                    'date' => $order->status === 'claimed' ? $order->claimed_at : $order->created_at,
                    'amount' => $order->status === 'claimed' ? "+{$order->points_earned}" : 'Pending',
                ];
            });

        $rewards = CustomerReward::with('reward:id,name,points_required')
            ->where('customer_id', $customer->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($item) {
                $points = $item->reward->points_required ?? 0;

                return [
                    'id' => 'reward_' . $item->id,
                    'type' => 'spend',
                    'title' => 'Reward Ditukar',
                    'date' => $item->created_at,
                    'amount' => "-{$points}",
                ];
            });

        $recentActivities = $orders->merge($rewards)
            ->sortByDesc('date')
            ->take(5)
            ->values();

        $products = Product::with(['activeVariants' => fn ($query) => $query->orderBy('sort_order')])
            ->where('is_active', true)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($product) {
                $variants = $product->activeVariants
                    ->map(fn ($variant) => [
                        'id' => $variant->id,
                        'name' => $variant->name,
                        'price' => $variant->price,
                    ])
                    ->values();

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'image_url' => $this->resolveImageUrl($product->image_url),
                    'starting_price' => $variants->min('price') ?? $product->price,
                    'variants' => $variants,
                ];
            });

        return response()->json([
            'message' => 'Home data fetched successfully',
            'customer' => $customer,
            'promos' => $promos,
            'products' => $products,
            'recent_activities' => $recentActivities,
        ]);
    }

    private function resolveImageUrl(?string $path): ?string
    {
        if (!$path) return null;

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('s3')->url($path);
    }
}