<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionMatchPair extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'left_text',
        'left_image',
        'right_text',
        'right_image',
        'pair_order',
    ];

    /**
     * Relasi ke pertanyaan
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
