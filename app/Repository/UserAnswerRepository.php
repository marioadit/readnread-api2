<?php

namespace App\Repository;

use App\Models\Option;
use App\Models\Question;
use App\Models\StudentAnswer;
use App\Repository\Interfaces\IUserAnswerRepository;
use App\Service\AIGradingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class UserAnswerRepository implements IUserAnswerRepository
{
    protected $studentAnswer;
    protected AIGradingService $aiGrading;

    public function __construct(StudentAnswer $studentAnswer, AIGradingService $aiGrading)
    {
        $this->studentAnswer = $studentAnswer;
        $this->aiGrading = $aiGrading;
    }

    public function getAll()
    {
        return $this->studentAnswer->with(['student', 'question', 'selectedOption'])->get();
    }

    public function getById($id)
    {
        return $this->studentAnswer->with(['student', 'question', 'selectedOption'])->find($id);
    }

    public function store(Request $request): array
    {
        $data    = $request->all();
        $answers = $data['answers'];

        // --- Validasi: quiz_id harus ada dan valid ---
        $quizId = $data['quiz_id'] ?? null;
        if (!$quizId) {
            return [
                'status'  => false,
                'message' => 'quiz_id wajib disertakan dalam request.',
            ];
        }

        $quiz = \App\Models\Quiz::find($quizId);
        if (!$quiz) {
            return [
                'status'  => false,
                'message' => "Quiz dengan id {$quizId} tidak ditemukan.",
            ];
        }

        // Ambil semua questions sekaligus (hindari N+1)
        $questionIds = array_map(fn($a) => $a['question_id'], $answers);
        $questions   = Question::with(['options', 'matchPairs', 'essayAnswers'])
            ->whereIn('id', $questionIds)
            ->get()
            ->keyBy('id');

        // --- Validasi: semua question_id harus milik quiz_id yang diberikan ---
        foreach ($questionIds as $qId) {
            if (!isset($questions[$qId])) {
                return [
                    'status'  => false,
                    'message' => "question_id {$qId} tidak ditemukan.",
                ];
            }
            if ((int) $questions[$qId]->quiz_id !== (int) $quizId) {
                return [
                    'status'  => false,
                    'message' => "question_id {$qId} bukan milik quiz_id {$quizId}.",
                ];
            }
        }

        $results          = [];
        $total_quiz_score = 0;

        foreach ($answers as $answer) {
            $question       = $questions[$answer['question_id']];
            $score          = 0;
            $is_correct     = false;
            $question_score = 0;

            switch ($question->type) {
                case 'essay':
                case 'guess_image':
                    // Get all correct answers from essay_answers table (supports multiple answers separated by |)
                    $correctAnswers = $question->essayAnswers;
                    if ($correctAnswers->isNotEmpty() && $answer['answer_text']) {
                        $studentAnswerText = $this->normalizeAnswerText($answer['answer_text']);
                        $correctAnswerTexts = $correctAnswers->pluck('option_text')->map(
                            fn($t) => $this->normalizeAnswerText($t)
                        )->toArray();

                        // Step 1: Exact match (fast, no API cost)
                        if (in_array($studentAnswerText, $correctAnswerTexts)) {
                            Log::info('AIGradingService: exact match ditemukan', [
                                'student_answer' => $answer['answer_text'],
                            ]);
                            $score          = $question->score;
                            $question_score = $score;
                            $is_correct     = true;
                        } else {
                            // Step 2: AI semantic grading as fallback (returns 0.0–1.0 ratio)
                            Log::info('AIGradingService: exact match gagal, memanggil AI grading', [
                                'student_answer'  => $answer['answer_text'],
                                'correct_answers' => $correctAnswers->pluck('option_text')->toArray(),
                            ]);
                            $rawCorrectAnswers = $correctAnswers->pluck('option_text')->toArray();
                            $aiRatio = $this->aiGrading->gradeAnswer(
                                $answer['answer_text'],
                                $rawCorrectAnswers,
                                $question->question_text ?? '',
                                $question->type
                            );

                            if ($aiRatio >= 1.0) {
                                // Inti / makna jawaban SAMA → full score
                                $score          = $question->score;
                                $question_score = $score;
                                $is_correct     = true;
                            } elseif ($aiRatio > 0) {
                                // Jawaban relevan sebagian → partial score
                                $score          = round($aiRatio * $question->score, 2);
                                $question_score = $score;
                                $is_correct     = false;
                            }
                            // else aiRatio == 0 → tidak dapat poin (default)
                        }
                    }
                    break;

                case 'choice_image':
                    // Validasi: choice_image tidak boleh menerima answer_text
                    if (!empty($answer['answer_text'])) {
                        return [
                            'status'  => false,
                            'message' => "Soal choice_image (question_id: {$answer['question_id']}) tidak boleh menerima answer_text. Gunakan answer_option_id.",
                        ];
                    }

                    if ($answer['answer_option_id'] ?? null) {
                        $selectedOption = $question->options->firstWhere('id', $answer['answer_option_id']);

                        // Validasi: answer_option_id harus milik question ini
                        if (!$selectedOption) {
                            return [
                                'status'  => false,
                                'message' => "answer_option_id {$answer['answer_option_id']} tidak valid untuk question_id {$answer['question_id']}.",
                            ];
                        }

                        $answer['answer_image'] = $selectedOption->option_image;
                        if ($selectedOption->is_correct) {
                            $score          = $question->score;
                            $question_score = $score;
                            $is_correct     = true;
                        }
                    }
                    break;

                case 'match_image':
                    // Validasi: match_image tidak boleh menerima answer_option_id
                    if (!empty($answer['answer_option_id'])) {
                        return [
                            'status'  => false,
                            'message' => "Soal match_image (question_id: {$answer['question_id']}) tidak boleh menerima answer_option_id. Gunakan answer_text berformat JSON pasangan.",
                        ];
                    }

                    if ($answer['answer_text'] ?? null) {
                        $userPairs = json_decode($answer['answer_text'], true);

                        // Validasi: answer_text harus berupa JSON array yang valid
                        if ($userPairs === null) {
                            return [
                                'status'  => false,
                                'message' => "answer_text untuk soal match_image (question_id: {$answer['question_id']}) harus berupa JSON array yang valid.",
                            ];
                        }

                        // Validasi: setiap pair harus memiliki key left_id dan right_id
                        foreach ($userPairs as $idx => $userPair) {
                            if (!isset($userPair['left_id']) || !isset($userPair['right_id'])) {
                                return [
                                    'status'  => false,
                                    'message' => "Pasangan ke-" . ($idx + 1) . " pada soal match_image (question_id: {$answer['question_id']}) harus memiliki 'left_id' dan 'right_id'.",
                                ];
                            }
                        }

                        // Validasi: left_id dan right_id harus sesuai dengan matchPairs milik question ini
                        $validPairIds = $question->matchPairs->pluck('id')->toArray();
                        foreach ($userPairs as $userPair) {
                            if (!in_array($userPair['left_id'], $validPairIds)) {
                                return [
                                    'status'  => false,
                                    'message' => "left_id {$userPair['left_id']} tidak valid untuk question_id {$answer['question_id']}.",
                                ];
                            }
                            if (!in_array($userPair['right_id'], $validPairIds)) {
                                return [
                                    'status'  => false,
                                    'message' => "right_id {$userPair['right_id']} tidak valid untuk question_id {$answer['question_id']}.",
                                ];
                            }
                        }

                        $correctPairs = $question->matchPairs->map(function ($pair) {
                            return [
                                'left_id'  => $pair->id,
                                'right_id' => $pair->id,
                            ];
                        })->toArray();

                        $correctCount = 0;
                        foreach ($userPairs as $userPair) {
                            foreach ($correctPairs as $correctPair) {
                                if (
                                    $userPair['left_id'] == $correctPair['left_id'] &&
                                    $userPair['right_id'] == $correctPair['right_id']
                                ) {
                                    $correctCount++;
                                    break;
                                }
                            }
                        }

                        if ($correctCount > 0) {
                            $totalPairs     = count($correctPairs);
                            $score          = ($correctCount / $totalPairs) * $question->score;
                            $question_score = $score;
                            $is_correct     = ($correctCount == $totalPairs);
                        }
                    }
                    break;
            }

            $studentAnswer = $this->studentAnswer->create([
                'student_id'       => $answer['student_id'],
                'question_id'      => $answer['question_id'],
                'answer_text'      => $answer['answer_text'] ?? null,
                'answer_option_id' => $answer['answer_option_id'] ?? null,
                'answer_image'     => $answer['answer_image'] ?? null,
                'submitted_at'     => $answer['submitted_at'] ?? now(),
                'is_correct'       => $is_correct,
                'score'            => $score,
                'feedback'         => null,
            ]);

            $total_quiz_score += $question_score;
            $results[]         = $studentAnswer;
        }

        return [
            'answers'          => $results,
            'total_quiz_score' => $total_quiz_score,
        ];
    }

    public function update(Request $request): array
    {
        $data    = $request->all();
        $answers = $data['answers'];
        $results          = [];
        $total_quiz_score = 0;

        // --- Validasi: quiz_id harus ada dan valid ---
        $quizId = $data['quiz_id'] ?? null;
        if (!$quizId) {
            return [
                'status'  => false,
                'message' => 'quiz_id wajib disertakan dalam request.',
            ];
        }

        $quiz = \App\Models\Quiz::find($quizId);
        if (!$quiz) {
            return [
                'status'  => false,
                'message' => "Quiz dengan id {$quizId} tidak ditemukan.",
            ];
        }

        // Ambil semua questions sekaligus (hindari N+1)
        $questionIds = array_map(fn($a) => $a['question_id'], $answers);
        $questions   = Question::with(['options', 'matchPairs', 'essayAnswers'])
            ->whereIn('id', $questionIds)
            ->get()
            ->keyBy('id');

        // --- Validasi: semua question_id harus milik quiz_id yang diberikan ---
        foreach ($questionIds as $qId) {
            if (!isset($questions[$qId])) {
                return [
                    'status'  => false,
                    'message' => "question_id {$qId} tidak ditemukan.",
                ];
            }
            if ((int) $questions[$qId]->quiz_id !== (int) $quizId) {
                return [
                    'status'  => false,
                    'message' => "question_id {$qId} bukan milik quiz_id {$quizId}.",
                ];
            }
        }

        foreach ($answers as $answer) {
            $question       = $questions[$answer['question_id']];
            $score          = 0;
            $is_correct     = false;
            $question_score = 0;

            switch ($question->type) {
                case 'essay':
                case 'guess_image':
                    $correctAnswers = $question->essayAnswers;
                    if ($correctAnswers->isNotEmpty() && $answer['answer_text']) {
                        $studentAnswerText  = $this->normalizeAnswerText($answer['answer_text']);
                        $correctAnswerTexts = $correctAnswers->pluck('option_text')->map(
                            fn($t) => $this->normalizeAnswerText($t)
                        )->toArray();

                        if (in_array($studentAnswerText, $correctAnswerTexts)) {
                            Log::info('AIGradingService: exact match ditemukan', [
                                'student_answer' => $answer['answer_text'],
                            ]);
                            $score          = $question->score;
                            $question_score = $score;
                            $is_correct     = true;
                        } else {
                            Log::info('AIGradingService: exact match gagal, memanggil AI grading', [
                                'student_answer'  => $answer['answer_text'],
                                'correct_answers' => $correctAnswers->pluck('option_text')->toArray(),
                            ]);
                            $rawCorrectAnswers = $correctAnswers->pluck('option_text')->toArray();
                            $aiRatio = $this->aiGrading->gradeAnswer(
                                $answer['answer_text'],
                                $rawCorrectAnswers,
                                $question->question_text ?? '',
                                $question->type
                            );

                            if ($aiRatio >= 1.0) {
                                $score          = $question->score;
                                $question_score = $score;
                                $is_correct     = true;
                            } elseif ($aiRatio > 0) {
                                $score          = round($aiRatio * $question->score, 2);
                                $question_score = $score;
                                $is_correct     = false;
                            }
                        }
                    }
                    break;

                case 'choice_image':
                    // Validasi: choice_image tidak boleh menerima answer_text
                    if (!empty($answer['answer_text'])) {
                        return [
                            'status'  => false,
                            'message' => "Soal choice_image (question_id: {$answer['question_id']}) tidak boleh menerima answer_text. Gunakan answer_option_id.",
                        ];
                    }

                    if ($answer['answer_option_id'] ?? null) {
                        $selectedOption = $question->options->firstWhere('id', $answer['answer_option_id']);

                        // Validasi: answer_option_id harus milik question ini
                        if (!$selectedOption) {
                            return [
                                'status'  => false,
                                'message' => "answer_option_id {$answer['answer_option_id']} tidak valid untuk question_id {$answer['question_id']}.",
                            ];
                        }

                        $answer['answer_image'] = $selectedOption->option_image;
                        if ($selectedOption->is_correct) {
                            $score          = $question->score;
                            $question_score = $score;
                            $is_correct     = true;
                        }
                    }
                    break;

                case 'match_image':
                    // Validasi: match_image tidak boleh menerima answer_option_id
                    if (!empty($answer['answer_option_id'])) {
                        return [
                            'status'  => false,
                            'message' => "Soal match_image (question_id: {$answer['question_id']}) tidak boleh menerima answer_option_id. Gunakan answer_text berformat JSON pasangan.",
                        ];
                    }

                    if ($answer['answer_text'] ?? null) {
                        $userPairs = json_decode($answer['answer_text'], true);

                        // Validasi: answer_text harus berupa JSON array yang valid
                        if ($userPairs === null) {
                            return [
                                'status'  => false,
                                'message' => "answer_text untuk soal match_image (question_id: {$answer['question_id']}) harus berupa JSON array yang valid.",
                            ];
                        }

                        // Validasi: setiap pair harus memiliki key left_id dan right_id
                        foreach ($userPairs as $idx => $userPair) {
                            if (!isset($userPair['left_id']) || !isset($userPair['right_id'])) {
                                return [
                                    'status'  => false,
                                    'message' => "Pasangan ke-" . ($idx + 1) . " pada soal match_image (question_id: {$answer['question_id']}) harus memiliki 'left_id' dan 'right_id'.",
                                ];
                            }
                        }

                        // Validasi: left_id dan right_id harus sesuai dengan matchPairs milik question ini
                        $validPairIds = $question->matchPairs->pluck('id')->toArray();
                        foreach ($userPairs as $userPair) {
                            if (!in_array($userPair['left_id'], $validPairIds)) {
                                return [
                                    'status'  => false,
                                    'message' => "left_id {$userPair['left_id']} tidak valid untuk question_id {$answer['question_id']}.",
                                ];
                            }
                            if (!in_array($userPair['right_id'], $validPairIds)) {
                                return [
                                    'status'  => false,
                                    'message' => "right_id {$userPair['right_id']} tidak valid untuk question_id {$answer['question_id']}.",
                                ];
                            }
                        }

                        $correctPairs = $question->matchPairs->map(function ($pair) {
                            return [
                                'left_id'  => $pair->id,
                                'right_id' => $pair->id,
                            ];
                        })->toArray();

                        $correctCount = 0;
                        foreach ($userPairs as $userPair) {
                            foreach ($correctPairs as $correctPair) {
                                if (
                                    $userPair['left_id'] == $correctPair['left_id'] &&
                                    $userPair['right_id'] == $correctPair['right_id']
                                ) {
                                    $correctCount++;
                                    break;
                                }
                            }
                        }

                        if ($correctCount > 0) {
                            $totalPairs     = count($correctPairs);
                            $score          = ($correctCount / $totalPairs) * $question->score;
                            $question_score = $score;
                            $is_correct     = ($correctCount == $totalPairs);
                        }
                    }
                    break;
            }

            // Update existing answer jika ada id
            if (isset($answer['id'])) {
                $studentAnswer = $this->studentAnswer->findOrFail($answer['id']);
                $studentAnswer->update([
                    'student_id'       => $answer['student_id'],
                    'question_id'      => $answer['question_id'],
                    'answer_text'      => $answer['answer_text'] ?? null,
                    'answer_option_id' => $answer['answer_option_id'] ?? null,
                    'answer_image'     => $answer['answer_image'] ?? null,
                    'submitted_at'     => $answer['submitted_at'] ?? now(),
                    'is_correct'       => $is_correct,
                    'score'            => $score,
                    'feedback'         => $answer['feedback'] ?? null,
                ]);
            }

            $total_quiz_score += $question_score;
            $results[]         = $studentAnswer;
        }

        return [
            'answers'          => $results,
            'total_quiz_score' => $total_quiz_score,
        ];
    }

    public function delete(Request $request)
    {
        try {
            $ids = $request->input('ids'); // now expecting array

            if (!is_array($ids) || empty($ids)) {
                return [
                    'status' => false,
                    'message' => 'Tidak ada ID yang diberikan untuk penghapusan',
                ];
            }

            $deletedCount = 0;

            foreach ($ids as $id) {
                $studentAnswer = $this->studentAnswer->find($id);
                if ($studentAnswer) {
                    $studentAnswer->delete();
                    $deletedCount++;
                }
            }

            return [
                'status' => true,
                'message' => "{$deletedCount} jawaban siswa berhasil dihapus",
                'deleted_count' => $deletedCount
            ];
        } catch (\Exception $e) {
            Log::error('Error menghapus jawaban: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Error menghapus jawaban: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Normalize answer text for comparison:
     * - Trim whitespace
     * - Convert to lowercase
     * - Remove all punctuation (keep letters, numbers, and whitespace)
     * - Collapse multiple spaces into one
     */
    private function normalizeAnswerText(string $text): string
    {
        $text = trim($text);
        $text = strtolower($text);
        // Remove all punctuation/special chars except letters, numbers, and whitespace
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);
        // Collapse multiple spaces into one
        $text = preg_replace('/\s+/', ' ', $text);
        return $text;
    }
}
