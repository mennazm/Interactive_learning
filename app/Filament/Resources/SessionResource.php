<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SessionResource\Pages;
use App\Models\Session as LearningSession;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SessionResource extends Resource
{
    protected static ?string $model = LearningSession::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
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
                Tables\Columns\TextColumn::make('started_at')
                    ->label('بدأت في')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSessions::route('/'),
        ];
    }
}
