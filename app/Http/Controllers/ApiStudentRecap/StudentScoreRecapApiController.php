<?php

namespace App\Http\Controllers\ApiStudentRecap;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Repository\Interfaces\IStudentScoreRecapRepository;
use Illuminate\Http\Request;

class StudentScoreRecapApiController extends Controller
{
    protected IStudentScoreRecapRepository $recapRepository;

    public function __construct(IStudentScoreRecapRepository $recapRepository)
    {
        $this->recapRepository = $recapRepository;
    }

    /**
     * Ambil data matriks rekap nilai siswa untuk seluruh kelas dan semester.
     * Mendukung filter:
     * - class_id (int)
     * - search (string nama / email siswa)
     * - status (string, e.g. aktif, lulus)
     * - per_page (int)
     * - all (boolean true/false)
     */
    public function index(Request $request)
    {
        $filters = $request->only(['class_id', 'search', 'status', 'per_page', 'all']);
        $result = $this->recapRepository->getRecapMatrix($filters);

        return ApiResponse::success($result, 'Data rekap nilai siswa berhasil diambil');
    }

    /**
     * Ambil rekap nilai untuk satu siswa spesifik.
     */
    public function show($studentId)
    {
        $result = $this->recapRepository->getRecapByStudent((int) $studentId);

        if (!$result) {
            return ApiResponse::error('Siswa tidak ditemukan atau bukan siswa aktif', 404);
        }

        return ApiResponse::success($result, 'Data rekap nilai siswa berhasil diambil');
    }

    /**
     * Ambil rekap nilai untuk siswa yang sedang login.
     */
    public function myRecap(Request $request)
    {
        $studentId = $request->user()->id;
        $result = $this->recapRepository->getRecapByStudent((int) $studentId);

        if (!$result) {
            return ApiResponse::error('Data rekap siswa tidak ditemukan', 404);
        }

        return ApiResponse::success($result, 'Data rekap nilai siswa berhasil diambil');
    }

    /**
     * Trigger manual sinkronisasi / kalkulasi ulang seluruh data dari answers_history.
     */
    public function sync()
    {
        try {
            $processed = $this->recapRepository->recalculateAll();
            return ApiResponse::success([
                'processed_records' => $processed,
            ], 'Sinkronisasi rekap nilai berhasil');
        } catch (\Exception $e) {
            return ApiResponse::error('Gagal melakukan sinkronisasi rekap: ' . $e->getMessage(), 500);
        }
    }
}
