<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AccountCategory;
use App\Models\TransactionCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        DB::beginTransaction();
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Buat master data default untuk user
        $defaultAccountCategory = AccountCategory::select(
            ['name', DB::raw("{$user->id} as user_id")]
        )->where('is_default', true)
            ->whereNull('user_id')
            ->get()
            ->toArray();
        AccountCategory::insert($defaultAccountCategory);

        $defaultTransactionCategory = TransactionCategory::select(
            ['name', DB::raw("{$user->id} as user_id"), 'type']
        )->where('is_default', true)
            ->whereNull('user_id')
            ->get()
            ->toArray();
        TransactionCategory::insert($defaultTransactionCategory);

        $token = JWTAuth::fromUser($user);
        DB::commit();
        return response()->json([
            'status' => 200,
            'message' => 'User registered successfully',
            'data' => [
                'user'  => $user,
                'token' => $token,
            ]
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json(['status' => 401, 'message' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Login success',
            'data' => [
                'token' => $token,
                'user'  => JWTAuth::user(),
            ]
        ]);
    }

    public function logout()
    {
        JWTAuth::logout();

        return response()->json(['status' => 200, 'message' => 'Logged out successfully']);
    }

    public function refresh(): JsonResponse
    {
        try {
            $token = JWTAuth::getToken();
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'A token is required'
                ], 400);
            }
            $newToken = JWTAuth::refresh($token);

            return response()->json([
                'success' => true,
                'data' => [
                    'auth_token' => $newToken,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60
                ]
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token cannot be refreshed',
                'errors' => $e->getMessage()
            ], 401);
        }
    }

    public function me()
    {
        $user = JWTAuth::user();
        $user->load(['accounts'=>function($query) {
            $query->orderBy('created_at');
        }]);
        return response()->json([
            'status' => 200,
            'message' => 'Authenticated user',
            'data' => $user
        ]);
    }
}
