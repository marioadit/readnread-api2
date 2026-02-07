<?php

namespace App\Repository;

use App\Models\User;
use App\Repository\Interface\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserRepository implements UserRepositoryInterface
{

    protected $model;

    public function __construct(User $post)
    {
        $this->model = $post;
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function getById($id)
    {
        return $this->model->with('classesEnrolled')->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $post = $this->model->findOrFail($id);
        $post->update($data);
        return $post;
    }

    public function delete($id)
    {
        $post = $this->model->findOrFail($id);
        return $post->delete();
    }

    public function getUserProfile()
    {
        $user = Auth::user()->id;
        return $this->model->findOrFail($user);
    }

    public function updateProfile($id, array $data)
    {
        $validator = Validator::make($data, [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
        ]);

        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ];
        }

        $user = $this->model->findOrFail($id);
        $user->update($data);
        return [
            'status' => true,
            'message' => 'Profil berhasil diperbarui',
            'data' => $user
        ];
    }

    public function updatePassword($id, array $data)
    {
        $validator = Validator::make($data, [
            'current_password' => 'required',
            'password' => 'required|string
            |min:8
            |confirmed',
            'password_confirmation' => 'required|same:password'
        ]);

        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ];
        }

        // Load user by id (caller passes authenticated user's id)
        $user = $this->model->findOrFail($id);

        // Check if current password matches
        if (!Hash::check($data['current_password'], $user->password)) {
            return [
                'status' => false,
                'message' => 'Password saat ini salah',
                'errors' => ['current_password' => ['Password saat ini tidak sesuai dengan catatan kami']]
            ];
        }

        // Update password
        $user->password = Hash::make($data['password']);
        $user->save();

        return [
            'status' => true,
            'message' => 'Password berhasil diperbarui',
            'data' => $user
        ];
    }

    public function updateUser($id, array $data)
    {
        $validator = Validator::make($data, [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'sometimes|min:8',
            'role' => 'sometimes|in:siswa,guru,admin'
        ]);

        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ];
        }

        $user = $this->model->findOrFail($id);
        $user->update($data);
        return [
            'status' => true,
            'message' => 'User berhasil diperbarui',
            'data' => $user
        ];
    }

    public function getUserStudent()
    {
        $data = $this->model->with('classesEnrolled')->where('role', 'siswa')->get();
        Log::info($data);
        return [
            'status' => true,
            'message' => 'Data siswa berhasil ditemukan',
            'data' => $data
        ];
    }

    public function getUserTeacher()
    {
        $data = $this->model->with('classesTeaching')->where('role', 'guru')->get();
        Log::info($data);
        return [
            'status' => true,
            'message' => 'Data guru berhasil ditemukan',
            'data' => $data
        ];
    }

    public function getClassUser()
    {
        $user = Auth::user();

        $classes = $user->classes()->with('teacher')->get();

        Log::info($classes);
        if ($classes->isNotEmpty()) {
            return $classes;
        }

        return $classes;
    }

    public function getUserLesson()
    {
        $user = Auth::user();

        $classes = $user->classes()->with('lessons')->get();

        $lessons = $classes->flatMap(function ($class) {
            return $class->lessons;
        });
        Log::info($lessons);
        return $lessons;
    }

    public function getStudentAnswers($id)
    {
        return $this->model->with(['studentAnswers.question.options'])->findOrFail($id);
    }

    public function getAllStudentAnswers()
    {
        $user = Auth::user();
        Log::info($user);
        return $this->model->with(['studentAnswers.question.options'])->findOrFail($user->id);
    }

    public function getStudentAnswersAdmin($idstudent, $idanswer)
    {
        return $this->model->with([
            'studentAnswers' => function ($query) use ($idanswer) {
                $query->where('id', $idanswer);
            },
            'studentAnswers.question.options'
        ])->findOrFail($idstudent);
    }

    public function getAllStudentAnswersAdmin($idstudent)
    {
        return $this->model->with(['studentAnswers.question.options'])->findOrFail($idstudent);
    }

    public function getUserQuiz()
    {
        try {

            $quiz = DB::table('users')
                ->join('class_students as cs', 'users.id', '=', 'cs.student_id')
                ->join('classes as c', 'cs.class_id', '=', 'c.id')
                ->select('users.*', 'cs.class_id', 'cs.created_at as enrolled_at', 'c.name as class_name', 'c.level as kelas')
                ->get();

            Log::info($quiz);

            return [
                'status' => true,
                'message' => 'User quiz retrieved successfully',
                'data' => $quiz
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Error retrieving user quiz: ' . $e->getMessage()
            ];
        }
    }
}
