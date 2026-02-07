<?php

namespace App\Http\Controllers\ApiOption;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Repository\Interface\OptionRepositoryInterface;
use Illuminate\Http\Request;

class OptionApiController extends Controller
{
    protected $optionRepository;

    public function __construct(OptionRepositoryInterface $optionRepository)
    {
        $this->optionRepository = $optionRepository;
    }

    /**
     * Mendapatkan semua opsi
     */
    public function index()
    {
        $options = $this->optionRepository->getAll();
        return ApiResponse::success($options);
    }

    /**
     * Mendapatkan detail opsi berdasarkan ID
     */
    public function show($id)
    {
        $option = $this->optionRepository->getById($id);
        return ApiResponse::success($option);
    }

    /**
     * Membuat opsi baru
     */
    public function store(Request $request)
    {
        $result = $this->optionRepository->create($request->all());

        if (!$result['status']) {
            return ApiResponse::error($result['message'], 422, $result['errors'] ?? null);
        }

        return ApiResponse::success($result['data'], $result['message'], 201);
    }

    /**
     * Mengupdate opsi
     */
    public function update(Request $request, $id)
    {
        $result = $this->optionRepository->update($id, $request);

        if (!$result['status']) {
            return ApiResponse::error($result['message'], 422, $result['errors'] ?? null);
        }

        return ApiResponse::success($result['data'], $result['message']);
    }

    /**
     * Menghapus opsi
     */
    public function destroy($id)
    {
        $result = $this->optionRepository->delete($id);

        if (!$result['status']) {
            return ApiResponse::error($result['message'], 404, $result['errors'] ?? null);
        }

        return ApiResponse::success(null, 'Opsi berhasil dihapus');
    }
}
