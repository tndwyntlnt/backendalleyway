<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer; // <-- Import model Customer kita
use Illuminate\Support\Facades\Hash; // <-- Import Hash
use Illuminate\Support\Facades\Validator; // <-- Import Validator

class AuthController extends Controller
{
    /**
     * Handle customer registration.
     */
    public function register(Request $request)
    {
        // 1. Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customers',
            'password' => 'required|string|min:8|confirmed', // 'confirmed' akan cek 'password_confirmation'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // 2. Buat customer baru
        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Jangan lupa HASH password
        ]);

        // 3. Buat token API untuk customer
        // 'flutterApp' adalah nama token, bisa apa saja
        $token = $customer->createToken('flutterApp')->plainTextToken;

        // 4. Kembalikan response sukses
        return response()->json([
            'message' => 'Registration successful',
            'customer' => $customer,
            'token' => $token
        ], 201);
    }

    /**
     * Handle customer login.
     */
    public function login(Request $request)
    {
        // 1. Validasi input
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // 2. Cari customer berdasarkan email
        $customer = Customer::where('email', $request->email)->first();

        // 3. Cek customer dan password
        if (! $customer || ! Hash::check($request->password, $customer->password)) {
            // Jika gagal, kirim pesan error
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // 4. Hapus token lama (jika ada) dan buat token baru
        $customer->tokens()->delete(); // Logout dari device lain (opsional)
        $token = $customer->createToken('flutterApp')->plainTextToken;

        // 5. Kembalikan response sukses
        return response()->json([
            'message' => 'Login successful',
            'customer' => $customer,
            'token' => $token
        ], 200);
    }

    public function profile(Request $request)
    {
        // 'auth:sanctum' akan otomatis mengisi $request->user()
        // dengan data customer yang sedang login.
        
        return response()->json([
            'message' => 'Profile fetched successfully',
            'customer' => $request->user()
        ], 200);
    }

    public function logout(Request $request)
    {
        // Hapus token yang sedang dipakai untuk request ini
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }
}