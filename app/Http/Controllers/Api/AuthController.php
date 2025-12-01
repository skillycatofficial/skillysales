<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Authenticate user with Google ID token
     */
    public function googleAuth(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        try {
            // Verify Google ID token
            $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $request->token,
            ]);

            if ($response->failed()) {
                return response()->json(['error' => 'Invalid Google token'], 401);
            }

            $googleUser = $response->json();

            // Verify the token is for our app
            if ($googleUser['aud'] !== env('GOOGLE_CLIENT_ID')) {
                return response()->json(['error' => 'Invalid client ID'], 401);
            }

            // Find or create user
            $user = User::where('google_id', $googleUser['sub'])
                ->orWhere('email', $googleUser['email'])
                ->first();

            if (!$user) {
                // Create new user
                $user = User::create([
                    'name' => $googleUser['name'],
                    'email' => $googleUser['email'],
                    'google_id' => $googleUser['sub'],
                    'email_verified_at' => now(),
                    'password' => bcrypt(Str::random(32)), // Random password since we use OAuth
                ]);
            } else {
                // Update google_id if not set
                if (!$user->google_id) {
                    $user->google_id = $googleUser['sub'];
                    $user->save();
                }
            }

            // Create Sanctum token
            $token = $user->createToken('mobile-app')->plainTextToken;

            // Check if user is admin
            $isAdmin = $user->email === env('ADMIN_EMAIL');

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_admin' => $isAdmin,
                ],
                'token' => $token,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Authentication failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request)
    {
        $user = $request->user()->load('dealer');
        $isAdmin = $user->email === env('ADMIN_EMAIL');

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_admin' => $isAdmin,
            'dealer' => $user->dealer,
        ]);
    }

    /**
     * Register a new user with email and password
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'email_verified_at' => now(), // Auto-verify for now
            ]);

            // Create Sanctum token
            $token = $user->createToken('mobile-app')->plainTextToken;

            // Check if user is admin
            $isAdmin = $user->email === env('ADMIN_EMAIL');

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_admin' => $isAdmin,
                ],
                'token' => $token,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Registration failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login user with email and password
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            // Create Sanctum token
            $token = $user->createToken('mobile-app')->plainTextToken;

            // Check if user is admin
            $isAdmin = $user->email === env('ADMIN_EMAIL');

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_admin' => $isAdmin,
                ],
                'token' => $token,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Invalid credentials',
                'message' => $e->getMessage(),
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Login failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Logout user (revoke current token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
