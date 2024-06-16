<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Password;

class ActiveAccountController extends Controller
{
    public function verify($id, $hash, Request $request)
    {
        $userExists = User::where('email', $request->email)->exists();

        if (!$userExists) {
            return response()->json(['error' => 'Unauthorized'], 403);

        }

        $user = User::where('email', $request->email)->first();

        if (! hash_equals(sha1($user->getEmailForVerification()), (string) $id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['errors' => ['email' => ['Email already verified']]], 400);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));

        }

        return response()->json([
            'data' => $user,
            'message' => 'Account verified successfully',
        ], 200);

    }
}
