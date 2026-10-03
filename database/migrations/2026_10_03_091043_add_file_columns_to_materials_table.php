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
        Schema::table('materials', function (Blueprint $table) {
            $table->longText('content')->nullable()->change(); // materi boleh berupa file saja
            $table->string('file_path')->nullable()->after('image_path');
            $table->string('file_name')->nullable()->after('file_path');
            $table->string('file_type', 10)->nullable()->after('file_name');
            $table->unsignedInteger('file_size')->nullable()->after('file_type');
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'file_name', 'file_type', 'file_size']);
            // kolom content sengaja dibiarkan nullable
        });
    }
};
