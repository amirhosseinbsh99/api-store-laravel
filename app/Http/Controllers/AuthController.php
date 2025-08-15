<?php

namespace App\Http\Controllers;
use App\Models\Otp;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Register new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255',
            'phone_number' => 'required|string|max:20|unique:users',
            'password'     => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name'         => $request->name,
            'phone_number' => $request->phone_number,
            'password'     => $request->password, // Automatically hashed via casts
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user'    => $user,
            'token'   => $token
        ], 201);
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('phone_number', $request->phone_number)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid phone number or password'], 401);
        }

        // Generate OTP
        $code = rand(100000, 999999);

        Otp::create([
            'phone_number' => $user->phone_number,
            'code' => $code,
            'expires_at' => now()->addMinutes(2),
        ]);

        \Log::info("OTP for {$user->phone_number} is {$code}");

        return response()->json([
            'message' => 'OTP sent. Please verify to complete login.'
        ]);
    }


    /**
     * Get authenticated user
     */
    public function dashboard(Request $request)
    {
        return response()->json($request->user());
    }
    public function updateDashboard(Request $request)
    {
        $request->validate([
            'name'             => 'sometimes|string|max:255',
            'phone_number'     => 'sometimes|string|max:20|unique:users,phone_number,' . $request->user()->id,
            'password'         => 'sometimes|string|min:6',
            'old_password'     => 'required_with:password|string', // require old_password only if password is being changed
        ]);

        $user = $request->user();

        // Check old password if password is being changed
        if ($request->filled('password')) {
            if (!\Hash::check($request->old_password, $user->password)) {
                return response()->json(['message' => 'Old password is incorrect'], 400);
            }
        }

        // Collect the fields to update
        $data = $request->only(['name', 'phone_number']);

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Dashboard profile updated successfully',
            'user'    => $user
        ]);
    }

    public function confirmPhoneChange(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'code' => 'required|string',
        ]);

        $otp = Otp::where('phone_number', $request->phone_number)
            ->where('code', $request->code)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            return response()->json(['message' => 'Invalid or expired OTP'], 400);
        }

        $user = $request->user();
        $user->update(['phone_number' => $request->phone_number]);

        // Delete OTP after use
        $otp->delete();

        return response()->json([
            'message' => 'Phone number updated successfully',
            'user' => $user,
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'code' => 'required|string'
        ]);

        $otp = Otp::where('phone_number', $request->phone_number)
                ->where('code', $request->code)
                ->where('expires_at', '>', Carbon::now())
                ->latest()
                ->first();

        if (! $otp) {
            return response()->json(['message' => 'Invalid or expired OTP'], 422);
        }

        // پیدا کردن یا ساختن کاربر
        $user = User::firstOrCreate(
            ['phone_number' => $request->phone_number],
            ['name' => 'User '.rand(1000,9999)]
        );

        // صدور توکن Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user'    => $user,
            'token'   => $token
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
