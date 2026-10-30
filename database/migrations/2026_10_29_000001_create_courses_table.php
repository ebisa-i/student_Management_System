<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('course_name');
            $table->string('course_code')->unique();
            $table->unsignedSmallInteger('credit_hours')->default(0);
            $table->text('description')->nullable();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['student_id', 'course_id']);
        });

        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('ass_10')->default(0);
            $table->unsignedTinyInteger('test_10')->default(0);
            $table->unsignedTinyInteger('proj_20')->default(0);
            $table->unsignedTinyInteger('mid_20')->default(0);
            $table->unsignedTinyInteger('final_40')->default(0);
            $table->unsignedTinyInteger('total')->default(0);
            $table->string('grade', 2)->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('courses');
    }
};
