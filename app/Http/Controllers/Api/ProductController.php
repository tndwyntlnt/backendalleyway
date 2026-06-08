<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['activeVariants' => fn ($query) => $query->orderBy('sort_order')])
            ->where('is_active', true)
            ->latest()
            ->get()
            ->map(fn ($product) => $this->formatProduct($product));

        return response()->json([
            'message' => 'Products fetched successfully',
            'products' => $products,
        ]);
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