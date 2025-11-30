<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Customer;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:customers,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Email not found.'], 404);
        }

        $token = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => $token, 
            'created_at' => Carbon::now()
        ]);

        try {
            Mail::raw("Your Alleyway password reset token is: $token", function ($message) use ($request) {
                $message->to($request->email);
                $message->subject('Reset Password Token');
            });

            return response()->json(['message' => 'Reset token sent to your email.'], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send email.', 'error' => $e->getMessage()], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:customers,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$resetRecord) {
            return response()->json(['message' => 'Invalid token.'], 400);
        }

        if (Carbon::parse($resetRecord->created_at)->addHour()->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['message' => 'Token expired.'], 400);
        }

        $customer = Customer::where('email', $request->email)->first();
        $customer->update([
            'password' => Hash::make($request->password)
        ]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password has been reset successfully.'], 200);
    }

    public function verifyToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:customers,email',
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $exists = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Token valid.'], 200);
        } else {
            return response()->json(['message' => 'Invalid token.'], 400);
        }
    }
}