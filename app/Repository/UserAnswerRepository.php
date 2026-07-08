<?php

namespace App\Repository;

use App\Models\Option;
use App\Models\Question;
use App\Models\StudentAnswer;
use App\Repository\Interfaces\IUserAnswerRepository;
use App\Service\AIGradingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserAnswerRepository implements IUserAnswerRepository
{
    protected $studentAnswer;
    protected AIGradingService $aiGrading;

    public function __construct(StudentAnswer $studentAnswer, AIGradingService $aiGrading)
    {
        $this->studentAnswer = $studentAnswer;
        $this->aiGrading     = $aiGrading;
    }

    public function getAll()
    {
        return $this->studentAnswer->with(['student', 'question', 'selectedOption'])->get();
    }

    public function getById($id)
    {
        return $this->studentAnswer->with(['student', 'question', 'selectedOption'])->find($id);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->all();
            $quiz_id = $data['quiz_id'];
            $answers = $data['answers'];

            // Get all questions for this quiz
            $questionIds = array_map(function ($answer) {
                return $answer['question_id'];
            }, $answers);

            $questions = Question::with(['options', 'matchPairs', 'essayAnswers'])
                ->whereIn('id', $questionIds)
                ->get()
                ->keyBy('id');

            $results = [];
            $total_quiz_score = 0;

            foreach ($answers as $answer) {
                $question = $questions[$answer['question_id']];
                $score = 0;
                $is_correct = false;
                $question_score = 0;

                switch ($question->type) {
                    case 'essay':
                    case 'guess_image':
                        // Get all correct answers from essay_answers table (supports multiple answers separated by |)
                        $correctAnswers = $question->essayAnswers;
                        if ($correctAnswers->isNotEmpty() && $answer['answer_text']) {
                            $studentAnswerText  = $this->normalizeAnswerText($answer['answer_text']);
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
                                if ($aiRatio > 0) {
                                    // Partial credit: actual score = ratio * full question score
                                    $score          = round($aiRatio * $question->score, 2);
                                    $question_score = $score;
                                    $is_correct     = ($aiRatio >= 1.0);
                                }
                            }
                        }
                        break;

                    case 'choice_image':
                        if ($answer['answer_option_id'] ?? null) {
                            // Get the selected option
                            $selectedOption = $question->options->firstWhere('id', $answer['answer_option_id']);
                            if ($selectedOption) {
                                $answer['answer_image'] = $selectedOption->option_image;
                                if ($selectedOption->is_correct) {
                                    $score = $question->score;
                                    $question_score = $score;
                                    $is_correct = true;
                                }
                            }
                        }
                        break;

                    case 'match_image':
                        if ($answer['answer_text'] ?? null) {
                            // Parse the matching pairs from answer_text
                            $userPairs = json_decode($answer['answer_text'], true);
                            if ($userPairs) {
                                // Get correct pairs from question
                                $correctPairs = $question->matchPairs->map(function ($pair) {
                                    return [
                                    'left_id' => $pair->id,
                                    'right_id' => $pair->id
                                    ];
                                })->toArray();

                                // Compare user pairs with correct pairs
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

                                // Calculate partial score based on correct matches
                                if ($correctCount > 0) {
                                    $totalPairs = count($correctPairs);
                                    $score = ($correctCount / $totalPairs) * $question->score;
                                    $question_score = $score;
                                    $is_correct = ($correctCount == $totalPairs);
                                }
                            }
                        }
                        break;
                }

                $studentAnswer = $this->studentAnswer->create([
                    'student_id' => $answer['student_id'],
                    'question_id' => $answer['question_id'],
                    'answer_text' => $answer['answer_text'] ?? null,
                    'answer_option_id' => $answer['answer_option_id'] ?? null,
                    'answer_image' => $answer['answer_image'] ?? null,
                    'submitted_at' => $answer['submitted_at'] ?? now(),
                    'is_correct' => $is_correct,
                    'score' => $score,
                    'feedback' => null
                ]);

                $total_quiz_score += $question_score;

                $results[] = $studentAnswer;
            }

            DB::commit();

            return [
                'answers' => $results,
                'total_quiz_score' => $total_quiz_score
            ];
        }
        catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing answers: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Error storing answers: ' . $e->getMessage()
            ];
        }
    }

    public function update(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->all();

            $quiz_id = $data['quiz_id'];
            $answers = $data['answers'];
            $results = [];
            $total_quiz_score = 0;

            // Get all questions for this quiz
            $questionIds = array_map(function ($answer) {
                return $answer['question_id'];
            }, $answers);

            $questions = Question::with(['options', 'matchPairs', 'essayAnswers'])
                ->whereIn('id', $questionIds)
                ->get()
                ->keyBy('id');

            foreach ($answers as $answer) {
                $question = $questions[$answer['question_id']];
                $score = 0;
                $is_correct = false;
                $question_score = 0;

                switch ($question->type) {
                    case 'essay':
                    case 'guess_image':
                        // Get all correct answers from essay_answers table (supports multiple answers separated by |)
                        $correctAnswers = $question->essayAnswers;
                        if ($correctAnswers->isNotEmpty() && $answer['answer_text']) {
                            $studentAnswerText  = $this->normalizeAnswerText($answer['answer_text']);
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
                                if ($aiRatio > 0) {
                                    // Partial credit: actual score = ratio * full question score
                                    $score          = round($aiRatio * $question->score, 2);
                                    $question_score = $score;
                                    $is_correct     = ($aiRatio >= 1.0);
                                }
                            }
                        }
                        break;

                    case 'choice_image':
                        if ($answer['answer_option_id'] ?? null) {
                            // Get the selected option
                            $selectedOption = $question->options->firstWhere('id', $answer['answer_option_id']);
                            if ($selectedOption) {
                                $answer['answer_image'] = $selectedOption->option_image;
                                if ($selectedOption->is_correct) {
                                    $score = $question->score;
                                    $question_score = $score;
                                    $is_correct = true;
                                }
                            }
                        }
                        break;

                    case 'match_image':
                        if ($answer['answer_text'] ?? null) {
                            // Parse the matching pairs from answer_text
                            $userPairs = json_decode($answer['answer_text'], true);
                            if ($userPairs) {
                                // Get correct pairs from question
                                $correctPairs = $question->matchPairs->map(function ($pair) {
                                    return [
                                    'left_id' => $pair->id,
                                    'right_id' => $pair->id
                                    ];
                                })->toArray();

                                // Compare user pairs with correct pairs
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

                                // Calculate partial score based on correct matches
                                if ($correctCount > 0) {
                                    $totalPairs = count($correctPairs);
                                    $score = ($correctCount / $totalPairs) * $question->score;
                                    $question_score = $score;
                                    $is_correct = ($correctCount == $totalPairs);
                                }
                            }
                        }
                        break;
                }

                // Update or create student answer
                if (isset($answer['id'])) {
                    // Update existing answer
                    $studentAnswer = $this->studentAnswer->findOrFail($answer['id']);
                    $studentAnswer->update([
                        'student_id' => $answer['student_id'],
                        'question_id' => $answer['question_id'],
                        'answer_text' => $answer['answer_text'] ?? null,
                        'answer_option_id' => $answer['answer_option_id'] ?? null,
                        'answer_image' => $answer['answer_image'] ?? null,
                        'submitted_at' => $answer['submitted_at'] ?? now(),
                        'is_correct' => $is_correct,
                        'score' => $score,
                        'feedback' => $answer['feedback'] ?? null
                    ]);
                }

                $total_quiz_score += $question_score;
                $results[] = $studentAnswer;
            }

            DB::commit();
            return [
                'answers' => $results,
                'total_quiz_score' => $total_quiz_score
            ];
        }
        catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating student answer: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Error updating student answer: ' . $e->getMessage()
            ];
        }
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
        }
        catch (\Exception $e) {
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
