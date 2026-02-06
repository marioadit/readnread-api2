<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'question_id',
        'answer_text',
        'answer_option_id',
        'answer_image',
        'submitted_at',
        'is_correct',
        'score',
        'feedback',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'is_correct' => 'boolean',
    ];

    /**
     * Relasi ke siswa
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Relasi ke pertanyaan
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Relasi ke opsi jawaban yang dipilih (jika ada)
     */
    public function selectedOption()
    {
        return $this->belongsTo(Option::class, 'answer_option_id');
    }
}
