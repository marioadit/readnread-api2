<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentScoreRecap extends Model
{
    use HasFactory;

    protected $table = 'student_score_recaps';

    protected $fillable = [
        'student_id',
        'class_level',
        'semester',
        'average_score',
        'total_quizzes',
    ];

    protected $casts = [
        'average_score' => 'float',
        'total_quizzes' => 'integer',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
