<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('attempt_number'); // 1 = pertama, 2 = revisi
            $table->text('jawaban_identifikasi');
            $table->text('jawaban_analisis');
            $table->text('jawaban_solusi');
            $table->timestamp('submitted_at');
            $table->timestamps();

            // Satu siswa hanya boleh punya satu baris per nomor percobaan di tiap kasus
            $table->unique(['case_id', 'user_id', 'attempt_number']);
        });

        // Pindahkan pengecekan DB ke dalam fungsi up()
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement('ALTER TABLE case_submissions ADD CONSTRAINT chk_attempt CHECK (attempt_number IN (1, 2))');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('case_submissions');
    }
};