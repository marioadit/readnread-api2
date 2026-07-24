<?php

namespace App\Http\Controllers\ApiAnswer;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Repository\Interfaces\IAnswerHistoryRepository;
use Illuminate\Http\Request;

class AnswerHistoryApiController extends Controller
{
    protected IAnswerHistoryRepository $answerHistoryRepository;

    public function __construct(IAnswerHistoryRepository $answerHistoryRepository)
    {
        $this->answerHistoryRepository = $answerHistoryRepository;
    }

    /**
     * Ambil semua history (admin/guru).
     */
    public function index()
    {
        $histories = $this->answerHistoryRepository->getAll();

        if ($histories->isEmpty()) {
            return ApiResponse::error('Answer histories not found', 404);
        }

        return ApiResponse::success($histories, 'Answer histories retrieved successfully');
    }

    /**
     * Ambil history milik siswa yang sedang login.
     */
    public function getMyHistory(Request $request)
    {
        $studentId = $request->user()->id;
        $histories = $this->answerHistoryRepository->getByStudent($studentId);

        if ($histories->isEmpty()) {
            return ApiResponse::error('Answer histories not found', 404);
        }

        return ApiResponse::success($histories, 'Answer histories retrieved successfully');
    }

    /**
     * Ambil history berdasarkan student_id.
     */
    public function getByStudent($studentId)
    {
        $histories = $this->answerHistoryRepository->getByStudent((int) $studentId);

        if ($histories->isEmpty()) {
            return ApiResponse::error('Answer histories not found for this student', 404);
        }

        return ApiResponse::success($histories, 'Answer histories retrieved successfully');
    }

    /**
     * Ambil history berdasarkan quiz_id.
     */
    public function getByQuiz($quizId)
    {
        $histories = $this->answerHistoryRepository->getByQuiz((int) $quizId);

        if ($histories->isEmpty()) {
            return ApiResponse::error('Answer histories not found for this quiz', 404);
        }

        return ApiResponse::success($histories, 'Answer histories retrieved successfully');
    }

    /**
     * Ambil detail history berdasarkan quiz_id.
     */
    public function getByDetail($studentId, $quizId)
    {
        $histories = $this->answerHistoryRepository->getByDetail((int) $studentId, (int) $quizId);

        if (!$histories) {
            return ApiResponse::error('Answer history not found for this student and quiz', 404);
        }

        return ApiResponse::success($histories, 'Answer history retrieved successfully');
    }

    /**
     * Ambil detail history milik siswa yang sedang login.
     */
    public function getMyDetailHistory(Request $request, $quizId)
    {
        $studentId = $request->user()->id;
        $histories = $this->answerHistoryRepository->getByDetail((int) $studentId, (int) $quizId);

        if (!$histories) {
            return ApiResponse::error('Answer history not found for this student and quiz', 404);
        }

        return ApiResponse::success($histories, 'Answer history retrieved successfully');
    }
}

