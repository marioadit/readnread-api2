<?php

namespace App\Repository;

use App\Models\AnswersHistory;
use App\Models\StudentScoreRecap;
use App\Models\User;
use App\Repository\Interfaces\IStudentScoreRecapRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentScoreRecapRepository implements IStudentScoreRecapRepository
{
    protected const CLASS_LEVELS = [
        'kelas 1',
        'kelas 2',
        'kelas 3',
        'kelas 4',
        'kelas 5',
        'kelas 6',
    ];

    /**
     * Hitung ulang dan simpan rekap nilai untuk satu siswa pada level & semester tertentu.
     */
    public function recalculateForStudent(int $studentId, string $classLevel, string $semester): ?StudentScoreRecap
    {
        $normalizedLevel = strtolower(trim($classLevel));
        $normalizedSemester = strtolower(trim($semester));

        $histories = AnswersHistory::where('student_id', $studentId)
            ->where('class_level', $normalizedLevel)
            ->where('class_semester', $normalizedSemester)
            ->get();

        if ($histories->isEmpty()) {
            StudentScoreRecap::where('student_id', $studentId)
                ->where('class_level', $normalizedLevel)
                ->where('semester', $normalizedSemester)
                ->delete();

            return null;
        }

        $totalQuizzes = $histories->count();
        $averageScore = round($histories->avg('percentage_score'), 2);

        $recap = StudentScoreRecap::updateOrCreate(
            [
                'student_id' => $studentId,
                'class_level' => $normalizedLevel,
                'semester' => $normalizedSemester,
            ],
            [
                'average_score' => $averageScore,
                'total_quizzes' => $totalQuizzes,
            ]
        );

        Log::info('StudentScoreRecapRepository: rekap berhasil dihitung ulang', [
            'student_id' => $studentId,
            'class_level' => $normalizedLevel,
            'semester' => $normalizedSemester,
            'average_score' => $averageScore,
            'total_quizzes' => $totalQuizzes,
        ]);

        return $recap;
    }

    /**
     * Format data siswa dan riwayat nilainya menjadi matriks kelas 1-6 & semester 1-2.
     */
    public function formatStudentMatrix(User $student): array
    {
        $recaps = $student->scoreRecaps ?? collect();

        $gradesNested = [];
        $gradesFlat = [];

        foreach (self::CLASS_LEVELS as $index => $level) {
            $levelNumber = $index + 1; // 1 to 6
            $levelKey = 'kelas_' . $levelNumber;

            // Semester 1 = gasal/ganjil, Semester 2 = genap
            $sem1Recap = $recaps->first(function ($r) use ($level) {
                return strtolower($r->class_level) === $level && strtolower($r->semester) === 'gasal';
            });

            $sem2Recap = $recaps->first(function ($r) use ($level) {
                return strtolower($r->class_level) === $level && strtolower($r->semester) === 'genap';
            });

            $sem1Score = $sem1Recap ? (float) $sem1Recap->average_score : null;
            $sem2Score = $sem2Recap ? (float) $sem2Recap->average_score : null;

            $gradesNested[$levelKey] = [
                'semester_1' => $sem1Score,
                'semester_2' => $sem2Score,
            ];

            // Flat key format untuk kemudahan data-table di Frontend
            $gradesFlat["{$levelKey}_sem_1"] = $sem1Score;
            $gradesFlat["{$levelKey}_sem_2"] = $sem2Score;
        }

        $currentClass = $student->classesEnrolled->first();

        return [
            'student_id' => $student->id,
            'student_name' => $student->name,
            'student_email' => $student->email,
            'student_status' => $student->status,
            'current_class' => $currentClass ? [
                'id' => $currentClass->id,
                'name' => $currentClass->name,
                'level' => $currentClass->level,
                'semester' => $currentClass->semester,
                'academic_year' => $currentClass->academic_year,
            ] : null,
            'grades' => $gradesNested,
            'flat_grades' => $gradesFlat,
        ];
    }

    /**
     * Ambil data matriks rekap untuk seluruh siswa.
     */
    public function getRecapMatrix(array $filters = []): array
    {
        $query = User::select('users.*')
            ->where('role', 'siswa')
            ->addSelect([
                'enrolled_class_level' => DB::table('classes')
                    ->join('class_students', 'classes.id', '=', 'class_students.class_id')
                    ->whereColumn('class_students.student_id', 'users.id')
                    ->orderByDesc('class_students.id')
                    ->select('classes.level')
                    ->limit(1),
                'enrolled_class_name' => DB::table('classes')
                    ->join('class_students', 'classes.id', '=', 'class_students.class_id')
                    ->whereColumn('class_students.student_id', 'users.id')
                    ->orderByDesc('class_students.id')
                    ->select('classes.name')
                    ->limit(1),
            ])
            ->with(['classesEnrolled', 'scoreRecaps']);

        // Filter status (aktif / lulus / dll)
        if (!empty($filters['status'])) {
            $query->where('users.status', $filters['status']);
        }

        // Filter pencarian nama / email siswa
        if (!empty($filters['search'])) {
            $searchTerm = trim($filters['search']);
            $query->where(function ($q) use ($searchTerm) {
                $q->where('users.name', 'like', "%{$searchTerm}%")
                    ->orWhere('users.email', 'like', "%{$searchTerm}%");
            });
        }

        // Filter kelas tertentu
        if (!empty($filters['class_id'])) {
            $classId = (int) $filters['class_id'];
            $query->whereHas('classesEnrolled', function ($q) use ($classId) {
                $q->where('classes.id', $classId);
            });
        }

        // Urutan:
        // 1. Siswa yang memiliki kelas didahulukan di atas siswa tanpa kelas
        // 2. Jenjang kelas tertinggi dahulu (Kelas 6 -> Kelas 1)
        // 3. Nama kelas (A -> Z, e.g. Kelas 6 A, Kelas 6 B)
        // 4. Nama siswa (A -> Z)
        $query->orderByRaw('CASE WHEN enrolled_class_level IS NULL THEN 1 ELSE 0 END ASC')
            ->orderByRaw("CASE enrolled_class_level
                WHEN 'kelas 6' THEN 6
                WHEN 'kelas 5' THEN 5
                WHEN 'kelas 4' THEN 4
                WHEN 'kelas 3' THEN 3
                WHEN 'kelas 2' THEN 2
                WHEN 'kelas 1' THEN 1
                ELSE 0
            END DESC")
            ->orderBy('enrolled_class_name', 'asc')
            ->orderBy('users.name', 'asc');

        // Jika request meminta all data tanpa pagination (misal untuk export Excel)
        if (!empty($filters['all']) && filter_var($filters['all'], FILTER_VALIDATE_BOOLEAN)) {
            $students = $query->get();
            $matrix = $students->map(fn($student) => $this->formatStudentMatrix($student))->values();

            return [
                'students' => $matrix,
                'total' => $students->count(),
            ];
        }

        // Default: gunakan pagination agar loading tetap cepat
        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 15;
        $paginated = $query->paginate($perPage);

        $matrix = collect($paginated->items())->map(fn($student) => $this->formatStudentMatrix($student))->values();

        return [
            'students' => $matrix,
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ]
        ];
    }

    /**
     * Ambil rekap untuk satu siswa spesifik.
     */
    public function getRecapByStudent(int $studentId): ?array
    {
        $student = User::where('role', 'siswa')
            ->with(['classesEnrolled', 'scoreRecaps'])
            ->find($studentId);

        if (!$student) {
            return null;
        }

        return $this->formatStudentMatrix($student);
    }

    /**
     * Hitung ulang seluruh data dari answers_history untuk seluruh siswa.
     */
    public function recalculateAll(): int
    {
        // Ambil semua kombinasi student_id, class_level, class_semester dari answers_history
        $groups = AnswersHistory::whereNotNull('student_id')
            ->whereNotNull('class_level')
            ->whereNotNull('class_semester')
            ->select(
                'student_id',
                'class_level',
                'class_semester',
                DB::raw('COUNT(*) as total_quizzes'),
                DB::raw('ROUND(AVG(percentage_score), 2) as avg_score')
            )
            ->groupBy('student_id', 'class_level', 'class_semester')
            ->get();

        $processed = 0;

        DB::beginTransaction();
        try {
            // Bersihkan data rekap lama di dalam transaksi
            StudentScoreRecap::query()->delete();

            foreach ($groups as $group) {
                $level = strtolower(trim($group->class_level));
                $sem = strtolower(trim($group->class_semester));

                StudentScoreRecap::create([
                    'student_id' => $group->student_id,
                    'class_level' => $level,
                    'semester' => $sem,
                    'average_score' => (float) $group->avg_score,
                    'total_quizzes' => (int) $group->total_quizzes,
                ]);

                $processed++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('StudentScoreRecapRepository::recalculateAll error: ' . $e->getMessage());
            throw $e;
        }

        return $processed;
    }
}
