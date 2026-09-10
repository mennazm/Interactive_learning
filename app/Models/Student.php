<?php

namespace App\Models;

use App\Enums\StudentGroup;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class Student extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'code',
        'group',
        'school_name',
        'is_active',
    ];

    protected $casts = [
        'group' => StudentGroup::class,
        'is_active' => 'boolean',
    ];

    protected $hidden = []; // No password — code-based auth

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    public function learningSessions(): HasMany
    {
        return $this->hasMany(LearningSession::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
