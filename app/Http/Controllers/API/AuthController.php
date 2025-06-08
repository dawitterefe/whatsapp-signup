<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\OTP;
use App\Models\User;
use Illuminate\Http\Request;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function sendWhatsAppOTP(Request $request)
    {
        $request->validate(['phone_number' => 'required|string']);

        $phoneNumber = $request->phone_number;
        $otp = rand(100000, 999999); // 6-digit OTP

        try {
            // Save OTP
            OTP::create([
                'phone_number' => $phoneNumber,
                'otp' => $otp,
            ]);

            // Send OTP via Twilio WhatsApp
            $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
            $twilio->messages->create(
                env('TWILIO_WHATSAPP_NUMBER'),
                [
                    'from' => env('TWILIO_WHATSAPP_NUMBER'),
                    'body' => "Your signup OTP is $otp. It expires in 5 minutes.",
                ]
            );

            return response()->json(['message' => 'OTP sent to WhatsApp']);
        } catch (\Exception $e) {
            Log::error('Twilio Error: ' . $e->getMessage());
            // Mock OTP for demo if Twilio fails
            Log::info("Mock OTP for $phoneNumber: $otp");
            return response()->json(['message' => 'Mock OTP sent (check logs)']);
        }
    }

    public function verifyOTP(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'otp' => 'required|string',
        ]);

        $otpRecord = OTP::where('phone_number', $request->phone_number)
            ->where('otp', $request->otp)
            ->first();

        if (!$otpRecord || $otpRecord->isExpired()) {
            return response()->json(['error' => 'Invalid or expired OTP'], 400);
        }

        // Check if user exists, else create
        $user = User::firstOrCreate(
            ['phone_number' => $request->phone_number],
            ['name' => 'User', 'email' => null, 'password' => bcrypt(str_random(16))]
        );

        // Delete OTP
        $otpRecord->delete();

        // Generate Sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => ['phone_number' => $user->phone_number],
        ]);
    }
}
