<?php

namespace App\Service;

use App\Repository\Interfaces\IAnswerHistoryRepository;
use App\Repository\Interfaces\IUserAnswerRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentAnswerService
{
    protected IUserAnswerRepository $userAnswerRepository;
    protected IAnswerHistoryRepository $answerHistoryRepository;

    public function __construct(
        IUserAnswerRepository $userAnswerRepository,
        IAnswerHistoryRepository $answerHistoryRepository
    ) {
        $this->userAnswerRepository    = $userAnswerRepository;
        $this->answerHistoryRepository = $answerHistoryRepository;
    }

    /**
     * Simpan jawaban siswa dan rekam ke answers_history dalam SATU transaksi.
     * Jika salah satu gagal, keduanya di-rollback.
     */
    public function store(Request $request): array
    {
        try {
            DB::beginTransaction();

            // 1. Simpan semua jawaban mentah ke student_answers
            $result = $this->userAnswerRepository->store($request);

            // Jika repository mengembalikan error (misal validasi gagal)
            if (isset($result['status']) && $result['status'] === false) {
                DB::rollBack();
                return $result;
            }

            // 2. Rekam history agregat ke answers_history
            //    Data diambil dari hasil step 1 — masih dalam transaksi yang sama
            $data = $request->all();
            $this->answerHistoryRepository->record(
                savedAnswers : $result['answers'],
                quizId       : (int) $data['quiz_id'],
                studentId    : (int) $data['answers'][0]['student_id'],
                totalScore   : (float) $result['total_quiz_score'],
                startTime    : $data['start_time'] ?? null
            );

            DB::commit();

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('StudentAnswerService::store error: ' . $e->getMessage());
            return [
                'status'  => false,
                'message' => 'Gagal menyimpan jawaban: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Update jawaban siswa (tanpa update history — history bersifat immutable).
     */
    public function update(Request $request): array
    {
        try {
            DB::beginTransaction();

            $result = $this->userAnswerRepository->update($request);

            if (isset($result['status']) && $result['status'] === false) {
                DB::rollBack();
                return $result;
            }

            DB::commit();

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('StudentAnswerService::update error: ' . $e->getMessage());
            return [
                'status'  => false,
                'message' => 'Gagal mengupdate jawaban: ' . $e->getMessage(),
            ];
        }
    }

    // --- Delegation methods (thin passthrough) ---

    public function getAll()
    {
        return $this->userAnswerRepository->getAll();
    }

    public function getById($id)
    {
        return $this->userAnswerRepository->getById($id);
    }

    public function delete(Request $request)
    {
        return $this->userAnswerRepository->delete($request);
    }
}
