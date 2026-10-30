<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('results', function (Blueprint $table) {
            $table->decimal('quiz_score', 5, 2)->nullable();
            $table->decimal('attendance_score', 5, 2)->nullable();
            $table->decimal('assignment_score', 5, 2)->nullable();
            $table->decimal('midterm_score', 5, 2)->nullable();
            $table->decimal('final_exam_score', 5, 2)->nullable();
        });

        DB::table('results')->orderBy('id')->chunkById(500, function ($results): void {
            foreach ($results as $result) {
                $quiz = ((float) $result->quiz_1 + (float) $result->quiz_2) / 2;
                $scores = [
                    'quiz_score' => $this->convertLegacyScore($quiz, 5),
                    'attendance_score' => $this->convertLegacyScore((float) $result->attendance, 5),
                    'assignment_score' => $this->convertLegacyScore((float) $result->ass_1, 10),
                    'midterm_score' => $this->convertLegacyScore((float) $result->mid_1, 30),
                    'final_exam_score' => $this->convertLegacyScore((float) $result->final_exam, 50),
                ];
                $total = round(array_sum($scores), 2);

                DB::table('results')->where('id', $result->id)->update([
                    ...$scores,
                    'total' => $total,
                    'grade' => $this->gradeFor($total),
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('results')->orderBy('id')->chunkById(500, function ($results): void {
            foreach ($results as $result) {
                $scores = array_map(
                    fn (string $field): float => (float) ($result->{$field} ?? 0),
                    ['quiz_1', 'quiz_2', 'mid_1', 'ass_1', 'pres_1', 'proj_1', 'attendance', 'lab_1', 'final_exam', 'test_1', 'test_2'],
                );
                $total = round(array_sum($scores) / count($scores), 2);

                DB::table('results')->where('id', $result->id)->update([
                    'total' => $total,
                    'grade' => $this->gradeFor($total),
                ]);
            }
        });

        Schema::table('results', function (Blueprint $table) {
            $table->dropColumn([
                'quiz_score',
                'attendance_score',
                'assignment_score',
                'midterm_score',
                'final_exam_score',
            ]);
        });
    }

    private function gradeFor(float $total): string
    {
        return match (true) {
            $total >= 90 => 'A',
            $total >= 80 => 'B',
            $total >= 70 => 'C',
            $total >= 60 => 'D',
            default => 'F',
        };
    }

    private function convertLegacyScore(float $score, int $maximum): float
    {
        return round($score <= $maximum ? $score : $score * $maximum / 100, 2);
    }
};
