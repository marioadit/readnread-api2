<?php

namespace App\Http\Controllers\ApiClass;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Repository\Interface\ClassRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassApiController extends Controller
{
    protected $classRepository;

    public function __construct(ClassRepositoryInterface $classRepository)
    {
        $this->classRepository = $classRepository;
    }

    /**
     * Mendapatkan semua kelas
     */
    public function index()
    {
        $classes = $this->classRepository->getAll();
        return ApiResponse::success($classes);
    }

    /**
     * Mendapatkan detail kelas berdasarkan ID
     */
    public function show($id)
    {
        $class = $this->classRepository->getById($id);
        return ApiResponse::success($class);
    }

    /**
     * Membuat kelas baru
     */
    public function store(Request $request)
    {
        $result = $this->classRepository->create($request->all());

        if (!$result['status']) {
            return ApiResponse::error($result['message'], 400, $result['errors'] ?? null);
        }

        return ApiResponse::success($result['data'], $result['message'], 201);
    }

    /**
     * Mengupdate kelas
     */
    public function update(Request $request, $id)
    {
        $result = $this->classRepository->update($id, $request->all());

        if (!$result['status']) {
            return ApiResponse::error($result['message'], 400, $result['errors'] ?? null);
        }

        return ApiResponse::success($result['data'], $result['message']);
    }

    /**
     * Menghapus kelas
     */
    public function destroy($id)
    {
        $result = $this->classRepository->delete($id);

        if (!$result['status']) {
            return ApiResponse::error($result['message'], 400);
        }

        return ApiResponse::success(null, $result['message']);
    }

    /**
     * Mendapatkan kelas yang diajar oleh guru yang login
     */
    public function myClasses()
    {
        $teacherId = Auth::id();
        $classes = $this->classRepository->getClassesForTeacher($teacherId);
        return ApiResponse::success($classes);
    }

    /**
     * Mendapatkan siswa dalam kelas
     */
    public function students($classId)
    {
        $students = $this->classRepository->getStudentsInClass($classId);
        return ApiResponse::success($students);
    }

    /**
     * Menambahkan siswa ke kelas
     */
    public function addStudent(Request $request, $classId)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id'
        ]);

        $result = $this->classRepository->addStudentToClass($classId, $request->student_id);

        if (!$result['status']) {
            return ApiResponse::error($result['message'], 400);
        }

        return ApiResponse::success(null, $result['message']);
    }

    /**
     * Menghapus siswa dari kelas
     */
    public function removeStudent(Request $request, $classId)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id'
        ]);

        $result = $this->classRepository->removeStudentFromClass($classId, $request->student_id);

        return ApiResponse::success(null, $result['message']);
    }
}
