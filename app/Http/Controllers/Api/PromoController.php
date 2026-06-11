<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PromoController extends Controller
{
    public function index()
    {
        $promos = Promo::where('is_active', true)
            ->select('id', 'title', 'description', 'image_url', 'is_active', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($promo) {
                $promo->image_url = $promo->image_url
                    ? Storage::disk('s3')->url($promo->image_url)
                    : null;

                return $promo;
            });

        return response()->json([
            'message' => 'Promos fetched successfully',
            'promos' => $promos
        ], 200);
    }
}