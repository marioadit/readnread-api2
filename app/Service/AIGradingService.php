<?php

namespace App\Service;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIGradingService
{
    protected string $apiKey;
    // gemini-3.1-flash-lite: low-latency, cost-effective, free-tier supported
    protected string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', '');
    }

    /**
     * Check if the student's answer is semantically correct
     * compared to the list of correct answers.
     *
     * Returns true if the answer is considered correct, false otherwise.
     * Falls back to false (not graded as correct) on API failure.
     *
     * @param string $studentAnswer  The raw answer submitted by the student
     * @param array  $correctAnswers Array of correct answer strings from essay_answers table
     * @param string $questionText   The question text for context
     * @param string $questionType   'essay' or 'guess_image'
     */
    public function gradeAnswer(
        string $studentAnswer,
        array $correctAnswers,
        string $questionText = '',
        string $questionType = 'essay'
    ): bool {
        if (empty($this->apiKey) || $this->apiKey === 'your-gemini-api-key-here') {
            Log::warning('AIGradingService: GEMINI_API_KEY belum dikonfigurasi, AI grading dilewati.', [
                'student_answer'  => $studentAnswer,
                'correct_answers' => $correctAnswers,
            ]);
            return false;
        }

        Log::info('AIGradingService: memulai penilaian AI', [
            'question_type'   => $questionType,
            'question_text'   => $questionText,
            'student_answer'  => $studentAnswer,
            'correct_answers' => $correctAnswers,
        ]);

        $correctList = implode(', ', array_map(fn($a) => '"' . $a . '"', $correctAnswers));

        $shared = <<<SHARED
Pertimbangkan hal berikut:
- Toleransi ejaan mirip / salah satu huruf (contoh: "tuju" = "tujuh", "kuceng" = "kucing")
- Angka digit dan tulisannya dianggap sama (contoh: "67" = "enam puluh tujuh" = "enam puluh tuju")
- Huruf kapital tidak berpengaruh
- Spasi berlebih tidak berpengaruh
- Jawaban tidak harus kata per kata sama, asalkan maknanya benar

Jawab HANYA dengan satu kata: "BENAR" atau "SALAH"
SHARED;

        // Build context-aware prompt
        if ($questionType === 'guess_image') {
            $prompt = <<<PROMPT
Kamu adalah sistem penilaian otomatis untuk soal tebak gambar dalam aplikasi belajar membaca anak SD.

Soal: "{$questionText}"
Jawaban yang benar: [{$correctList}]
Jawaban siswa: "{$studentAnswer}"

{$shared}
PROMPT;
        } else {
            $prompt = <<<PROMPT
Kamu adalah sistem penilaian otomatis untuk soal essay dalam aplikasi belajar membaca anak SD.

Soal: "{$questionText}"
Kunci jawaban yang diterima: [{$correctList}]
Jawaban siswa: "{$studentAnswer}"

{$shared}
PROMPT;
        }

        try {
            $response = Http::timeout(10)->post(
                $this->apiUrl . '?key=' . $this->apiKey,
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.1,
                        'maxOutputTokens' => 10,
                    ],
                ]
            );

            if ($response->failed()) {
                Log::error('AIGradingService: Gemini API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }

            $json = $response->json();
            $rawText = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $result = strtoupper(trim($rawText));

            Log::info('AIGradingService: hasil penilaian AI', [
                'student_answer'  => $studentAnswer,
                'correct_answers' => $correctAnswers,
                'ai_raw_result'   => $result,
                'is_correct'      => str_contains($result, 'BENAR'),
            ]);

            return str_contains($result, 'BENAR');

        } catch (\Exception $e) {
            Log::error('AIGradingService: Exception during API call', [
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
