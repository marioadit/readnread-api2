<?php

namespace App\Repository;

use App\Models\Classes;
use App\Models\User;
use App\Repository\Interfaces\ClassRepositoryInterface;
use Illuminate\Support\Facades\Validator;

class ClassRepository implements ClassRepositoryInterface

{
    protected $model;

    public function __construct(Classes $classes)
    {
        $this->model = $classes;
    }

    public function getAll()
    {
        return $this->model->with('teacher')->get();
    }

    public function getById($id)
    {
        return $this->model->with(['teacher', 'students'])->findOrFail($id);
    }

    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'level' => 'required|in:kelas 1,kelas 2,kelas 3,kelas 4,kelas 5,kelas 6',
            'teacher_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ];
        }

        $class = $this->model->create($data);

        return [
            'status' => true,
            'message' => 'Kelas berhasil dibuat',
            'data' => $class
        ];
    }

    public function update($id, array $data)
    {
        $validator = Validator::make($data, [
            'name' => 'sometimes|string|max:255',
            'level' => 'sometimes|in:kelas 1,kelas 2,kelas 3,kelas 4,kelas 5,kelas 6',
            'teacher_id' => 'sometimes|exists:users,id'
        ]);

        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ];
        }

        $class = $this->model->findOrFail($id);
        $class->update($data);

        return [
            'status' => true,
            'message' => 'Kelas berhasil diperbarui',
            'data' => $class
        ];
    }

    public function delete($id)
    {
        $class = $this->model->findOrFail($id);
        $class->delete();

        return [
            'status' => true,
            'message' => 'Kelas berhasil dihapus'
        ];
    }

    public function getClassesForTeacher($teacherId)
    {
        return $this->model->where('teacher_id', $teacherId)->get();
    }

    public function getStudentsInClass($classId)
    {
        $class = $this->model->findOrFail($classId);
        return $class->students;
    }

    public function addStudentToClass($classId, $studentId)
    {
        $class = $this->model->findOrFail($classId);
        $student = User::where('role', 'siswa')->findOrFail($studentId);

        // Cek apakah siswa sudah ada di kelas
        if ($class->students()->where('users.id', $studentId)->exists()) {
            return [
                'status' => false,
                'message' => 'Siswa sudah terdaftar dalam kelas ini'
            ];
        }

        $class->students()->attach($studentId);

        return [
            'status' => true,
            'message' => 'Siswa berhasil ditambahkan ke kelas'
        ];
    }

    public function removeStudentFromClass($classId, $studentId)
    {
        $class = $this->model->findOrFail($classId);
        $class->students()->detach($studentId);

        return [
            'status' => true,
            'message' => 'Siswa berhasil dihapus dari kelas'
        ];
    }
} 