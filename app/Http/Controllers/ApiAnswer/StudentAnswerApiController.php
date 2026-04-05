<?php

namespace App\Http\Controllers\ApiAnswer;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Repository\Interfaces\IUserAnswerRepository;
use Illuminate\Http\Request;

class StudentAnswerApiController extends Controller
{
    protected $userAnswerRepository;

    public function __construct(IUserAnswerRepository $userAnswerRepository)
    {
        $this->userAnswerRepository = $userAnswerRepository;
    }


    public function index()
    {
        $userAnswers = $this->userAnswerRepository->getAll();

        if ($userAnswers->isEmpty()) {
            return ApiResponse::error('User answers not found', 404);
        }

        return ApiResponse::success($userAnswers, 'User answers retrieved successfully');
    }

    public function show($id)
    {
        $userAnswer = $this->userAnswerRepository->getById($id);

        if (!$userAnswer) {
            return ApiResponse::error('User answer not found', 404);
        }

        return ApiResponse::success($userAnswer, 'User answer retrieved successfully');
    }

    public function store(Request $request)
    {
        $userAnswer = $this->userAnswerRepository->store($request);

        if (!$userAnswer) {
            return ApiResponse::error('User answer not created', 422, $userAnswer['errors'] ?? null);
        }

        return ApiResponse::success($userAnswer, 'User answer created successfully');
    }

    public function update(Request $request)
    {
        $userAnswer = $this->userAnswerRepository->update($request);

        if (!$userAnswer) {
            return ApiResponse::error('User answer not updated', 400);
        }

        return ApiResponse::success($userAnswer, 'User answer updated successfully');
    }

    public function destroy(Request $request)
    {
        $deleted = $this->userAnswerRepository->destroy($request);

        if (!$deleted) {
            return ApiResponse::error('User answer not found', 404);
        }

        return ApiResponse::success(null, 'User answer deleted successfully');
    }
}
