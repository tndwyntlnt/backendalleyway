<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min($request->integer('per_page', 20), 50);

        $products = Product::with([
                'activeVariants' => fn ($query) => $query
                    ->select('id', 'product_id', 'name', 'price', 'sort_order', 'is_active')
                    ->orderBy('sort_order'),
            ])
            ->select('id', 'name', 'description', 'image_url', 'price', 'is_active', 'created_at')
            ->where('is_active', true)
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'message' => 'Products fetched successfully',
            'products' => $products->getCollection()
                ->map(fn ($product) => $this->formatProduct($product))
                ->values(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ])->header('Cache-Control', 'private, max-age=60');
    }

    public function show(Product $product)
    {
        abort_if(! $product->is_active, 404);

        $product->load(['activeVariants' => fn ($query) => $query->orderBy('sort_order')]);

        return response()->json([
            'message' => 'Product fetched successfully',
            'product' => $this->formatProduct($product),
        ]);
    }

    private function formatProduct(Product $product): array
    {
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
    }

    private function resolveImageUrl(?string $path): ?string
    {
        if (! $path) return null;

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('s3')->url($path);
    }
}