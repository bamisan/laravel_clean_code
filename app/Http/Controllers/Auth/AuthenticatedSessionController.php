<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Models\UserOtp;
use Carbon\Carbon;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $userExists = User::where('email', $request->email)->exists();

        if (!$userExists) {
            return response()->json(['error' => 'Unauthorized'], 403);

        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->input('password'), $user->getAttribute('password'))) {
            return response()->json(['error' => 'The provided credentials are incorrect.'], 403);
        }

        $otp = '';

        if ($user || Hash::check($request->input('password'), $user->getAttribute('password'))) {
            $otp = $user->generateCode();
        }

        $user['meta'] = $otp;

        return response()->json([
            'data' => $user,
            'message' => 'Successfully send otp in email.',
        ], 200);
    }

    public function verifyOtp($userId, $otp)
    {
        $verifyOtp = UserOtp::where('user_id', $userId)
        ->where('otp', $otp)
        ->where('updated_at', '>=', now()->subMinutes(9))
        ->first();

        if (is_null($verifyOtp)) {
            return response()->json(['error' => 'Incorrect OTP / USER.'], 403);
        }

        $user = User::find($userId);
        $user->email_verified_at = Carbon::now()->toDateTimeString();
        $user->save();

        $verifyOtp->delete();

        $accessToken = $user->createToken('mobile')->plainTextToken;

        $user['meta'] = [
            'tokenType' => 'Bearer',
            'accessToken' => $accessToken
        ];

        return response()->json([
            'data' => $user,
            'message' => 'Successfully 2FA verified.',
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
