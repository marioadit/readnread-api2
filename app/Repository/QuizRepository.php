<?php

namespace App\Repository;

use App\Repository\Interface\QuizRepositoryInterface;
use App\Models\Quiz;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class QuizRepository implements QuizRepositoryInterface
{
    protected $model;

    public function __construct(Quiz $quiz)
    {
        $this->model = $quiz;
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function getQuizByLessons($quizId)
    {
        return $this->model->with('lesson')->findOrFail($quizId);
    }

    public function getQuizByQuestions($quizId)
    {
        return $this->model->with('questions')->findOrFail($quizId);
    }

    public function getWithLessonAndQuestions($quizId)
    {
        return $this->model->with(['lesson', 'questions'])->findOrFail($quizId);
    }


    public function getById($id)
    {
        return $this->model->with(['lesson', 'questions'])->findOrFail($id);
    }

    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'lesson_id' => 'required|exists:lessons,id',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date',
            'is_active' => 'required'
        ]);


        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ];
        }

        $quiz = $this->model->create($data);

        return [
            'status' => true,
            'message' => 'Kelas berhasil dibuat',
            'data' => $quiz
        ];
    }
    public function update($id, array $data)
    {
        $validator = Validator::make($data, [
            'lesson_id' => 'required|exists:lessons,id',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date',
            'is_active' => 'required'
        ]);

        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ];
        }

        $quiz = $this->model->findOrFail($id);

        $quiz->update($data);

        return [
            'status' => true,
            'message' => 'Kelas berhasil diperbarui',
            'data' => $quiz
        ];
    }
    public function delete($id)
    {
        $quiz = $this->model->findOrFail($id);
        $quiz->delete();
        return [
            'status' => true,
            'message' => 'Kelas berhasil dihapus'
        ];
    }

    public function getQuizResults($quizId, $studentId)
    {
        try {
            $quiz = $this->model
                ->with(['questions.studentAnswers' => function ($query) use ($studentId) {
                    $query->where('student_id', $studentId)
                          ->orderBy('created_at', 'desc');
                }])
                ->with(['questions' => function ($query) {
                    $query->orderBy('order');
                }])
                ->with('questions.options')
                ->findOrFail($quizId);

            $totalScore = 0;
            $maxPossibleScore = 0;
            $answeredQuestions = 0;
            $correctAnswers = 0;

            foreach ($quiz->questions as $question) {
                $maxPossibleScore += $question->score;
                
                // Get the latest answer for this question
                $latestAnswer = $question->studentAnswers->first();
                
                if ($latestAnswer) {
                    $answeredQuestions++;
                    $totalScore += $latestAnswer->score;
                    
                    if ($latestAnswer->is_correct) {
                        $correctAnswers++;
                    }
                }
            }

            $percentageScore = $maxPossibleScore > 0 ? ($totalScore / $maxPossibleScore) * 100 : 0;
            $completionRate = count($quiz->questions) > 0 ? ($answeredQuestions / count($quiz->questions)) * 100 : 0;

            return [
                'status' => true,
                'message' => 'Quiz results retrieved successfully',
                'data' => [
                    'quiz_id' => $quiz->id,
                    'quiz_title' => $quiz->title,
                    'total_questions' => count($quiz->questions),
                    'answered_questions' => $answeredQuestions,
                    'correct_answers' => $correctAnswers,
                    'total_score' => $totalScore,
                    'max_possible_score' => $maxPossibleScore,
                    'percentage_score' => round($percentageScore, 2),
                    'completion_rate' => round($completionRate, 2),
                    'questions' => $quiz->questions->map(function ($question) {
                        $latestAnswer = $question->studentAnswers->first();
                        return [
                            'question_id' => $question->id,
                            'question_text' => $question->question_text,
                            'question_type' => $question->type,
                            'max_score' => $question->score,
                            'student_answer' => $latestAnswer ? [
                                'answer_text' => $latestAnswer->answer_text,
                                'answer_option_id' => $latestAnswer->answer_option_id,
                                'is_correct' => $latestAnswer->is_correct,
                                'score' => $latestAnswer->score,
                                'feedback' => $latestAnswer->feedback,
                                'submitted_at' => $latestAnswer->submitted_at
                            ] : null
                        ];
                    })->values()->all()
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Error retrieving quiz results: ' . $e->getMessage()
            ];
        }
    }

    public function getUserQuizResults($studentId)
    {
        try {
            // Get all quizzes that have answers from this student
            $quizzes = $this->model
                ->whereHas('questions.studentAnswers', function ($query) use ($studentId) {
                    $query->where('student_id', $studentId);
                })
                ->with(['questions.studentAnswers' => function ($query) use ($studentId) {
                    $query->where('student_id', $studentId);
                }, 'questions.options', 'lesson'])
                ->with([
                    'questions.studentAnswers.student' => function ($query) use ($studentId) {
                        $query->where('id', $studentId);
                    },
                    'questions.options',
                    'lesson.class.students' => function ($query) use ($studentId) {
                        $query->where('student_id', $studentId);
                    },
                    'lesson'
                ])
                ->get();

            $results = $quizzes->map(function ($quiz) {
                $totalScore = 0;
                $maxPossibleScore = 0;
                $answeredQuestions = 0;
                $correctAnswers = 0;

                foreach ($quiz->questions as $question) {
                    $maxPossibleScore += $question->score;

                    if ($question->studentAnswers->isNotEmpty()) {
                        $answeredQuestions++;
                        $studentAnswer = $question->studentAnswers->sortByDesc('created_at')->first();
                        $totalScore += $studentAnswer->score;

                        if ($studentAnswer->is_correct) {
                            $correctAnswers++;
                        }
                    }
                }

                $percentageScore = $maxPossibleScore > 0 ? ($totalScore / $maxPossibleScore) * 100 : 0;
                $completionRate = count($quiz->questions) > 0 ? ($answeredQuestions / count($quiz->questions)) * 100 : 0;

                $student = $quiz->questions->first()->studentAnswers->first()->student ?? null;

                return [
                    'quiz_id' => $quiz->id,
                    'quiz_title' => $quiz->title,
                    'lesson' => [
                        'id' => $quiz->lesson->id,
                        'title' => $quiz->lesson->title
                    ],
                    'class' => [
                        'id' => $quiz->lesson->class->id,
                        'name' => $quiz->lesson->class->name
                    ],
                    'student' => $student,
                    'total_questions' => count($quiz->questions),
                    'answered_questions' => $answeredQuestions,
                    'correct_answers' => $correctAnswers,
                    'total_score' => $totalScore,
                    'max_possible_score' => $maxPossibleScore,
                    'percentage_score' => round($percentageScore, 2),
                    'completion_rate' => round($completionRate, 2),
                    'start_time' => $quiz->start_time,
                    'end_time' => $quiz->end_time,
                    'submitted_at' => $quiz->questions->first()->studentAnswers->sortByDesc('created_at')->first()->submitted_at ?? null
                ];
            });

            return [
                'status' => true,
                'message' => 'User quiz results retrieved successfully',
                'data' => $results
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Error retrieving user quiz results: ' . $e->getMessage()
            ];
        }
    }
}
