<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer; 
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customers',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:15', 
            'birthday' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone,
            'birthday' => $request->birthday,
        ]);

        $token = $customer->createToken('flutterApp')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'customer' => $customer,
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $customer = Customer::where('email', $request->email)->first();

        if (! $customer || ! Hash::check($request->password, $customer->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $customer->tokens()->delete();
        $token = $customer->createToken('flutterApp')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'customer' => $customer,
            'token' => $token
        ], 200);
    }

    public function profile(Request $request)
    {
        
        return response()->json([
            'message' => 'Profile fetched successfully',
            'customer' => $request->user()
        ], 200);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,' . $user->id,
            'phone' => 'nullable|string|max:15', 
            'birthday' => 'nullable|date',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $path = $user->profile_photo_path;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('profile-photos', 'public');
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone, 
            'birthday' => $request->birthday,
            'profile_photo_path' => $path,
        ]);

        return response()->json([
            'message' => 'Profile updated successfully',
            'customer' => $user,
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Password lama tidak sesuai.',
                'errors' => ['current_password' => ['Password lama salah']]
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'message' => 'Password berhasil diubah.',
        ], 200);
    }
}