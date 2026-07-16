<?php

namespace App\Http\Controllers\ApiAnswer;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Service\StudentAnswerService;
use Illuminate\Http\Request;

class StudentAnswerApiController extends Controller
{
    protected StudentAnswerService $studentAnswerService;

    public function __construct(StudentAnswerService $studentAnswerService)
    {
        $this->studentAnswerService = $studentAnswerService;
    }

    public function index()
    {
        $userAnswers = $this->studentAnswerService->getAll();

        if ($userAnswers->isEmpty()) {
            return ApiResponse::error('User answers not found', 404);
        }

        return ApiResponse::success($userAnswers, 'User answers retrieved successfully');
    }

    public function show($id)
    {
        $userAnswer = $this->studentAnswerService->getById($id);

        if (!$userAnswer) {
            return ApiResponse::error('User answer not found', 404);
        }

        return ApiResponse::success($userAnswer, 'User answer retrieved successfully');
    }

    public function store(Request $request)
    {
        $result = $this->studentAnswerService->store($request);

        if (isset($result['status']) && $result['status'] === false) {
            return ApiResponse::error($result['message'] ?? 'User answer not created', 422);
        }

        return ApiResponse::success($result, 'User answer created successfully');
    }

    public function update(Request $request)
    {
        $result = $this->studentAnswerService->update($request);

        if (isset($result['status']) && $result['status'] === false) {
            return ApiResponse::error($result['message'] ?? 'User answer not updated', 400);
        }

        return ApiResponse::success($result, 'User answer updated successfully');
    }

    public function destroy(Request $request)
    {
        $deleted = $this->studentAnswerService->delete($request);

        if (!$deleted) {
            return ApiResponse::error('User answer not found', 404);
        }

        return ApiResponse::success(null, 'User answer deleted successfully');
    }
}
