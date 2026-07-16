<?php

namespace App\Repository\Interfaces;

interface IAnswerHistoryRepository
{
    /**
     * Simpan satu record history dari hasil pengerjaan quiz.
     *
     * @param  array  $savedAnswers   Koleksi StudentAnswer yang sudah disimpan
     * @param  int    $quizId
     * @param  int    $studentId
     * @param  float  $totalScore     Total score yang didapat siswa
     * @param  string|null $startTime Waktu mulai mengerjakan (ISO string)
     * @return \App\Models\AnswersHistory
     */
    public function record(
        array $savedAnswers,
        int $quizId,
        int $studentId,
        float $totalScore,
        ?string $startTime = null
    );

    /**
     * Ambil semua history.
     */
    public function getAll();

    /**
     * Ambil history berdasarkan student_id.
     */
    public function getByStudent(int $studentId);

    /**
     * Ambil history berdasarkan quiz_id.
     */
    public function getByQuiz(int $quizId);
}
