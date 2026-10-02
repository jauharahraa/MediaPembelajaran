<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       Schema::create('submission_feedbacks', function (Blueprint $table) {
        $table->id();
        $table->foreignId('submission_id')->unique()->constrained('case_submissions')->cascadeOnDelete();
        $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
        $table->text('feedback_identifikasi')->nullable();
        $table->text('feedback_analisis')->nullable();
        $table->text('feedback_solusi')->nullable();
        $table->text('error_message')->nullable();
        $table->unsignedTinyInteger('attempts_count')->default(0);
        $table->timestamps();
    });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_feedbacks');
    }
};