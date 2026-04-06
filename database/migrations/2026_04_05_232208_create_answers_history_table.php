<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('answers_history', function (Blueprint $table) {
            $table->id();

            // --- Foreign Keys (referensi ke tabel master, bisa null jika data dihapus) ---
            $table->foreignId('student_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('quiz_id')->nullable()->constrained('quizzes')->nullOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained('lessons')->nullOnDelete();

            // --- Snapshot Quiz & Lesson (tidak berubah meski master diupdate) ---
            $table->string('quiz_title');
            $table->string('lesson_title');

            // --- Snapshot Student (hanya nama yang boleh berubah secara dinamis) ---
            $table->string('student_name');

            // --- Snapshot Class (hanya nama yang boleh berubah secara dinamis) ---
            $table->string('class_name');
            $table->string('class_level');
            $table->enum('class_semester', ['gasal', 'genap']);
            $table->string('class_academic_year');

            // --- Relasi ID dalam bentuk JSON array ---
            // Menyimpan array ID soal yang dikerjakan, misal: [1, 2, 3]
            $table->text('question_ids')->nullable();
            // Menyimpan array ID jawaban siswa dari tabel student_answers, misal: [10, 11, 12]
            $table->text('student_answers_ids')->nullable();

            // --- Statistik hasil kuis ---
            $table->unsignedInteger('total_questions')->default(0);
            $table->unsignedInteger('answered_questions')->default(0);
            $table->unsignedInteger('correct_answers')->default(0);
            $table->unsignedInteger('total_score')->default(0);
            $table->unsignedInteger('max_possible_score')->default(0);
            $table->decimal('percentage_score', 5, 2)->default(0.00);
            $table->decimal('completion_rate', 5, 2)->default(0.00);

            // --- Waktu kuis ---
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers_history');
    }
};
