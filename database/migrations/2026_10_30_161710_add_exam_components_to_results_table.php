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
        Schema::table('results', function (Blueprint $table) {
            $table->unsignedTinyInteger('quiz_1')->nullable();
            $table->unsignedTinyInteger('quiz_2')->nullable();
            $table->unsignedTinyInteger('mid_1')->nullable();
            $table->unsignedTinyInteger('ass_1')->nullable();
            $table->unsignedTinyInteger('pres_1')->nullable();
            $table->unsignedTinyInteger('proj_1')->nullable();
            $table->unsignedTinyInteger('attendance')->nullable();
            $table->unsignedTinyInteger('lab_1')->nullable();
            $table->unsignedTinyInteger('final_exam')->nullable();
            $table->unsignedTinyInteger('test_1')->nullable();
            $table->unsignedTinyInteger('test_2')->nullable();
            $table->decimal('total', 5, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('results', function (Blueprint $table) {
            $table->dropColumn([
                'quiz_1',
                'quiz_2',
                'mid_1',
                'ass_1',
                'pres_1',
                'proj_1',
                'attendance',
                'lab_1',
                'final_exam',
                'test_1',
                'test_2',
            ]);
            $table->unsignedTinyInteger('total')->change();
        });
    }
};
