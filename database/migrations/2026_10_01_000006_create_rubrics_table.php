<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       Schema::create('rubrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->unique()->constrained('cases')->cascadeOnDelete();
            $table->text('identifikasi_indikator');
            $table->text('identifikasi_kriteria');
            $table->text('analisis_indikator');
            $table->text('analisis_kriteria');
            $table->text('solusi_indikator');
            $table->text('solusi_kriteria');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rubrics');
    }
};