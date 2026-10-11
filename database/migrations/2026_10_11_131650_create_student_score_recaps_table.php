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
        Schema::create('student_score_recaps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->string('class_level');
            $table->enum('semester', ['gasal', 'genap']);
            $table->decimal('average_score', 5, 2)->default(0.00);
            $table->unsignedInteger('total_quizzes')->default(0);
            $table->timestamps();

            // Memastikan satu kombinasi siswa, jenjang kelas, dan semester bersifat unik
            $table->unique(['student_id', 'class_level', 'semester'], 'student_level_sem_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_score_recaps');
    }
};
