<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnswersHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'class_id',
        'quiz_id',
        'lesson_id',
        'quiz_title',
        'lesson_title',
        'student_name',
        'class_name',
        'class_level',
        'class_semester',
        'class_academic_year',
        'question_ids',
        'student_answers_ids',
        'total_questions',
        'answered_questions',
        'correct_answers',
        'total_score',
        'max_possible_score',
        'percentage_score',
        'completion_rate',
        'start_time',
        'end_time',
        'submitted_at',
    ];

    protected $casts = [
        'question_ids' => 'array',
        'student_answers_ids' => 'array',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class , 'student_id');
    }

    public function class()
    {
        return $this->belongsTo(Classes::class , 'class_id');
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class , 'quiz_id');
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class , 'lesson_id');
    }
}
