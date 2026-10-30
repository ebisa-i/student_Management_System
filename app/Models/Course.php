<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = ['course_name', 'course_code', 'credit_hours', 'description', 'teacher_id', 'academic_year', 'semester'];

    protected function casts(): array
    {
        return [
            'semester' => 'integer',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments', 'course_id', 'student_id')->withTimestamps();
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }
}
