<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Mendapatkan kelas yang diajar oleh guru
     */
    public function classesTeaching()
    {
        return $this->hasMany(Classes::class, 'teacher_id');
    }

    /**
     * Mendapatkan kelas yang diikuti oleh siswa
     */
    public function classesEnrolled()
    {
        return $this->belongsToMany(Classes::class, 'class_students', 'student_id', 'class_id');
    }

    public function classes()
    {
        return $this->belongsToMany(Classes::class, 'class_students', 'student_id', 'class_id');
    }

    public function studentAnswers()
    {
        return $this->hasMany(StudentAnswer::class, 'student_id');
    }

    public function scoreRecaps()
    {
        return $this->hasMany(StudentScoreRecap::class, 'student_id');
    }
}
