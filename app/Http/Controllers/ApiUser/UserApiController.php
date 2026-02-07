<?php

namespace App\Http\Controllers\ApiUser;

use App\Http\Controllers\Controller;
use App\Http\Requests\Validate\UserValidate;
use App\Http\Requests\UserProfileValidate;
use App\Service\UserService;
use App\Helper\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class UserApiController extends Controller
{
    protected $userService;

    public function __construct(UserService $_userService)
    {
        $this->userService = $_userService;
    }

    public function index()
    {
        $users = $this->userService->getAllUser();
        return ApiResponse::success($users);
    }

    public function show($id)
    {
        $user = $this->userService->getUserById($id);
        return ApiResponse::success($user);
    }

    public function store(UserValidate $request)
    {
        $user = $this->userService->createUser($request->validated());
        return ApiResponse::success($user, 'User created successfully', 201);
    }

    public function update(UserProfileValidate $request, $id)
    {
        $user = $this->userService->updateUser($id, $request->validated());
        return ApiResponse::success($user, 'User updated successfully');
    }

    public function updateUser(UserValidate $request, $id)
    {
        $user = $this->userService->updateUser($id, $request->validated());
        return ApiResponse::success($user, 'User updated successfully');
    }

    public function destroy($id)
    {
        $deleted = $this->userService->deleteUser($id);

        if (!$deleted) {
            return ApiResponse::error('Failed to delete user', 400);
        }

        return ApiResponse::success([], 'User deleted successfully');
    }

    public function getUserProfile()
    {
        $user = $this->userService->getUserProfile();
        return ApiResponse::success($user);
    }

    public function updateProfile(UserProfileValidate $request, $id)
    {
        $user = $this->userService->updateProfile($id, $request->validated());
        return ApiResponse::success($user, 'User updated successfully');
    }

    public function resetPassword($id)
    {
        $user = $this->userService->resetPassword($id);
        return ApiResponse::success($user, 'User password reset successfully');
    }

    public function updatePassword($id, Request $request)
    {
        // Validate request first
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => 'required|string
            |min:8
            |confirmed',
            'password_confirmation' => 'required|same:password'
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed', 422, $validator->errors());
        }

        // $id = Auth::user()->id;
        $result = $this->userService->updatePassword($id, $request->all());

        if (!$result['status']) {
            return ApiResponse::error($result['message'], 400, $result['errors'] ?? null);
        }

        return ApiResponse::success($result['data'], $result['message']);
    }

    public function getUserStudent()
    {
        $user = $this->userService->getUserStudent();
        return ApiResponse::success($user);
    }

    public function getUserTeacher()
    {
        $user = $this->userService->getUserTeacher();
        return ApiResponse::success($user);
    }

    public function getClassUser()
    {
        $user = $this->userService->getClassUser();
        return ApiResponse::success($user);
    }

    public function getUserLesson()
    {
        try {
            $user = $this->userService->getUserLesson();
            return ApiResponse::success($user);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage());
        }
    }

    public function getStudentAnswers($id)
    {
        $user = $this->userService->getStudentAnswers($id);
        return ApiResponse::success($user);
    }

    public function getAllStudentAnswers()
    {
        $user = $this->userService->getAllStudentAnswers();
        return ApiResponse::success($user);
    }

    public function getStudentAnswersAdmin($idstudent, $idanswer)
    {
        $user = $this->userService->getStudentAnswersAdmin($idstudent, $idanswer);
        return ApiResponse::success($user);
    }

    public function getAllStudentAnswersAdmin($idstudent)
    {
        $user = $this->userService->getAllStudentAnswersAdmin($idstudent);
        return ApiResponse::success($user);
    }

    public function getUserQuiz()
    {
        $user = $this->userService->getUserQuiz();

        if (!$user['status']) {
            return ApiResponse::error($user['message'], 400);
        }

        return ApiResponse::success($user['data'], $user['message']);
    }
}
