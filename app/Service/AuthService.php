<?php

namespace App\Service;

use App\Helper\ApiResponse;
use App\Repository\Interfaces\AuthRepositoryInterface;

class AuthService
{
    protected $authRepository;

    public function __construct(AuthRepositoryInterface $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function login(array $data)
    {
        $response = $this->authRepository->login($data);

        if ($response['status']) {
            return ApiResponse::success([
                'user' => $response['user'],
                'token' => $response['token']
            ]);
        }

        return [
            'status' => false,
            'message' => 'Invalid credentials',
        ];
    }

    // Register
    public function register(array $data)
    {
        $response = $this->authRepository->register($data);

        if ($response['status']) {
            return ApiResponse::success($response['user'], 'User registered successfully');
        }

        return ApiResponse::error([], $response['message']);
    }


    // Logout
    public function logout($user)
    {
        $response = $this->authRepository->logout($user);
        if ($response['status']) {
            return ApiResponse::success([], $response['message']);
        }
        return ApiResponse::error([], $response['message']);

    }
}
