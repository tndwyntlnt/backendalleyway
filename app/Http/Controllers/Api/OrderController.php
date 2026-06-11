<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'customer_note' => ['nullable', 'string', 'max:500'],
        ], [
            'items.required' => 'Pesanan belum memiliki item.',
            'items.array' => 'Format pesanan tidak valid.',
            'items.min' => 'Pesanan minimal memiliki 1 item.',
            'items.*.product_variant_id.required' => 'Ukuran produk belum dipilih.',
            'items.*.product_variant_id.integer' => 'Ukuran produk tidak valid.',
            'items.*.product_variant_id.exists' => 'Produk atau ukuran yang dipilih sudah tidak tersedia. Silakan refresh menu.',
            'items.*.quantity.required' => 'Jumlah produk belum dipilih.',
            'items.*.quantity.integer' => 'Jumlah produk tidak valid.',
            'items.*.quantity.min' => 'Jumlah produk minimal 1.',
            'items.*.quantity.max' => 'Jumlah produk terlalu banyak.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first() ?? 'Data order belum valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $customer = $request->user();

        Order::where('customer_id', $customer->id)
            ->where('source', 'app')
            ->where('order_status', 'pending')
            ->where('created_at', '<', now()->subMinutes(30))
            ->update(['order_status' => 'cancelled']);

        $activeOrder = Order::with('orderItems')
            ->where('customer_id', $customer->id)
            ->where('source', 'app')
            ->whereIn('order_status', ['pending', 'ready'])
            ->latest()
            ->first();

        if ($activeOrder) {
            return response()->json([
                'message' => 'Kamu masih memiliki pesanan aktif. Selesaikan atau tunggu pesanan sebelumnya dibatalkan terlebih dahulu.',
                'order' => $this->formatOrder($activeOrder),
            ], 409);
        }

        $todayOrderCount = Order::where('customer_id', $customer->id)
            ->where('source', 'app')
            ->whereDate('created_at', now()->toDateString())
            ->count();

        if ($todayOrderCount >= 5) {
            return response()->json([
                'message' => 'Batas pesanan harian sudah tercapai. Silakan coba lagi besok.',
            ], 429);
        }

        $order = DB::transaction(function () use ($request, $customer) {
            $items = collect($request->items);
            $total = 0;
            $preparedItems = [];

            foreach ($items as $item) {
                $variant = ProductVariant::with('product')->lockForUpdate()->findOrFail($item['product_variant_id']);

                if (! $variant->is_active || ! $variant->product || ! $variant->product->is_active) {
                    abort(422, 'Produk tidak tersedia.');
                }

                $quantity = (int) $item['quantity'];
                $subtotal = $variant->price * $quantity;
                $total += $subtotal;

                $preparedItems[] = [
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'variant_name' => $variant->name,
                    'quantity' => $quantity,
                    'price_per_item' => $variant->price,
                    'subtotal' => $subtotal,
                ];
            }

            $order = Order::create([
                'transaction_code' => $this->generateTransactionCode(),
                'customer_id' => $customer->id,
                'source' => 'app',
                'order_status' => 'pending',
                'status' => 'unclaimed',
                'total_amount' => $total,
                'points_earned' => floor($total / 20000) * 10,
                'customer_note' => $request->customer_note,
            ]);

            $order->orderItems()->createMany($preparedItems);

            return $order->load('orderItems');
        });

        return response()->json([
            'message' => 'Order berhasil dibuat.',
            'order' => $this->formatOrder($order),
        ], 201);
    }

    public function myOrders(Request $request)
    {
        $orders = Order::with('orderItems')
            ->where('customer_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn ($order) => $this->formatOrder($order));

        return response()->json([
            'message' => 'Orders fetched successfully',
            'orders' => $orders,
        ]);
    }

    public function show(Request $request, Order $order)
    {
        abort_if($order->customer_id !== $request->user()->id, 404);

        $order->load('orderItems');

        return response()->json([
            'message' => 'Order fetched successfully',
            'order' => $this->formatOrder($order),
        ]);
    }

    private function formatOrder(Order $order): array
    {
        return [
            'id' => $order->id,
            'transaction_code' => $order->transaction_code,
            'total_amount' => $order->total_amount,
            'points_earned' => $order->points_earned,
            'status' => $order->status,
            'order_status' => $order->order_status,
            'customer_note' => $order->customer_note,
            'created_at' => $order->created_at,
            'items' => $order->orderItems->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'product_name' => $item->product_name,
                'variant_name' => $item->variant_name,
                'quantity' => $item->quantity,
                'price_per_item' => $item->price_per_item,
                'subtotal' => $item->subtotal,
            ])->values(),
        ];
    }

    private function generateTransactionCode(): string
    {
        do {
            $code = 'ALW-' . strtoupper(Str::random(6));
        } while (Order::where('transaction_code', $code)->exists());

        return $code;
    }
}