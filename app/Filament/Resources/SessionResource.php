<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SessionResource\Pages;
use App\Models\Session as LearningSession;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SessionResource extends Resource
{
    protected static ?string $model = LearningSession::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'الجلسات';
    protected static ?string $modelLabel = 'جلسة';
    protected static ?string $pluralModelLabel = 'الجلسات';
    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.code')
                    ->label('الطالب')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('scenario.title')
                    ->label('السيناريو')
                    ->sortable()
                    ->limit(25),
                Tables\Columns\TextColumn::make('session_number')
                    ->label('رقم الجلسة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn ($state): string => match ($state?->value ?? $state) {
                        'completed' => 'success',
                        'in_progress' => 'info',
                        'not_started' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('duration_formatted')
                    ->label('المدة')
                    ->getStateUsing(function (LearningSession $record) {
                        if (!$record->duration_seconds) return '-';
                        $m = floor($record->duration_seconds / 60);
                        $s = $record->duration_seconds % 60;
                        return "{$m}:" . str_pad($s, 2, '0', STR_PAD_LEFT);
                    }),
                Tables\Columns\TextColumn::make('conversationTurns_count')
                    ->label('عدد الأدوار')
                    ->counts('conversationTurns'),
                Tables\Columns\TextColumn::make('correct_turns')
                    ->label('الإجابات الصحيحة')
                    ->getStateUsing(fn (LearningSession $record) => 
                        $record->conversationTurns()->where('is_correct', true)->count()
                    ),
                Tables\Columns\TextColumn::make('started_at')
                    ->label('بدأت في')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'completed' => 'مكتملة',
                        'in_progress' => 'جارية',
                        'not_started' => 'لم تبدأ',
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSessions::route('/'),
        ];
    }
}
