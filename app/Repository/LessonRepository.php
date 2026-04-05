<?php

namespace App\Repository;

use App\Models\Lesson;
use App\Repository\Interfaces\LessonRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class LessonRepository implements LessonRepositoryInterface
{
    protected $model;

    public function __construct(Lesson $lesson)
    {
        $this->model = $lesson;
    }

    public function getAll()
    {
        return $this->model->with('class')->get();
    }

    public function getById($id)
    {
        return $this->model->with(['class', 'quizzes'])->findOrFail($id);
    }

    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'class_id' => 'required|exists:classes,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);


        Log::info("Creating lesson with data:", $data);
        Log::info('Request has file thumbnail:', ['validator' => $validator->errors()->toArray()]);

        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ];
        }

        // Handle file upload if thumbnail is in the request
        if (isset($data['thumbnail']) && $data['thumbnail'] instanceof UploadedFile) {
            $file = $data['thumbnail'];
            $filename = 'thumbnail_' . time() . '.' . $file->getClientOriginalExtension();

            $path = $file->storeAs('thumbnails', $filename, 'public');

            $data['thumbnail'] = 'storage/' . $path;
        }

        $lesson = $this->model->create($data);

        return [
            'status' => true,
            'message' => 'Pelajaran berhasil dibuat',
            'data' => $lesson
        ];
    }

    public function update($id, Request $request)
    {

        $data = $request->all();

        $validator = Validator::make($data, [
            'class_id' => 'required|exists:classes,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Match create validation
        ]);

        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ];
        }

        $lesson = $this->model->findOrFail($id);

        $oldThumbnailPath = $lesson->thumbnail;

        $lesson->class_id = $data['class_id'];
        $lesson->title = $data['title'];

        $lesson->description = $data['description'];
        $lesson->content = $data['content'];

        if ($request->hasFile('thumbnail') && $request->file('thumbnail')->isValid()) {
            if ($oldThumbnailPath) {
                $oldFilePath = str_replace('storage/', '', $oldThumbnailPath);

                if (Storage::disk('public')->exists($oldFilePath)) {
                    Storage::disk('public')->delete($oldFilePath);
                // Log::info("Old thumbnail deleted: " . $oldFilePath);
                }
            }

            $file = $request->file('thumbnail');
            $filename = 'thumbnail_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('thumbnails', $filename, 'public');

            $lesson->thumbnail = 'storage/' . $path;

        // Log::info("New thumbnail uploaded: " . $lesson->thumbnail);
        }


        $lesson->save();

        return [
            'status' => true,
            'message' => 'Pelajaran berhasil diperbarui',
            'data' => $lesson
        ];
    }

    public function delete($id)
    {
        $lesson = $this->model->findOrFail($id);
        $lesson->delete();
        if (!$lesson) {
            return [
                'status' => false,
                'message' => 'Pelajaran tidak ditemukan',
            ];
        }
        // Delete the thumbnail if it exists
        if ($lesson->thumbnail) {
            $oldThumbnailPath = str_replace('storage/', '', $lesson->thumbnail);
            if (Storage::disk('public')->exists($oldThumbnailPath)) {
                Storage::disk('public')->delete($oldThumbnailPath);
            // Log::info("Thumbnail deleted: " . $oldThumbnailPath);
            }
        }

        return [
            'status' => true,
            'message' => 'Pelajaran berhasil dihapus'
        ];
    }

    public function getLessonsByClass($classId)
    {
        // Cek keberadaan kelas dengan exists()
        $classExists = \App\Models\Classes::where('id', $classId)->exists();
        // $classExists = $this->model->where('id', $classId)->exists();

        if (!$classExists) {
            return [
                'status' => false,
                'message' => 'Kelas tidak ditemukan',
                'data' => null
            ];
        }

        $lessons = $this->model->where('class_id', $classId)->get();

        return [
            'status' => true,
            'message' => $lessons->isEmpty() ? 'Tidak ada pelajaran untuk kelas ini' : 'Pelajaran ditemukan',
            'data' => $lessons
        ];
    }

    public function getLessonsByQuiz($lessonId)
    {
        return $this->model->with('quizzes')->findOrFail($lessonId);
    }
}
