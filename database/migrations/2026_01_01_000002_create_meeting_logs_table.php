<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->dateTime('meeting_date');
            $table->integer('session_number')->default(1);
            $table->string('topic');
            $table->string('project_progress')->nullable();
            $table->integer('rating')->default(5); // 1-5
            $table->text('evaluation_notes')->nullable();
            $table->enum('attendance_status', ['Hadir', 'Izin', 'Alfa'])->default('Hadir');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_logs');
    }
};
