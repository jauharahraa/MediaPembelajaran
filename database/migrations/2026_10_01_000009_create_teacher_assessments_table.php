<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // siswa
            $table->foreignId('submission_id')->constrained('case_submissions')->cascadeOnDelete();
            $table->unsignedTinyInteger('nilai_identifikasi');
            $table->unsignedTinyInteger('nilai_analisis');
            $table->unsignedTinyInteger('nilai_solusi');
            $table->decimal('final_score', 5, 2);
            $table->text('comment')->nullable();
            $table->timestamp('graded_at');
            $table->timestamps();
            $table->unique(['case_id', 'user_id']); // satu nilai akhir per siswa per kasus
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_assessments');
    }
};