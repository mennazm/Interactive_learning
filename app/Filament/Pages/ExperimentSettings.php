<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ExperimentSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'إعدادات التجربة';
    protected static ?string $title = 'إعدادات التجربة';
    protected static ?int $navigationSort = 100;
    protected static string $view = 'filament.pages.experiment-settings';

    public ?string $experiment_start_date = null;

    public function mount(): void
    {
        $this->experiment_start_date = Setting::get('experiment_start_date');
    }

    public function save(): void
    {
        Setting::set('experiment_start_date', $this->experiment_start_date);

        Notification::make()
            ->title('تم حفظ الإعدادات')
            ->success()
            ->send();
    }

    public function getCurrentWeekProperty(): int
    {
        return Setting::getCurrentWeek();
    }

    public function getAvailableSessionsProperty(): array
    {
        return Setting::getAvailableSessionNumbers();
    }
}
