<?php

namespace App\Http\Controllers\ApiAuth;

use App\Http\Controllers\Controller;
use App\Repository\AuthRepository;
use App\Repository\Interface\AuthRepositoryInterface;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthApiController extends Controller
{
    protected $authService;

    public function __construct(AuthRepository $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        $response = $this->authService->login($request->only(['email', 'password']));
        return response()->json($response, $response['status'] ? 200 : 422);
    }

    // Register
    public function register(Request $request){
        $response = $this->authService->register($request->only(['name', 'email', 'password', 'password_confirmation', 'role']));
        return response()->json($response, $response['status'] ? 200 : 422);
    }

    // Logout
    public function logout(Request $request){
        $response = $this->authService->logout($request->user());
        return response()->json($response, $response['status'] ? 200 : 422);
    }
}
