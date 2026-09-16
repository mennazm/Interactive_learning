<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, $default = null): ?string
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value.
     */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Get the experiment start date as Carbon instance.
     */
    public static function getExperimentStartDate(): ?Carbon
    {
        $date = static::get('experiment_start_date');
        return $date ? Carbon::parse($date) : null;
    }

    /**
     * Calculate current week number (1-5) based on experiment start date.
     * Returns 0 if experiment hasn't started yet.
     * Returns 6+ if experiment is over.
     */
    public static function getCurrentWeek(): int
    {
        $startDate = static::getExperimentStartDate();
        if (!$startDate) {
            return 0;
        }

        $now = now();
        if ($now->lt($startDate)) {
            return 0; // لم تبدأ بعد
        }

        $daysSinceStart = $startDate->diffInDays($now);
        $week = (int) floor($daysSinceStart / 7) + 1;

        return $week;
    }

    /**
     * Get which session numbers (1-10) are available this week.
     * Week 1 → sessions 1,2 | Week 2 → 1-4 | Week 3 → 1-6 | etc.
     * Sessions accumulate — all previous weeks' sessions remain available.
     */
    public static function getAvailableSessionNumbers(): array
    {
        $week = static::getCurrentWeek();
        if ($week <= 0) {
            return [];
        }

        // كل أسبوع يفتح جلستين جديدتين، والسابقة تفضل متاحة
        $maxSession = min($week * 2, 10);
        return range(1, $maxSession);
    }
}
