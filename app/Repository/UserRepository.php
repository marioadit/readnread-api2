<?php

namespace App\Repository;

use App\Models\User;
use App\Models\Classes;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\AnswersHistory;
use App\Repository\Interfaces\UserRepositoryInterface;
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
            'status' => 'sometimes|in:aktif,lulus,pindah',
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

    public function resetPassword($id)
    {
        $user = $this->model->findOrFail($id);
        $user->password = Hash::make('password');
        $user->save();
        return [
            'status' => true,
            'message' => 'Password berhasil direset',
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
            'role' => 'sometimes|in:siswa,guru,admin',
            'status' => 'sometimes|in:aktif,lulus,pindah'
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
        $data = $this->model->with('classesEnrolled')
            ->where('role', 'siswa')
            ->where('status', 'aktif')
            ->get()
            ->sortBy([
                fn ($a, $b) => ($a->classesEnrolled->first()?->id ?? 99999) <=> ($b->classesEnrolled->first()?->id ?? 99999),
                fn ($a, $b) => strnatcasecmp($a->name, $b->name)
            ])
            ->values();

        Log::info($data);
        return [
            'status' => true,
            'message' => 'Data siswa aktif berhasil ditemukan',
            'data' => $data
        ];
    }

    public function getUserStudentGraduated()
    {
        $data = $this->model->with('classesEnrolled')
            ->where('role', 'siswa')
            ->where('status', '!=', 'aktif')
            ->get()
            ->sortBy([
                // Status 'lulus' diprioritaskan paling atas, kemudian status lainnya
                fn ($a, $b) => ($a->status === 'lulus' ? 1 : 2) <=> ($b->status === 'lulus' ? 1 : 2),
                // Waktu update terbaru (updated_at DESC)
                fn ($a, $b) => strtotime($b->updated_at ?? 0) <=> strtotime($a->updated_at ?? 0)
            ])
            ->values();

        Log::info($data);
        return [
            'status' => true,
            'message' => 'Data siswa lulus/alumni berhasil ditemukan',
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

        $classes = $user->classes()->orderBy('classes.id', 'asc')->with(['lessons' => function ($query) {
            $query->orderBy('id', 'asc');
        }])->get();

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
        }
        catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Error retrieving user quiz: ' . $e->getMessage()
            ];
        }
    }

    public function getTeacherDashboard()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return [
                    'status' => false,
                    'message' => 'Unauthenticated'
                ];
            }

            $teacherId = $user->id;

            // Get classes taught by this teacher
            $classes = Classes::where('teacher_id', $teacherId)->get();
            $classIds = $classes->pluck('id');

            $totalClasses = $classes->count();

            // Total students in teacher's classes
            $totalStudents = DB::table('class_students')
                ->whereIn('class_id', $classIds)
                ->distinct('student_id')
                ->count('student_id');

            // Lessons in teacher's classes
            $lessons = Lesson::whereIn('class_id', $classIds)->get();
            $lessonIds = $lessons->pluck('id');

            // Quizzes in teacher's classes
            $quizzes = Quiz::whereIn('lesson_id', $lessonIds)->get();
            $totalQuizzes = $quizzes->count();
            $activeQuizzes = $quizzes->where('is_active', true)->count();

            // Completed quizzes from AnswersHistory
            $answersHistory = AnswersHistory::whereIn('class_id', $classIds)->get();
            $completedQuizzesCount = $answersHistory->count();

            // Perfect scores (percentage_score >= 100 or correct_answers == total_questions)
            $perfectScoresCount = $answersHistory->filter(function ($history) {
                return $history->percentage_score >= 100 || 
                       ($history->total_questions > 0 && $history->correct_answers == $history->total_questions);
            })->count();

            // Completed lessons count
            $completedLessonsCount = $answersHistory->pluck('lesson_id')->unique()->filter()->count();
            $totalLessonsCount = $lessons->count();

            // Progress breakdown per class
            $classProgress = $classes->map(function ($class) use ($answersHistory) {
                $classLessons = Lesson::where('class_id', $class->id)->get();
                $classLessonIds = $classLessons->pluck('id');
                $classQuizzesCount = Quiz::whereIn('lesson_id', $classLessonIds)->count();

                $classHistory = $answersHistory->where('class_id', $class->id);
                $completedCount = $classHistory->count();

                $studentsCount = DB::table('class_students')->where('class_id', $class->id)->count();
                $totalPossible = $studentsCount * $classQuizzesCount;

                $percentage = $totalPossible > 0 ? round(($completedCount / $totalPossible) * 100) : 0;

                return [
                    'class_id' => $class->id,
                    'class_name' => $class->name,
                    'level' => $class->level,
                    'completed_quizzes' => $completedCount,
                    'total_quizzes' => $classQuizzesCount,
                    'total_students' => $studentsCount,
                    'percentage' => $percentage,
                ];
            });

            // Students enrolled in teacher's classes
            $students = User::whereIn('id', function ($query) use ($classIds) {
                $query->select('student_id')->from('class_students')->whereIn('class_id', $classIds);
            })->with('classesEnrolled')->get();

            return [
                'status' => true,
                'message' => 'Dashboard teacher retrieved successfully',
                'data' => [
                    'stats' => [
                        'quizzes_completed' => [
                            'completed' => $completedQuizzesCount,
                            'total' => $totalQuizzes,
                            'formatted' => $completedQuizzesCount . '/' . ($totalQuizzes > 0 ? $totalQuizzes : 0)
                        ],
                        'lessons_completed' => [
                            'completed' => $completedLessonsCount,
                            'total' => $totalLessonsCount,
                            'formatted' => $completedLessonsCount . '/' . $totalLessonsCount
                        ],
                        'active_quizzes' => [
                            'active' => $activeQuizzes,
                            'total' => $totalQuizzes,
                            'formatted' => $activeQuizzes . '/' . $totalQuizzes
                        ],
                        'perfect_scores' => [
                            'perfect' => $perfectScoresCount,
                            'total' => $completedQuizzesCount,
                            'formatted' => $perfectScoresCount . '/' . ($completedQuizzesCount > 0 ? $completedQuizzesCount : 0)
                        ],
                        'total_students' => $totalStudents,
                        'total_classes' => $totalClasses,
                    ],
                    'class_progress' => $classProgress,
                    'students' => $students,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Error retrieving teacher dashboard: ' . $e->getMessage()
            ];
        }
    }

    public function getAdminDashboard()
    {
        try {
            $totalAdmin = $this->model->where('role', 'admin')->count();
            $totalGuru = $this->model->where('role', 'guru')->count();
            $totalSiswa = $this->model->where('role', 'siswa')->where('status', 'aktif')->count();
            $totalAlumni = $this->model->where('role', 'siswa')->where('status', '!=', 'aktif')->count();
            $totalUsers = $this->model->count();
            $totalClasses = Classes::count();

            $studentsPerClass = Classes::withCount('students')
                ->with('teacher:id,name,email')
                ->get()
                ->map(function ($class) {
                    return [
                        'class_id' => $class->id,
                        'class_name' => $class->name,
                        'level' => $class->level,
                        'semester' => $class->semester,
                        'academic_year' => $class->academic_year,
                        'teacher_name' => $class->teacher ? $class->teacher->name : null,
                        'student_count' => $class->students_count,
                    ];
                });

            return [
                'status' => true,
                'message' => 'Dashboard admin retrieved successfully',
                'data' => [
                    'stats' => [
                        'total_admin' => $totalAdmin,
                        'total_guru' => $totalGuru,
                        'total_siswa' => $totalSiswa,
                        'total_alumni' => $totalAlumni,
                        'total_users' => $totalUsers,
                        'total_classes' => $totalClasses,
                    ],
                    'students_per_class' => $studentsPerClass,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Error retrieving admin dashboard: ' . $e->getMessage()
            ];
        }
    }
}
