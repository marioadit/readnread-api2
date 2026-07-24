<?php

namespace App\Repository;

use App\Models\AnswersHistory;
use App\Models\Quiz;
use App\Models\StudentAnswer;
use App\Models\User;
use App\Repository\Interfaces\IAnswerHistoryRepository;
use Illuminate\Support\Facades\Log;

class AnswerHistoryRepository implements IAnswerHistoryRepository
{
    protected $answersHistory;

    public function __construct(AnswersHistory $answersHistory)
    {
        $this->answersHistory = $answersHistory;
    }

    /**
     * Simpan satu record history setelah siswa submit quiz.
     * Hanya menyimpan nilai tertinggi per student+quiz.
     * Jika nilai sama, ambil yang terbaru (replace).
     * Jawaban lama (student_answers) yang tidak terpakai akan dihapus.
     */
    public function record(
        array $savedAnswers,
        int $quizId,
        int $studentId,
        float $totalScore,
        ?string $startTime = null
    ) {
        // --- Cek apakah sudah ada history sebelumnya untuk student+quiz ini ---
        $existingHistory = $this->answersHistory
            ->where('student_id', $studentId)
            ->where('quiz_id', $quizId)
            ->first();

        if ($existingHistory) {
            // Jika score baru LEBIH RENDAH dari yang sudah ada,
            // hapus student_answers yang baru saja disimpan (tidak terpakai)
            // dan kembalikan history yang lama (score tertinggi tetap dipertahankan).
            if ($totalScore < $existingHistory->total_score) {
                $newAnswerIds = collect($savedAnswers)->pluck('id')->toArray();
                StudentAnswer::whereIn('id', $newAnswerIds)->delete();

                Log::info('AnswerHistoryRepository: score baru lebih rendah, mempertahankan history lama', [
                    'student_id' => $studentId,
                    'quiz_id' => $quizId,
                    'new_score' => $totalScore,
                    'existing_score' => $existingHistory->total_score,
                ]);

                return $existingHistory;
            }

            // Score baru >= score lama → gantikan yang lama.
            // Hapus student_answers lama yang direferensikan oleh history lama.
            $oldAnswerIds = $existingHistory->student_answers_ids ?? [];
            if (!empty($oldAnswerIds)) {
                StudentAnswer::whereIn('id', $oldAnswerIds)->delete();
            }

            // Hapus record history lama
            $existingHistory->delete();

            Log::info('AnswerHistoryRepository: score baru >= score lama, menggantikan history', [
                'student_id' => $studentId,
                'quiz_id' => $quizId,
                'new_score' => $totalScore,
                'old_score' => $existingHistory->total_score,
            ]);
        }

        // --- Ambil snapshot metadata quiz & lesson ---
        $quiz = Quiz::with(['lesson.class'])->find($quizId);

        $lesson = $quiz?->lesson;
        $class = $lesson?->class;

        // --- Ambil snapshot metadata student ---
        $student = User::find($studentId);

        // --- Hitung agregat dari savedAnswers ---
        $totalQuestions = count($savedAnswers);
        $answeredQuestions = collect($savedAnswers)
            ->filter(fn($a) => !is_null($a->answer_text) || !is_null($a->answer_option_id))
            ->count();
        $correctAnswers = collect($savedAnswers)->filter(fn($a) => $a->is_correct)->count();

        // Max possible score = jumlah score dari tiap question yang dijawab
        $maxPossibleScore = collect($savedAnswers)
            ->sum(fn($a) => $a->question?->score ?? 0);

        $percentageScore = $maxPossibleScore > 0
            ? round(($totalScore / $maxPossibleScore) * 100, 2)
            : 0;

        $completionRate = $totalQuestions > 0
            ? round(($answeredQuestions / $totalQuestions) * 100, 2)
            : 0;

        $questionIds = collect($savedAnswers)->pluck('question_id')->toArray();
        $studentAnswersIds = collect($savedAnswers)->pluck('id')->toArray();

        Log::info('AnswerHistoryRepository: merekam history (best score)', [
            'student_id' => $studentId,
            'quiz_id' => $quizId,
            'total_score' => $totalScore,
            'correct' => $correctAnswers,
            'percentage' => $percentageScore,
        ]);

        return $this->answersHistory->create([
            'student_id' => $studentId,
            'class_id' => $class?->id,
            'quiz_id' => $quizId,
            'lesson_id' => $lesson?->id,

            // Snapshot — tidak berubah walau data master diedit
            'quiz_title' => $quiz?->title,
            'lesson_title' => $lesson?->title,
            'student_name' => $student?->name,
            'class_name' => $class?->name,
            'class_level' => $class?->level,
            'class_semester' => $class?->semester,
            'class_academic_year' => $class?->academic_year,

            // Agregat
            'question_ids' => $questionIds,
            'student_answers_ids' => $studentAnswersIds,
            'total_questions' => $totalQuestions,
            'answered_questions' => $answeredQuestions,
            'correct_answers' => $correctAnswers,
            'total_score' => $totalScore,
            'max_possible_score' => $maxPossibleScore,
            'percentage_score' => $percentageScore,
            'completion_rate' => $completionRate,

            'start_time' => $startTime,
            'end_time' => now(),
            'submitted_at' => now(),
        ]);
    }

    public function getAll()
    {
        return $this->answersHistory
            ->with(['student', 'class', 'quiz', 'lesson'])
            ->orderByDesc('submitted_at')
            ->get();
    }

    public function getByStudent(int $studentId)
    {
        return $this->answersHistory
            ->with(['quiz', 'lesson', 'class'])
            ->where('student_id', $studentId)
            ->orderByDesc('submitted_at')
            ->get();
    }

    public function getByQuiz(int $quizId)
    {
        return $this->answersHistory
            ->with(['student', 'class'])
            ->where('quiz_id', $quizId)
            ->orderByDesc('submitted_at')
            ->get();
    }

    public function getByDetail(int $studentId, int $quizId)
    {
        return $this->answersHistory
            ->with(['student', 'class', 'quiz', 'lesson'])
            ->where('student_id', $studentId)
            ->where('quiz_id', $quizId)
            ->orderByDesc('submitted_at')
            ->first();
    }

}

