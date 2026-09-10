<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scenario extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'title',
        'title_ar',
        'topic',
        'communicative_function',
        'b1_axes',
        'vocabulary',
        'system_prompt',
        'scenario_module',
        'completion_criteria',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'b1_axes' => 'array',
        'vocabulary' => 'array',
        'is_active' => 'boolean',
    ];

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
