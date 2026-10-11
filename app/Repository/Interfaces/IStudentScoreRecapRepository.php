<?php

namespace App\Repository\Interfaces;

use App\Models\StudentScoreRecap;

interface IStudentScoreRecapRepository
{
    /**
     * Hitung ulang dan update record rekap untuk satu siswa pada tingkatan kelas dan semester tertentu.
     */
    public function recalculateForStudent(int $studentId, string $classLevel, string $semester): ?StudentScoreRecap;

    /**
     * Ambil data matriks rekap untuk seluruh siswa (dengan filter opsional class_id, search, pagination).
     */
    public function getRecapMatrix(array $filters = []): array;

    /**
     * Ambil data matriks rekap untuk satu siswa spesifik.
     */
    public function getRecapByStudent(int $studentId): ?array;

    /**
     * Hitung ulang seluruh data dari answers_history untuk semua siswa.
     */
    public function recalculateAll(): int;
}
