<?php

namespace App\Repository;

use App\Models\AnswersHistory;
use App\Models\Quiz;
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
     * Data diambil dari $savedAnswers yang merupakan hasil insert student_answers
     * dalam transaksi yang sama (dikelola oleh StudentAnswerService).
     */
    public function record(
        array $savedAnswers,
        int $quizId,
        int $studentId,
        float $totalScore,
        ?string $startTime = null
    ) {
        // --- Ambil snapshot metadata quiz & lesson ---
        $quiz = Quiz::with(['lesson.class'])->find($quizId);

        $lesson      = $quiz?->lesson;
        $class       = $lesson?->class;

        // --- Ambil snapshot metadata student ---
        $student = User::find($studentId);

        // --- Hitung agregat dari savedAnswers ---
        $totalQuestions    = count($savedAnswers);
        $answeredQuestions = collect($savedAnswers)
            ->filter(fn($a) => !is_null($a->answer_text) || !is_null($a->answer_option_id))
            ->count();
        $correctAnswers = collect($savedAnswers)->filter(fn($a) => $a->is_correct)->count();

        // Max possible score = jumlah score dari tiap question yang dijawab
        $maxPossibleScore = collect($savedAnswers)
            ->sum(fn($a) => $a->question?->score ?? 0);

        $percentageScore  = $maxPossibleScore > 0
            ? round(($totalScore / $maxPossibleScore) * 100, 2)
            : 0;

        $completionRate = $totalQuestions > 0
            ? round(($answeredQuestions / $totalQuestions) * 100, 2)
            : 0;

        $questionIds       = collect($savedAnswers)->pluck('question_id')->toArray();
        $studentAnswersIds = collect($savedAnswers)->pluck('id')->toArray();

        Log::info('AnswerHistoryRepository: merekam history', [
            'student_id'    => $studentId,
            'quiz_id'       => $quizId,
            'total_score'   => $totalScore,
            'correct'       => $correctAnswers,
            'percentage'    => $percentageScore,
        ]);

        return $this->answersHistory->create([
            'student_id'            => $studentId,
            'class_id'              => $class?->id,
            'quiz_id'               => $quizId,
            'lesson_id'             => $lesson?->id,

            // Snapshot — tidak berubah walau data master diedit
            'quiz_title'            => $quiz?->title,
            'lesson_title'          => $lesson?->title,
            'student_name'          => $student?->name,
            'class_name'            => $class?->name,
            'class_level'           => $class?->level,
            'class_semester'        => $class?->semester,
            'class_academic_year'   => $class?->academic_year,

            // Agregat
            'question_ids'          => $questionIds,
            'student_answers_ids'   => $studentAnswersIds,
            'total_questions'       => $totalQuestions,
            'answered_questions'    => $answeredQuestions,
            'correct_answers'       => $correctAnswers,
            'total_score'           => $totalScore,
            'max_possible_score'    => $maxPossibleScore,
            'percentage_score'      => $percentageScore,
            'completion_rate'       => $completionRate,

            'start_time'            => $startTime,
            'end_time'              => now(),
            'submitted_at'          => now(),
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
}
