<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

   class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'title',
        'description',
        'content',
        'thumbnail',
    ];

    /**
     * Relasi ke kelas
     */
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    /**
     * Relasi ke quiz pada pelajaran
     */
    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'lesson_id', 'id');
    }
}
