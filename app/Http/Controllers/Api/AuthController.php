<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer; 
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('customers', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:15', Rule::unique('customers', 'phone_number')],
            'birthday' => ['nullable', 'date'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'phone.unique' => 'Nomor telepon ini sudah terdaftar.',
            'phone.max' => 'Nomor telepon maksimal 15 karakter.',
            'birthday.date' => 'Format tanggal lahir tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Data registrasi belum valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone,
            'birthday' => $request->birthday,
            'member_code' => $this->generateMemberCode(),
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
            return response()->json([
                'message' => 'Data yang kamu masukkan belum valid.',
                'errors' => $validator->errors(),
            ], 422);
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

    public function exchangeSupabaseToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'access_token' => ['required', 'string'],
        ], [
            'access_token.required' => 'Token login tidak ditemukan.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Data login belum valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $supabaseUrl = rtrim(config('services.supabase.url'), '/');
        $supabaseAnonKey = config('services.supabase.anon_key');

        if (! $supabaseUrl || ! $supabaseAnonKey) {
            return response()->json([
                'message' => 'Konfigurasi login belum lengkap.',
            ], 500);
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $request->access_token,
                    'apikey' => $supabaseAnonKey,
                ])
                ->get($supabaseUrl . '/auth/v1/user');
        } catch (\Throwable $e) {
            Log::warning('Supabase token exchange failed', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Login sedang bermasalah. Coba lagi nanti.',
            ], 503);
        }

        if (! $response->successful()) {
            return response()->json([
                'message' => 'Sesi login tidak valid. Silakan login ulang.',
            ], 401);
        }

        $supabaseUser = $response->json();

        $supabaseUserId = $supabaseUser['id'] ?? null;
        $email = $supabaseUser['email'] ?? null;
        $emailConfirmedAt = $supabaseUser['email_confirmed_at']
            ?? $supabaseUser['confirmed_at']
            ?? null;

        if (! $supabaseUserId || ! $email) {
            return response()->json([
                'message' => 'Data akun tidak valid. Silakan login ulang.',
            ], 401);
        }

        if (! $emailConfirmedAt) {
            return response()->json([
                'message' => 'Email kamu belum dikonfirmasi. Silakan cek inbox email terlebih dahulu.',
            ], 403);
        }

        $metadata = $supabaseUser['user_metadata'] ?? [];

        $customer = Customer::where('supabase_user_id', $supabaseUserId)->first();

        if (! $customer) {
            $customer = Customer::where('email', $email)->first();
        }

        $name = $metadata['name']
            ?? $metadata['full_name']
            ?? ($customer?->name)
            ?? explode('@', $email)[0];

        $phone = $metadata['phone']
            ?? $metadata['phone_number']
            ?? null;

        $birthday = $metadata['birthday'] ?? null;

        if ($phone) {
            $phoneAlreadyUsed = Customer::where('phone_number', $phone)
                ->when($customer, fn ($query) => $query->where('id', '!=', $customer->id))
                ->exists();

            if ($phoneAlreadyUsed) {
                $phone = null;
            }
        }

        if (! $customer) {
            $customer = Customer::create([
                'supabase_user_id' => $supabaseUserId,
                'name' => $name,
                'email' => $email,
                'phone_number' => $phone,
                'birthday' => $birthday,
                'password' => Hash::make(Str::random(32)),
                'email_verified_at' => Carbon::parse($emailConfirmedAt),
                'member_code' => $this->generateMemberCode(),
            ]);
        } else {
            $customer->update([
                'supabase_user_id' => $supabaseUserId,
                'email_verified_at' => Carbon::parse($emailConfirmedAt),
            ]);
        }

        $customer->tokens()->delete();

        $token = $customer->createToken('flutterApp')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'customer' => $customer,
            'token' => $token,
        ], 200);
    }

    public function profile(Request $request)
    {
        $customer = $request->user();
        $customer->profile_photo_path = $this->resolveProfilePhotoUrl(
            $customer->profile_photo_path
        );

        return response()->json([
            'message' => 'Profile fetched successfully',
            'customer' => $customer
        ], 200);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('customers', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:15', Rule::unique('customers', 'phone_number')->ignore($user->id)],
            'birthday' => ['nullable', 'date'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan.',
            'phone.unique' => 'Nomor telepon ini sudah digunakan.',
            'photo.image' => 'File harus berupa gambar.',
            'photo.max' => 'Ukuran foto maksimal 2MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Data profil belum valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $path = $user->profile_photo_path;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('profile-photos', 's3');
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone, 
            'birthday' => $request->birthday,
            'profile_photo_path' => $path,
        ]);

        $user->profile_photo_path = $this->resolveProfilePhotoUrl(
            $user->profile_photo_path
        );

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
            return response()->json([
                'message' => 'Data yang kamu masukkan belum valid.',
                'errors' => $validator->errors(),
            ], 422);
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

    private function resolveProfilePhotoUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('s3')->url($path);
    }

    private function generateMemberCode(): string
    {
        do {
            $code = 'ALW-' . strtoupper(Str::random(6));
        } while (Customer::where('member_code', $code)->exists());

        return $code;
    }
}