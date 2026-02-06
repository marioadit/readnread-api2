<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'level',
        'teacher_id',
    ];

    /**
     * Relasi ke guru yang mengajar kelas
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Relasi ke siswa yang terdaftar di kelas
     */
    public function students()
    {
        return $this->belongsToMany(User::class, 'class_students', 'class_id', 'student_id');
    }

    /**
     * Relasi ke pelajaran (lesson) dalam kelas
     */
    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'class_id');
    }
}
