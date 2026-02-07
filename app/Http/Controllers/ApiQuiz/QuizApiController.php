<?php

namespace App\Http\Controllers\ApiQuiz;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Repository\Interface\QuizRepositoryInterface;
use Illuminate\Http\Request;

class QuizApiController extends Controller
{
    protected $quizRepository;

    public function __construct(QuizRepositoryInterface $quiz)
    {
        $this->quizRepository = $quiz;
    }

    public function index()
    {
        $quiz = $this->quizRepository->getAll();

        return ApiResponse::success($quiz);
    }

    public function getQuizByLessons($quizId)
    {
        $quiz = $this->quizRepository->getQuizByLessons($quizId);

        return ApiResponse::success($quiz);
    }

    public function getQuizByQuestions($quizId)
    {
        $quiz = $this->quizRepository->getQuizByQuestions($quizId);

        return ApiResponse::success($quiz);
    }

    public function getWithLessonAndQuestions($quizId)
    {
        $quiz = $this->quizRepository->getWithLessonAndQuestions($quizId);

        return ApiResponse::success($quiz);
    }

    public function show($id)
    {
        $quiz = $this->quizRepository->getById($id);

        return ApiResponse::success($quiz);
    }

    public function store(Request $request)
    {
        $quiz = $this->quizRepository->create($request->all());

        if (!$quiz['status']) {
            return ApiResponse::error($quiz['message'], 422, $quiz['errors'] ?? null);
        }

        return ApiResponse::success($quiz['data'], $quiz['message'], 201);
    }

    public function update(Request $request, $id)
    {
        $quiz = $this->quizRepository->update($id, $request->all());

        if (!$quiz['status']) {
            return ApiResponse::error($quiz['message'], 422, $quiz['errors'] ?? null);
        }

        return ApiResponse::success($quiz['data'], $quiz['message']);
    }

    public function getQuizResults($quizId)
    {
        $studentId = auth()->id(); // Get the authenticated student's ID
        $result = $this->quizRepository->getQuizResults($quizId, $studentId);

        if (!$result['status']) {
            return ApiResponse::error($result['message'], 400);
        }

        return ApiResponse::success($result['data'], $result['message']);
    }

    public function getUserQuizResults($userId = null)
    {
        $studentId = $userId ?? auth()->id(); // Use provided user ID or authenticated user's ID
        $result = $this->quizRepository->getUserQuizResults($studentId);

        if (!$result['status']) {
            return ApiResponse::error($result['message'], 422);
        }

        return ApiResponse::success($result['data'], $result['message']);
    }

    public function destroy($id)
    {
        $result = $this->quizRepository->delete($id);

        if (!$result['status']) {
            return ApiResponse::error($result['message'], 400);
        }

        return ApiResponse::success(null, $result['message']);
    }
}
