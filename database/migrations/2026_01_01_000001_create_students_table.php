<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_code')->unique(); // e.g. COD-001
            $table->string('name');
            $table->string('age_level')->nullable();
            $table->string('parent_email')->nullable();
            $table->string('programming_lang')->default('Python');
            $table->string('schedule_notes')->nullable()->default('Fleksibel');
            $table->enum('learning_system', ['Paket', 'Bulanan'])->default('Paket');
            $table->integer('package_quota')->default(8);
            $table->enum('status', ['Aktif', 'Cuti', 'Lulus'])->default('Aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
