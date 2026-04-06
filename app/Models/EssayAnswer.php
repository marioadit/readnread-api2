<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EssayAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'option_text',
    ];

    /**
     * Relasi ke pertanyaan
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
