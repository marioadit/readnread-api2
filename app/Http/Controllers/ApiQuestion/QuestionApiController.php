<?php

namespace App\Http\Controllers\ApiQuestion;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Repository\Interfaces\QuestionRepositoryInterface;
use Illuminate\Http\Request;

class QuestionApiController extends Controller
{
    protected $questionRepository;

    public function __construct(QuestionRepositoryInterface $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

    /**
     * Get all questions with their relations
     */
    public function index()
    {
        $questions = $this->questionRepository->getAll();
        return ApiResponse::success($questions, 'Questions retrieved successfully');
    }

    /**
     * Get question details with relations by ID
     */
    public function show($id)
    {
        $question = $this->questionRepository->getWithRelations($id);
        return ApiResponse::success($question, 'Question retrieved successfully');
    }

    public function getStudentAnswers($id)
    {
        $studentAnswers = $this->questionRepository->getStudentAnswers($id);
        return ApiResponse::success($studentAnswers, 'Student answers retrieved successfully');
    }

    public function getAllStudentAnswers()
    {
        $studentAnswers = $this->questionRepository->getAllStudentAnswers();
        return ApiResponse::success($studentAnswers, 'Student answers retrieved successfully');
    }

    /**
     * Create a new question with options and match pairs
     */
    public function store(Request $request)
    {
        $result = $this->questionRepository->createWithRelations($request->all());

        if (!$result['status']) {
            return ApiResponse::error($result['message'], 400, $result['errors'] ?? null);
        }

        return ApiResponse::success($result['data'], $result['message'], 201);
    }

    /**
     * Update question with options and match pairs
     */
    public function update(Request $request, $id)
    {
        $result = $this->questionRepository->updateWithRelations($id, $request->all());

        if (!$result['status']) {
            return ApiResponse::error($result['message'], 400, $result['errors'] ?? null);
        }

        return ApiResponse::success($result['data'], $result['message']);
    }

    /**
     * Delete question and its relations
     */
    public function destroy($id)
    {
        $result = $this->questionRepository->delete($id);

        if (!$result['status']) {
            return ApiResponse::error($result['message'], 400);
        }

        return ApiResponse::success(null, 'Question and its relations deleted successfully');
    }

    /**
     * Get detailed question information with all relations
     */
    public function detail($id)
    {
        $result = $this->questionRepository->getDetailQuestion($id);

        if (!$result['status']) {
            return ApiResponse::error($result['message'], 400);
        }

        return ApiResponse::success($result['data'], $result['message']);
    }

}
