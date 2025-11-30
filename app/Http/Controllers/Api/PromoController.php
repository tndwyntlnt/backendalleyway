<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function index()
    {
        $promos = Promo::where('is_active', true)
                       ->orderBy('created_at', 'desc')
                       ->get();

        return response()->json([
            'message' => 'Promos fetched successfully',
            'promos' => $promos
        ], 200);
    }
}