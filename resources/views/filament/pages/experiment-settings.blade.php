<x-filament-panels::page>
    <form wire:submit="save">
        <x-filament::section>
            <x-slot name="heading">تاريخ بداية التجربة</x-slot>
            <x-slot name="description">حدد التاريخ الذي تبدأ فيه التجربة. سيتم حساب الجدول الأسبوعي تلقائياً.</x-slot>

            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">تاريخ البداية</label>
                    <input type="date" wire:model="experiment_start_date"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                </div>

                @if($this->experiment_start_date)
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                    <h3 class="font-bold text-blue-800 dark:text-blue-200 mb-2">📅 الجدول الأسبوعي المحسوب:</h3>
                    <p class="text-sm text-blue-700 dark:text-blue-300 mb-2">
                        الأسبوع الحالي: <strong>{{ $this->currentWeek > 5 ? 'انتهت التجربة' : ($this->currentWeek == 0 ? 'لم تبدأ بعد' : $this->currentWeek) }}</strong>
                    </p>
                    <p class="text-sm text-blue-700 dark:text-blue-300">
                        الجلسات المتاحة: <strong>{{ implode(', ', $this->availableSessions) ?: 'لم تبدأ بعد' }}</strong>
                    </p>

                    <div class="mt-3 grid grid-cols-5 gap-2">
                        @for($w = 1; $w <= 5; $w++)
                        @php
                            $startDate = \Carbon\Carbon::parse($this->experiment_start_date)->addWeeks($w - 1);
                            $sessions = [($w*2)-1, $w*2];
                            $isCurrent = $this->currentWeek === $w;
                        @endphp
                        <div class="rounded-lg p-2 text-center text-xs {{ $isCurrent ? 'bg-primary-100 dark:bg-primary-900 border-2 border-primary-500' : 'bg-gray-100 dark:bg-gray-800' }}">
                            <div class="font-bold">الأسبوع {{ $w }}</div>
                            <div>{{ $startDate->format('M d') }}</div>
                            <div class="mt-1">جلسة {{ implode(' + ', $sessions) }}</div>
                        </div>
                        @endfor
                    </div>
                </div>
                @endif
            </div>
        </x-filament::section>

        <div class="mt-4">
            <x-filament::button type="submit" color="primary">
                💾 حفظ الإعدادات
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
