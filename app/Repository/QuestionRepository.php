<?php

namespace App\Repository;

use App\Repository\Interfaces\QuestionRepositoryInterface;
use App\Models\Question;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class QuestionRepository implements QuestionRepositoryInterface
{
    protected $model;

    public function __construct(Question $question)
    {
        $this->model = $question;
    }

    public function getAll()
    {
        return $this->model->with(['quiz', 'options', 'matchPairs'])->get();
    }

    public function getById($id)
    {
        return $this->model->with(['quiz', 'options', 'matchPairs'])->findOrFail($id);
    }

    public function getWithRelations($id)
    {
        return $this->model->with(['quiz', 'options', 'matchPairs'])->findOrFail($id);
    }

    public function getStudentAnswers($id)
    {
        return $this->model->with(['studentAnswers'])->findOrFail($id);
    }

    public function getAllStudentAnswers()
    {
        return $this->model->with(['studentAnswers'])->get();
    }

    public function createWithRelations(array $data)
    {
        // Check if it's a multiple questions request
        if (isset($data[0]) && is_array($data[0])) {
            return $this->createMultipleWithRelations($data);
        }

        $validator = Validator::make($data, [
            'quiz_id' => 'required|exists:quizzes,id',
            'type' => 'required|string|in:essay,choice_image,guess_image,match_image',
            'question_text' => 'nullable|string',
            'question_image' => 'required_if:type,guess_image|nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'order' => 'nullable|integer|min:0',
            'score' => 'nullable|integer|min:0',
            'options' => 'required_if:type,choice_image|array',
            'option_text' => 'required_if:type,essay|required_if:type,guess_image|string',
            'options.*.option_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'options.*.is_correct' => 'required_if:type,choice_image|boolean',
            'options.*.order' => 'nullable|integer|min:0',
            'match_pairs' => 'required_if:type,match_image|array',
            'match_pairs.*.left_text' => 'nullable|string',
            'match_pairs.*.left_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'match_pairs.*.right_text' => 'nullable|string',
            'match_pairs.*.right_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'match_pairs.*.pair_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ];
        }

        try {
            DB::beginTransaction();

            // Handle question image upload
            if (isset($data['question_image']) && $data['question_image'] instanceof UploadedFile) {
                $file = $data['question_image'];
                $filename = 'question_image_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('questionsImage', $filename, 'public');
                $data['question_image'] = 'storage/' . $path;
            }

            // Create question
            $question = $this->model->create([
                'quiz_id' => $data['quiz_id'],
                'type' => $data['type'],
                'question_text' => $data['question_text'] ?? null,
                'question_image' => $data['question_image'] ?? null,
                'order' => $data['order'] ?? 0,
                'score' => $data['score'] ?? 0,
            ]);

            // Handle options if present
            if (isset($data['options']) && is_array($data['options'])) {
                foreach ($data['options'] as $optionData) {
                    if (isset($optionData['option_image']) && $optionData['option_image'] instanceof UploadedFile) {
                        $file = $optionData['option_image'];
                        $filename = 'option_image_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                        $path = $file->storeAs('optionsImage', $filename, 'public');
                        $optionData['option_image'] = 'storage/' . $path;
                    }

                    $question->options()->create([
                        'option_text' => $optionData['option_text'] ?? null,
                        'option_image' => $optionData['option_image'] ?? null,
                        'is_correct' => $optionData['is_correct'] ?? false,
                        'order' => $optionData['order'] ?? 0,
                    ]);
                }
            }

            // Handle match pairs if present
            if (isset($data['match_pairs']) && is_array($data['match_pairs'])) {
                foreach ($data['match_pairs'] as $pairData) {
                    // Handle left image
                    if (isset($pairData['left_image']) && $pairData['left_image'] instanceof UploadedFile) {
                        $file = $pairData['left_image'];
                        $filename = 'left_image_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                        $path = $file->storeAs('matchPairsImage', $filename, 'public');
                        $pairData['left_image'] = 'storage/' . $path;
                    }

                    // Handle right image
                    if (isset($pairData['right_image']) && $pairData['right_image'] instanceof UploadedFile) {
                        $file = $pairData['right_image'];
                        $filename = 'right_image_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                        $path = $file->storeAs('matchPairsImage', $filename, 'public');
                        $pairData['right_image'] = 'storage/' . $path;
                    }

                    $question->matchPairs()->create([
                        'left_text' => $pairData['left_text'] ?? null,
                        'left_image' => $pairData['left_image'] ?? null,
                        'right_text' => $pairData['right_text'] ?? null,
                        'right_image' => $pairData['right_image'] ?? null,
                        'pair_order' => $pairData['pair_order'] ?? 0,
                    ]);
                }
            }

            // Handle Essay and guess Image
            if (in_array($data['type'], ['essay', 'guess_image']) && isset($data['option_text'])) {
                $question->essayAnswers()->create([
                    'option_text' => $data['option_text'],
                ]);
            }

            DB::commit();

            return [
                'status' => true,
                'message' => 'Question created successfully with all relations',
                'data' => $this->getWithRelations($question->id)
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'status' => false,
                'message' => 'Error creating question: ' . $e->getMessage()
            ];
        }
    }

    protected function createMultipleWithRelations(array $questions)
    {
        $validator = Validator::make(['questions' => $questions], [
            'questions' => 'required|array|min:1',
            'questions.*.quiz_id' => 'required|exists:quizzes,id',
            'questions.*.type' => 'required|string|in:essay,choice_image,guess_image,match_image',
            'questions.*.question_text' => 'nullable|string',
            'questions.*.question_image' => 'required_if:questions.*.type,guess_image|nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'questions.*.order' => 'nullable|integer|min:0',
            'questions.*.score' => 'nullable|integer|min:0',
            'questions.*.options' => 'required_if:questions.*.type,choice_image|array',
            'questions.*.option_text' => 'required_if:questions.*.type,essay|required_if:questions.*.type,choice_image|required_if:questions.*.type,match_image|required_if:questions.*.type,guess_image|required|string',
            'questions.*.options.*.option_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'questions.*.options.*.is_correct' => 'required_if:questions.*.type,choice_image|boolean',
            'questions.*.options.*.order' => 'nullable|integer|min:0',
            'questions.*.match_pairs' => 'required_if:questions.*.type,match_image|array',
            'questions.*.match_pairs.*.left_text' => 'nullable|string',
            'questions.*.match_pairs.*.left_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'questions.*.match_pairs.*.right_text' => 'nullable|string',
            'questions.*.match_pairs.*.right_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'questions.*.match_pairs.*.pair_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ];
        }

        try {
            DB::beginTransaction();

            $createdQuestions = [];

            foreach ($questions as $questionData) {
                // Handle question image upload
                if (isset($questionData['question_image']) && $questionData['question_image'] instanceof UploadedFile) {
                    $file = $questionData['question_image'];
                    $filename = 'question_image_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('questionsImage', $filename, 'public');
                    $questionData['question_image'] = 'storage/' . $path;
                }

                // Create question
                $question = $this->model->create([
                    'quiz_id' => $questionData['quiz_id'],
                    'type' => $questionData['type'],
                    'question_text' => $questionData['question_text'] ?? null,
                    'question_image' => $questionData['question_image'] ?? null,
                    'order' => $questionData['order'] ?? 0,
                    'score' => $questionData['score'] ?? 0,
                ]);

                // Handle options if present
                if (isset($questionData['options']) && is_array($questionData['options'])) {
                    foreach ($questionData['options'] as $optionData) {
                        if (isset($optionData['option_image']) && $optionData['option_image'] instanceof UploadedFile) {
                            $file = $optionData['option_image'];
                            $filename = 'option_image_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                            $path = $file->storeAs('optionsImage', $filename, 'public');
                            $optionData['option_image'] = 'storage/' . $path;
                        }

                        $question->options()->create([
                            'option_text' => $optionData['option_text'] ?? null,
                            'option_image' => $optionData['option_image'] ?? null,
                            'is_correct' => $optionData['is_correct'] ?? false,
                            'order' => $optionData['order'] ?? 0,
                        ]);
                    }
                }

                // Handle match pairs if present
                if (isset($questionData['match_pairs']) && is_array($questionData['match_pairs'])) {
                    foreach ($questionData['match_pairs'] as $pairData) {
                        // Handle left image
                        if (isset($pairData['left_image']) && $pairData['left_image'] instanceof UploadedFile) {
                            $file = $pairData['left_image'];
                            $filename = 'left_image_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                            $path = $file->storeAs('matchPairsImage', $filename, 'public');
                            $pairData['left_image'] = 'storage/' . $path;
                        }

                        // Handle right image
                        if (isset($pairData['right_image']) && $pairData['right_image'] instanceof UploadedFile) {
                            $file = $pairData['right_image'];
                            $filename = 'right_image_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                            $path = $file->storeAs('matchPairsImage', $filename, 'public');
                            $pairData['right_image'] = 'storage/' . $path;
                        }

                        $question->matchPairs()->create([
                            'left_text' => $pairData['left_text'] ?? null,
                            'left_image' => $pairData['left_image'] ?? null,
                            'right_text' => $pairData['right_text'] ?? null,
                            'right_image' => $pairData['right_image'] ?? null,
                            'pair_order' => $pairData['pair_order'] ?? 0,
                        ]);
                    }
                }

                // Handle Essay and guess Image
                if (in_array($questionData['type'], ['essay', 'guess_image']) && isset($questionData['option_text'])) {
                    $question->essayAnswers()->create([
                        'option_text' => $questionData['option_text'],
                    ]);
                }

                $createdQuestions[] = $this->getWithRelations($question->id);
            }


            DB::commit();

            return [
                'status' => true,
                'message' => count($createdQuestions) . ' questions created successfully with all relations',
                'data' => $createdQuestions
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'status' => false,
                'message' => 'Error creating questions: ' . $e->getMessage()
            ];
        }
    }

    public function updateWithRelations($id, array $data)
    {
        // Restructure the options data if it's in array-like format
        $restructuredData = $this->restructureData($data);

        // Log the restructured data
        Log::info('Restructured update data:', ['data' => $restructuredData]);

        $validator = Validator::make($restructuredData, [
            'quiz_id' => 'required|exists:quizzes,id',
            'type' => 'required|string|in:essay,choice_image,guess_image,match_image',
            'question_text' => 'nullable|string',
            'question_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'order' => 'nullable|integer|min:0',
            'score' => 'nullable|integer|min:0',
            'options' => 'required_if:type,choice_image|array',
            'options.*.option_text' => 'nullable|string',
            'options.*.option_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'options.*.is_correct' => 'required_if:type,choice_image|boolean',
            'options.*.order' => 'nullable|integer|min:0',
            'match_pairs' => 'required_if:type,match_image|array',
            'match_pairs.*.left_text' => 'nullable|string',
            'match_pairs.*.left_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'match_pairs.*.right_text' => 'nullable|string',
            'match_pairs.*.right_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'match_pairs.*.pair_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed:', [
                'errors' => $validator->errors()->toArray(),
                'data' => $restructuredData
            ]);
            return [
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ];
        }

        try {
            DB::beginTransaction();

            $question = $this->model->findOrFail($id);

            // Use restructured data for the rest of the method
            $data = $restructuredData;

            // Handle question image upload
            if (isset($data['question_image']) && $data['question_image'] instanceof UploadedFile) {
                // Delete old image if exists
                if ($question->question_image) {
                    $oldPath = str_replace('storage/', '', $question->question_image);
                    Storage::disk('public')->delete($oldPath);
                }

                $file = $data['question_image'];
                $filename = 'question_image_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('questionsImage', $filename, 'public');
                $data['question_image'] = 'storage/' . $path;
            }

            // Update question
            $question->update([
                'quiz_id' => $data['quiz_id'],
                'type' => $data['type'],
                'question_text' => $data['question_text'] ?? $question->question_text,
                'question_image' => $data['question_image'] ?? $question->question_image,
                'order' => $data['order'] ?? $question->order,
                'score' => $data['score'] ?? $question->score,
            ]);

            // Update options
            if (isset($data['options'])) {
                // Delete existing options not in the update data
                $existingOptionIds = collect($data['options'])->pluck('id')->filter()->toArray();
                $question->options()->whereNotIn('id', $existingOptionIds)->get()->each(function ($option) {
                    if ($option->option_image) {
                        $oldPath = str_replace('storage/', '', $option->option_image);
                        Storage::disk('public')->delete($oldPath);
                    }
                    $option->delete();
                });

                // Update or create options
                foreach ($data['options'] as $optionData) {
                    if (isset($optionData['option_image']) && $optionData['option_image'] instanceof UploadedFile) {
                        $file = $optionData['option_image'];
                        $filename = 'option_image_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                        $path = $file->storeAs('optionsImage', $filename, 'public');
                        $optionData['option_image'] = 'storage/' . $path;
                    }

                    if (isset($optionData['id'])) {
                        $option = $question->options()->find($optionData['id']);
                        if ($option) {
                            // Delete old image if new one is provided
                            if (isset($optionData['option_image']) && $option->option_image) {
                                $oldPath = str_replace('storage/', '', $option->option_image);
                                Storage::disk('public')->delete($oldPath);
                            }

                            $option->update([
                                'option_text' => $optionData['option_text'] ?? $option->option_text,
                                'option_image' => $optionData['option_image'] ?? $option->option_image,
                                'is_correct' => $optionData['is_correct'] ?? $option->is_correct,
                                'order' => $optionData['order'] ?? $option->order,
                            ]);
                        }
                    } else {
                        $question->options()->create([
                            'option_text' => $optionData['option_text'] ?? null,
                            'option_image' => $optionData['option_image'] ?? null,
                            'is_correct' => $optionData['is_correct'] ?? false,
                            'order' => $optionData['order'] ?? 0,
                        ]);
                    }
                }
            }

            // Update match pairs
            if (isset($data['match_pairs'])) {
                // Delete existing pairs not in the update data
                $existingPairIds = collect($data['match_pairs'])->pluck('id')->filter()->toArray();
                $question->matchPairs()->whereNotIn('id', $existingPairIds)->get()->each(function ($pair) {
                    if ($pair->left_image) {
                        $oldPath = str_replace('storage/', '', $pair->left_image);
                        Storage::disk('public')->delete($oldPath);
                    }
                    if ($pair->right_image) {
                        $oldPath = str_replace('storage/', '', $pair->right_image);
                        Storage::disk('public')->delete($oldPath);
                    }
                    $pair->delete();
                });

                // Update or create pairs
                foreach ($data['match_pairs'] as $pairData) {
                    // Handle left image
                    if (isset($pairData['left_image']) && $pairData['left_image'] instanceof UploadedFile) {
                        $file = $pairData['left_image'];
                        $filename = 'left_image_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                        $path = $file->storeAs('matchPairsImage', $filename, 'public');
                        $pairData['left_image'] = 'storage/' . $path;
                    }

                    // Handle right image
                    if (isset($pairData['right_image']) && $pairData['right_image'] instanceof UploadedFile) {
                        $file = $pairData['right_image'];
                        $filename = 'right_image_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                        $path = $file->storeAs('matchPairsImage', $filename, 'public');
                        $pairData['right_image'] = 'storage/' . $path;
                    }

                    if (isset($pairData['id'])) {
                        $pair = $question->matchPairs()->find($pairData['id']);
                        if ($pair) {
                            // Delete old images if new ones are provided
                            if (isset($pairData['left_image']) && $pair->left_image) {
                                $oldPath = str_replace('storage/', '', $pair->left_image);
                                Storage::disk('public')->delete($oldPath);
                            }
                            if (isset($pairData['right_image']) && $pair->right_image) {
                                $oldPath = str_replace('storage/', '', $pair->right_image);
                                Storage::disk('public')->delete($oldPath);
                            }

                            $pair->update([
                                'left_text' => $pairData['left_text'] ?? $pair->left_text,
                                'left_image' => $pairData['left_image'] ?? $pair->left_image,
                                'right_text' => $pairData['right_text'] ?? $pair->right_text,
                                'right_image' => $pairData['right_image'] ?? $pair->right_image,
                                'pair_order' => $pairData['pair_order'] ?? $pair->pair_order,
                            ]);
                        }
                    } else {
                        $question->matchPairs()->create([
                            'left_text' => $pairData['left_text'] ?? null,
                            'left_image' => $pairData['left_image'] ?? null,
                            'right_text' => $pairData['right_text'] ?? null,
                            'right_image' => $pairData['right_image'] ?? null,
                            'pair_order' => $pairData['pair_order'] ?? 0,
                        ]);
                    }
                }
            }

            // Handle Essay and guess Image
            if (in_array($data['type'], ['essay', 'guess_image']) && isset($data['option_text'])) {
                $question->options()->updateOrCreate(
                    ['question_id' => $question->id, 'is_correct' => true],
                    ['option_text' => $data['option_text']]
                );
            }

            DB::commit();

            return [
                'status' => true,
                'message' => 'Question updated successfully with all relations',
                'data' => $this->getWithRelations($question->id)
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'status' => false,
                'message' => 'Error updating question: ' . $e->getMessage()
            ];
        }
    }

    public function delete($id)
    {
        $question = $this->getById($id);

        if (!$question) {
            return [
                'status' => false,
                'message' => 'Pertanyaan tidak ditemukan'
            ];
        }
        // Delete the question image if it exists
        if ($question->question_image) {
            $oldImagePath = str_replace('storage/', '', $question->question_image);
            if (Storage::disk('public')->exists($oldImagePath)) {
                Storage::disk('public')->delete($oldImagePath);
                // Log::info('Question image deleted: ' . $oldImagePath);
            }
        }
        $question->delete();
        return [
            'status' => true,
            'message' => 'Pertanyaan berhasil dihapus'
        ];
    }

    public function create(array $data)
    {
        return $this->createWithRelations($data);
    }

    public function update($id, Request $request)
    {
        return $this->updateWithRelations($id, $request->all());
    }

    public function getDetailQuestion($id)
    {
        try {
            $question = $this->model
                ->with([
                    'quiz',
                    'options' => function ($query) {
                        $query->orderBy('order', 'asc');
                    },
                    'matchPairs' => function ($query) {
                        $query->orderBy('pair_order', 'asc');
                    },
                    'studentAnswers.student', // If you have student answers relation
                    'files' // If you have files relation
                ])
                ->findOrFail($id);

            // Transform the data to include additional information
            $transformedData = [
                'id' => $question->id,
                'quiz' => [
                    'id' => $question->quiz->id,
                    'title' => $question->quiz->title,
                    // Add other quiz fields you need
                ],
                'type' => $question->type,
                'question_text' => $question->question_text,
                'question_image' => $question->question_image,
                'order' => $question->order,
                'score' => $question->score,
                'created_at' => $question->created_at,
                'updated_at' => $question->updated_at
            ];

            // Add options if present
            if ($question->type === 'choice_image' && $question->options->isNotEmpty()) {
                $transformedData['options'] = $question->options->map(function ($option) {
                    return [
                        'id' => $option->id,
                        'option_text' => $option->option_text,
                        'option_image' => $option->option_image,
                        'is_correct' => $option->is_correct,
                        'order' => $option->order
                    ];
                });
            }

            // Add match pairs if present
            if ($question->type === 'match_image' && $question->matchPairs->isNotEmpty()) {
                $transformedData['match_pairs'] = $question->matchPairs->map(function ($pair) {
                    return [
                        'id' => $pair->id,
                        'left_text' => $pair->left_text,
                        'left_image' => $pair->left_image,
                        'right_text' => $pair->right_text,
                        'right_image' => $pair->right_image,
                        'pair_order' => $pair->pair_order
                    ];
                });
            }

            // Add student answers if present and user has permission
            if ($question->studentAnswers->isNotEmpty()) {
                $transformedData['student_answers'] = $question->studentAnswers->map(function ($answer) {
                    return [
                        'id' => $answer->id,
                        'student' => [
                            'id' => $answer->student->id,
                            'name' => $answer->student->name
                        ],
                        'answer_text' => $answer->answer_text,
                        'is_correct' => $answer->is_correct,
                        'score' => $answer->score,
                        'submitted_at' => $answer->created_at
                    ];
                });
            }

            return [
                'status' => true,
                'message' => 'Question details retrieved successfully',
                'data' => $transformedData
            ];

        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Error retrieving question details: ' . $e->getMessage()
            ];
        }
    }

    protected function restructureData(array $data)
    {
        $restructured = [];
        $options = [];
        $matchPairs = [];

        foreach ($data as $key => $value) {
            // Handle options array-like format
            if (preg_match('/^options\[(\d+)\]\[([^\]]+)\]$/', $key, $matches)) {
                $index = $matches[1];
                $field = $matches[2];
                $options[$index][$field] = $value;
                continue;
            }

            // Handle match_pairs array-like format
            if (preg_match('/^match_pairs\[(\d+)\]\[([^\]]+)\]$/', $key, $matches)) {
                $index = $matches[1];
                $field = $matches[2];
                $matchPairs[$index][$field] = $value;
                continue;
            }

            // Copy other fields as is
            $restructured[$key] = $value;
        }

        // Add restructured arrays if they exist
        if (!empty($options)) {
            $restructured['options'] = array_values($options);
        }
        if (!empty($matchPairs)) {
            $restructured['match_pairs'] = array_values($matchPairs);
        }

        return $restructured;
    }
}
