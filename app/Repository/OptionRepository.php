<?php

namespace App\Repository;

use App\Repository\Interfaces\OptionRepositoryInterface;
use App\Models\Option;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class OptionRepository implements OptionRepositoryInterface
{
    protected $model;

    public function __construct(Option $option)
    {
        $this->model = $option;
    }

    public function getAll()
    {
        return $this->model->with(['question'])->get();
    }

    public function getById($id)
    {
        return $this->model->with(['question'])->findOrFail($id);
    }
    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'question_id' => 'required|exists:questions,id',
            'option_text' => 'nullable|string|max:255',
            'option_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_correct' => 'required|in:0,1',
            'order' => 'nullable|integer|min:0|max:9999999999',
        ]);

        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ];
        }

        // Handle file upload if option_image is in the request
        if (isset($data['option_image']) && $data['option_image'] instanceof UploadedFile) {
            $file = $data['option_image'];
            $filename = 'option_image_' . time() . '.' . $file->getClientOriginalExtension();

            $path = $file->storeAs('optionsImage', $filename, 'public');

            $data['option_image'] = 'storage/' . $path;
        }

        $option = $this->model->create($data);

        return [
            'status' => true,
            'message' => 'Opsi berhasil dibuat',
            'data' => $option,
        ];
    }
    public function update($id, Request $request)
    {
        $data = $request->all();

        if (!$data) {
            return [
                'status' => false,
                'message' => 'Opsi tidak ditemukan',
            ];
        }

        $validator = Validator::make($data, [
            'question_id' => 'required|exists:questions,id',
            'option_text' => 'nullable|string|max:255',
            'option_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_correct' => 'required|in:0,1',
            'order' => 'nullable|integer|min:0|max:9999999999',
        ]);

        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ];
        }
        $option = $this->model->findOrFail($id);

        $oldImagePath = $option->option_image;

        $option->question_id = $data['question_id'];
        $option->option_text = $data['option_text'];
        $option->is_correct = $data['is_correct'];
        $option->order = $data['order'];

        if ($request->hasFile('option_image') && $request->file('option_image')->isValid()) {
            if ($oldImagePath) {
                $oldImagePath = str_replace('storage/', '', $oldImagePath);
                if (Storage::disk('public')->exists($oldImagePath)) {
                    Storage::disk('public')->delete($oldImagePath);
                // Log::info('Old option image deleted: ' . $oldImagePath);
                }
            }
            $file = $data['option_image'];
            $filename = 'option_image_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('optionsImage', $filename, 'public');
            $option->option_image = 'storage/' . $path;
        // Log::info('New option image stored: ' . $option->option_image);
        }

        $option->save();
        return [
            'status' => true,
            'message' => 'Opsi berhasil diperbarui',
            'data' => $option,
        ];
    }
    public function delete($id)
    {
        $option = $this->model->findOrFail($id);
        $option->delete();
        if (!$option) {
            return [
                'status' => false,
                'message' => 'Opsi tidak ditemukan'
            ];
        }
        // Delete the option image if it exists
        if ($option->option_image) {
            $oldImagePath = str_replace('storage/', '', $option->option_image);
            if (Storage::disk('public')->exists($oldImagePath)) {
                Storage::disk('public')->delete($oldImagePath);
            // Log::info('Option image deleted: ' . $oldImagePath);
            }
        }
        return [
            'status' => true,
            'message' => 'Opsi berhasil dihapus',
        ];
    }
    public function getWithStudentAnswerandQuestion($optionId)
    {
        return $this->model->with(['studentAnswers', 'question'])->findOrFail($optionId);
    }
}
