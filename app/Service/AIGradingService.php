<?php

namespace App\Service;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIGradingService
{
    protected string $apiKey;

    /**
     * Model priority list — falls back to next on 429 / failure.
     * gemini-1.5-flash has a much larger free-tier quota than gemini-2.0-flash.
     */
    protected array $models = [
        'gemini-1.5-flash',
        'gemini-1.5-flash-8b',
    ];

    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', '');
    }

    /**
     * Grade a student answer against the correct answers using AI.
     *
     * Returns a structured array:
     * [
     *   'is_correct'   => bool,
     *   'score_factor' => float,   // 0.0 - 1.0  (use for partial scoring if needed)
     *   'reasoning'    => string,  // short AI explanation (logged)
     *   'source'       => string,  // 'ai' | 'api_error' | 'no_api_key'
     * ]
     *
     * @param string $studentAnswer  Raw answer from student
     * @param array  $correctAnswers All accepted correct answer strings
     * @param string $questionText   Question text for context
     * @param string $questionType   'essay' | 'guess_image'
     */
    public function gradeAnswer(
        string $studentAnswer,
        array  $correctAnswers,
        string $questionText = '',
        string $questionType = 'essay'
    ): array {
        // --- Guard: API key not configured ---
        if (empty($this->apiKey) || $this->apiKey === 'your-gemini-api-key-here') {
            Log::warning('AIGradingService: GEMINI_API_KEY belum dikonfigurasi, AI grading dilewati.', [
                'student_answer'  => $studentAnswer,
                'correct_answers' => $correctAnswers,
            ]);
            return $this->result(false, 0.0, 'API key tidak dikonfigurasi', 'no_api_key');
        }

        Log::info('AIGradingService: memulai AI grading', [
            'question_type'   => $questionType,
            'question_text'   => $questionText,
            'student_answer'  => $studentAnswer,
            'correct_answers' => $correctAnswers,
        ]);

        $prompt = $this->buildPrompt($studentAnswer, $correctAnswers, $questionText, $questionType);

        // --- Try each model in order ---
        foreach ($this->models as $model) {
            $response = $this->callApi($model, $prompt);

            if ($response === null) {
                // Exception — already logged inside callApi
                continue;
            }

            if ($response['status'] === 429) {
                Log::warning("AIGradingService: model {$model} rate-limited (429), mencoba model berikutnya.", [
                    'retry_after' => $response['retry_after'] ?? null,
                ]);
                continue;
            }

            if (!$response['success']) {
                Log::error("AIGradingService: model {$model} gagal.", [
                    'http_status' => $response['status'],
                    'body'        => $response['body'],
                ]);
                continue;
            }

            // --- Parse response ---
            $rawText   = $response['text'];
            $upper     = strtoupper(trim($rawText));
            $isCorrect = str_contains($upper, 'BENAR');

            // Extract reasoning if present (format: "BENAR|alasan" or just "BENAR")
            $parts     = explode('|', $rawText, 2);
            $reasoning = isset($parts[1]) ? trim($parts[1]) : trim($rawText);

            Log::info('AIGradingService: hasil grading', [
                'model'           => $model,
                'student_answer'  => $studentAnswer,
                'correct_answers' => $correctAnswers,
                'ai_raw_response' => $rawText,
                'is_correct'      => $isCorrect,
                'reasoning'       => $reasoning,
            ]);

            return $this->result(
                $isCorrect,
                $isCorrect ? 1.0 : 0.0,
                $reasoning,
                'ai'
            );
        }

        // All models failed / rate-limited
        Log::error('AIGradingService: semua model gagal, jawaban dianggap SALAH.', [
            'student_answer'  => $studentAnswer,
            'correct_answers' => $correctAnswers,
        ]);

        return $this->result(false, 0.0, 'Semua model AI tidak tersedia', 'api_error');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function buildPrompt(
        string $studentAnswer,
        array  $correctAnswers,
        string $questionText,
        string $questionType
    ): string {
        $correctList = implode(', ', array_map(fn($a) => '"' . $a . '"', $correctAnswers));

        $shared = <<<SHARED
Pertimbangkan hal berikut:
- Toleransi ejaan mirip / salah satu huruf (contoh: "tuju" = "tujuh", "kuceng" = "kucing")
- Angka dan tulisannya dianggap sama (contoh: "67" = "enam puluh tujuh" = "enam puluh tuju")
- Huruf kapital tidak berpengaruh
- Spasi berlebih tidak berpengaruh
- Singkatan umum yang jelas diterima

Format jawaban HANYA: BENAR|alasan singkat  atau  SALAH|alasan singkat
SHARED;

        if ($questionType === 'guess_image') {
            return <<<PROMPT
Kamu adalah sistem penilaian soal tebak gambar untuk siswa SD.

Soal: "{$questionText}"
Kunci jawaban yang diterima: [{$correctList}]
Jawaban siswa: "{$studentAnswer}"

{$shared}
PROMPT;
        }

        return <<<PROMPT
Kamu adalah sistem penilaian soal essay untuk siswa SD.

Soal: "{$questionText}"
Kunci jawaban yang diterima: [{$correctList}]
Jawaban siswa: "{$studentAnswer}"

{$shared}
PROMPT;
    }

    /**
     * Call Gemini API for the given model.
     * Returns null on exception, or array with keys: success, status, text, body, retry_after.
     */
    private function callApi(string $model, string $prompt): ?array
    {
        $url = $this->baseUrl . $model . ':generateContent?key=' . $this->apiKey;

        try {
            $response = Http::timeout(15)->post($url, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'temperature'     => 0.1,
                    'maxOutputTokens' => 50,
                ],
            ]);

            $status = $response->status();

            if ($status === 429) {
                return [
                    'success'     => false,
                    'status'      => 429,
                    'body'        => $response->body(),
                    'retry_after' => $response->header('Retry-After'),
                    'text'        => '',
                ];
            }

            if ($response->failed()) {
                return [
                    'success' => false,
                    'status'  => $status,
                    'body'    => $response->body(),
                    'text'    => '',
                ];
            }

            $json = $response->json();
            $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';

            return [
                'success' => true,
                'status'  => $status,
                'body'    => null,
                'text'    => $text,
            ];

        } catch (\Exception $e) {
            Log::error("AIGradingService: Exception saat memanggil model {$model}", [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function result(bool $isCorrect, float $scoreFactor, string $reasoning, string $source): array
    {
        return [
            'is_correct'   => $isCorrect,
            'score_factor' => $scoreFactor,
            'reasoning'    => $reasoning,
            'source'       => $source,
        ];
    }
}
