<?php

namespace App\Filament\Resources\SessionResource\RelationManagers;

use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ConversationTurnsRelationManager extends RelationManager
{
    protected static string $relationship = 'conversationTurns';
    protected static ?string $title = 'سجل المحادثة';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Components\TextInput::make('turn_number')->label('رقم الدور')->disabled(),
            Components\TextInput::make('speaker')->label('المتحدث')->disabled(),
            Components\Textarea::make('text_content')->label('النص')->disabled()->columnSpanFull(),
            Components\TextInput::make('feedback_type')->label('نوع التغذية')->disabled(),
            Components\Textarea::make('feedback_text')->label('نص التغذية')->disabled()->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('turn_number')
            ->columns([
                Tables\Columns\TextColumn::make('turn_number')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('speaker')->label('المتحدث')
                    ->badge()
                    ->color(fn ($state) => $state === 'student' ? 'primary' : 'success')
                    ->formatStateUsing(fn ($state) => $state === 'student' ? '🗣️ طالب' : '🤖 أحمد'),
                Tables\Columns\TextColumn::make('text_content')->label('النص')->limit(80)->wrap(),
                Tables\Columns\TextColumn::make('feedback_type')->label('التغذية')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'none' => 'gray',
                        'recast' => 'info',
                        'clarification' => 'warning',
                        'model' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_correct')->label('صح؟')->boolean(),
                Tables\Columns\TextColumn::make('attempt_number')->label('المحاولة'),
            ])
            ->defaultSort('turn_number')
            ->actions([
                Actions\ViewAction::make(),
            ]);
    }
}
