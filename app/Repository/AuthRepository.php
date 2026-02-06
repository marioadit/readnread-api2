<?php

namespace App\Repository;

use App\Models\User;
use App\Repository\Interface\AuthRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthRepository implements AuthRepositoryInterface
{

    public function login(array $data)
    {
        try {
            if (!Auth::attempt($data)) {
                return [
                    'status' => false,
                    'message' => 'Email atau password salah'
                ];
            }

            $user = Auth::user();
            
            // Delete any existing tokens for this user
            $user->tokens()->delete();

            // Create new token with expiration
            $expiresAt = now('Asia/Jakarta')->addDays(1); 
            $token = $user->createToken('auth_token', ['*'], $expiresAt)->plainTextToken;

            return [
                'status' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'expires_at' => $expiresAt->toDateTimeString()
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Login gagal',
                'error' => $e->getMessage()
            ];
        }
    }

    public function register(array $data)
    {

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required|same:password',
            'role' => 'sometimes|in:siswa,guru,admin',
        ]);

        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->all(),
            ];
        }

        // Create the user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'role' => $data['role'] ?? 'siswa',
        ]);

        return [
            'status' => true,
            'data' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ];
    }

    public function logout($user)
    {
        $logout = $user->tokens()->delete();

        if (!$logout) {
            return ['status' => false, 'message' => 'Logout gagal'];
        }

        return ['status' => true, 'message' => 'Logout berhasil'];
    }
}
