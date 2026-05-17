<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'type',
        'question_text',
        'question_image',
        'order',
        'score',
    ];

    /**
     * Relasi ke kuis
     */
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Relasi ke opsi/pilihan jawaban
     */
    public function options()
    {
        return $this->hasMany(Option::class);
    }

    /**
     * Relasi ke pasangan pertanyaan (khusus untuk tipe match_image)
     */
    public function matchPairs()
    {
        return $this->hasMany(QuestionMatchPair::class);
    }

    /**
     * Relasi ke essay answer (khusus untuk tipe essay dan guess image)
     */
    public function essayAnswers()
    {
        return $this->hasMany(EssayAnswer::class);
    }

    /**
     * Relasi ke jawaban siswa
     */
    public function studentAnswers()
    {
        return $this->hasMany(StudentAnswer::class, 'question_id');
    }

    /**
     * Relasi ke file
     */
    public function files()
    {
        return $this->morphMany(File::class, 'owner');
    }
}
