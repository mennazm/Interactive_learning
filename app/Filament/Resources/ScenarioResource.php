<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScenarioResource\Pages;
use App\Models\Scenario;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ScenarioResource extends Resource
{
    protected static ?string $model = Scenario::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'السيناريوهات';
    protected static ?string $pluralModelLabel = 'السيناريوهات';
    protected static ?string $modelLabel = 'سيناريو';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Components\TextInput::make('number')->label('رقم السيناريو')->numeric()->required(),
            Components\TextInput::make('title')->label('العنوان (إنجليزي)')->required()->maxLength(255),
            Components\TextInput::make('title_ar')->label('العنوان (عربي)')->maxLength(255),
            Components\TextInput::make('topic')->label('الموضوع')->maxLength(255),
            Components\TextInput::make('communicative_function')->label('الوظيفة التواصلية')->maxLength(500),
            Components\Textarea::make('system_prompt')->label('System Prompt')->rows(6),
            Components\Textarea::make('scenario_module')->label('تفاصيل الموقف')->rows(4),
            Components\Textarea::make('completion_criteria')->label('معيار الإكمال')->rows(3),
            Components\TagsInput::make('b1_axes')->label('محاور التقييم B1'),
            Components\TagsInput::make('vocabulary')->label('المفردات المستهدفة'),
            Components\TextInput::make('sort_order')->label('ترتيب العرض')->numeric()->default(0),
            Components\Toggle::make('is_active')->label('مفعّل')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('number')->label('#')->sortable(),
            Tables\Columns\TextColumn::make('title')->label('العنوان')->searchable(),
            Tables\Columns\TextColumn::make('title_ar')->label('بالعربي')->searchable(),
            Tables\Columns\TextColumn::make('topic')->label('الموضوع')->searchable()->toggleable(),
            Tables\Columns\ToggleColumn::make('is_active')->label('مفعّل'),
            Tables\Columns\TextColumn::make('sort_order')->label('الترتيب')->sortable(),
        ])
        ->defaultSort('sort_order')
        ->reorderable('sort_order')
        ->filters([
            Tables\Filters\TernaryFilter::make('is_active')->label('مفعّل'),
        ])
        ->actions([
            Actions\EditAction::make(),
        ])
        ->bulkActions([
            Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListScenarios::route('/'),
            'create' => Pages\CreateScenario::route('/create'),
            'edit' => Pages\EditScenario::route('/{record}/edit'),
        ];
    }
}
