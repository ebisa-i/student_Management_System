<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PENDING_REVIEW = 'pending_review';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_RETURNED = 'returned';

    public const SCORE_FIELDS = [
        'quiz_score' => 'Quiz',
        'attendance_score' => 'Attendance',
        'assignment_score' => 'Assignment',
        'midterm_score' => 'Midterm',
        'final_exam_score' => 'Final',
    ];

    public const SCORE_MAXIMUMS = [
        'quiz_score' => 5,
        'attendance_score' => 5,
        'assignment_score' => 10,
        'midterm_score' => 30,
        'final_exam_score' => 50,
    ];

    protected $fillable = [
        'student_id',
        'course_id',
        'quiz_score',
        'attendance_score',
        'assignment_score',
        'midterm_score',
        'final_exam_score',
        'total',
        'grade',
    ];

    protected $attributes = [
        'status' => self::STATUS_DRAFT,
    ];

    public function canBeEditedByTeacher(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_RETURNED], true);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'quiz_score' => 'decimal:2',
            'attendance_score' => 'decimal:2',
            'assignment_score' => 'decimal:2',
            'midterm_score' => 'decimal:2',
            'final_exam_score' => 'decimal:2',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }
}
