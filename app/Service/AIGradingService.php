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
     * Grade the student's answer semantically and return a score ratio (0.0 – 1.0).
     *
     * - 1.0  → jawaban sepenuhnya benar
     * - 0.5  → jawaban relevan / mendekati benar (partial credit)
     * - 0.0  → jawaban salah / tidak relevan
     *
     * Falls back to 0.0 (no credit) on API failure.
     *
     * @param string $studentAnswer  The raw answer submitted by the student
     * @param array  $correctAnswers Array of correct answer strings from essay_answers table
     * @param string $questionText   The question text for context
     * @param string $questionType   'essay' or 'guess_image'
     * @return float Score ratio between 0.0 and 1.0
     */
    public function gradeAnswer(
        string $studentAnswer,
        array $correctAnswers,
        string $questionText = '',
        string $questionType = 'essay'
    ): float {
        if (empty($this->apiKey) || $this->apiKey === 'your-gemini-api-key-here') {
            Log::warning('AIGradingService: GEMINI_API_KEY belum dikonfigurasi, AI grading dilewati.', [
                'student_answer'  => $studentAnswer,
                'correct_answers' => $correctAnswers,
            ]);
            return 0.0;
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
- Huruf kapital dan spasi berlebih tidak berpengaruh
- Jawaban tidak harus kata per kata sama, yang dinilai adalah INTI / MAKNA dari jawaban

Berikan skor dalam skala 0 sampai 100:
- 100  = Inti / makna jawaban SAMA dengan kunci jawaban, meskipun menggunakan kata berbeda, sinonim, atau ejaan sedikit berbeda
         (contoh: kunci="kucing", jawaban="kuceng" → 100 | kunci="berlari", jawaban="lari" → 100)
- 50   = Jawaban ada hubungannya / mendekati kunci tapi inti makna tidak sepenuhnya tepat
         (contoh: kunci="kucing", jawaban="hewan berbulu" → 50)
- 0    = Jawaban salah atau tidak ada hubungannya sama sekali dengan kunci jawaban

Boleh menggunakan nilai di antara (misal 70, 30) jika dirasa perlu.

Jawab HANYA dengan angka saja (contoh: 100, 50, 0). Jangan tambahkan kata atau karakter lain.
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
                        'temperature'     => 0.1,
                        'maxOutputTokens' => 10,
                    ],
                ]
            );

            if ($response->failed()) {
                Log::error('AIGradingService: Gemini API request failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return 0.0;
            }

            $json    = $response->json();
            $rawText = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $cleaned = trim(preg_replace('/[^0-9]/', '', $rawText)); // strip non-numeric chars
            $score   = is_numeric($cleaned) ? (int) $cleaned : 0;

            // Clamp to 0–100
            $score = max(0, min(100, $score));

            // Convert to ratio 0.0–1.0
            $ratio = $score / 100.0;

            Log::info('AIGradingService: hasil penilaian AI (partial scoring)', [
                'student_answer'  => $studentAnswer,
                'correct_answers' => $correctAnswers,
                'ai_raw_result'   => $rawText,
                'ai_score_0_100'  => $score,
                'score_ratio'     => $ratio,
            ]);

            return $ratio;

        } catch (\Exception $e) {
            Log::error('AIGradingService: Exception during API call', [
                'message' => $e->getMessage(),
            ]);
            return 0.0;
        }
    }
}
