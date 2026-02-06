<?php

namespace App\Http\Controllers\ApiLesson;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Repository\Interface\LessonRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LessonApiController extends Controller
{
    protected $lessonRepository;

    public function __construct(LessonRepositoryInterface $lessonRepository)
    {
        $this->lessonRepository = $lessonRepository;
    }

    /**
     * Mendapatkan semua pelajaran
     */
    public function index()
    {
        $lessons = $this->lessonRepository->getAll();
        return ApiResponse::success($lessons);
    }

    /**
     * Mendapatkan detail pelajaran berdasarkan ID
     */
    public function show($id)
    {
        $lesson = $this->lessonRepository->getById($id);
        return ApiResponse::success($lesson);
    }

    /**
     * Membuat pelajaran baru
     */
    public function store(Request $request)
    {
        $result = $this->lessonRepository->create($request->all());

        if (!$result['status']) {
            return ApiResponse::error($result['message'], 400, $result['errors'] ?? null);
        }

        return ApiResponse::success($result['data'], $result['message'], 201);
    }

    /**
     * Mengupdate pelajaran
     */
    public function update(Request $request, $id)
    {

        $result = $this->lessonRepository->update($id, $request);

        if (!$result['status']) {
            return ApiResponse::error($result['message'], 400, $result['errors'] ?? null);
        }

        return ApiResponse::success($result['data'], $result['message']);
    }

    /**
     * Menghapus pelajaran
     */
    public function destroy($id)
    {
        $result = $this->lessonRepository->delete($id);

        if (!$result['status']) {
            return ApiResponse::error($result['message'], 400);
        }

        return ApiResponse::success(null, $result['message']);
    }

    /**
     * Mendapatkan pelajaran dalam kelas
     */
    public function lessonsByClass($classId)
    {
        $lessons = $this->lessonRepository->getLessonsByClass($classId);
        return ApiResponse::success($lessons);
    }

    public function getLessonsByQuiz($lessonId)
    {
        $lessons = $this->lessonRepository->getLessonsByQuiz($lessonId);
        return ApiResponse::success($lessons);
    }
}
