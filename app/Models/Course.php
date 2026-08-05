<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Course extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'skills',
        'objectives',
        'category',
        'program',
        'term',
        'level',
        'duration',
        'instructor',
        'created_by',
        'badge_id',
        'lessons_count',
        'enrolled_count',
        'passing_score',
        'is_featured',
        'is_published',
        'thumbnail_url',
        'approval_status',
        'is_approved',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'skills'       => 'array',
            'objectives'   => 'array',
            'is_featured'  => 'boolean',
            'is_published' => 'boolean',
            'is_approved'  => 'boolean',
            'approved_at'  => 'datetime',
        ];
    }

    /**
     * Display status used by the Faculty / Admin course cards.
     */
    public function statusLabel(): string
    {
        if ($this->approval_status === 'approved' || $this->is_approved) {
            return 'Published';
        }

        return match ($this->approval_status) {
            'pending' => 'Pending',
            'denied'  => 'Denied',
            default   => 'Draft',
        };
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class)->orderBy('order');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(CourseLesson::class)->orderBy('order');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments')
            ->withPivot(['is_completed', 'progress_percent', 'enrolled_at'])
            ->withTimestamps();
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }
}
