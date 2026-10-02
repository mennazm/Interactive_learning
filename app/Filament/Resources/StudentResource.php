<?php

namespace App\Filament\Resources;

use App\Enums\StudentGroup;
use App\Filament\Resources\StudentResource\Pages;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'الطلبة';
    protected static ?string $modelLabel = 'طالب';
    protected static ?string $pluralModelLabel = 'الطلبة';
    protected static ?int $navigationSort = 1;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('رمز الطالب')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('school_name')
                    ->label('المدرسة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('group')
                    ->label('المجموعة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof StudentGroup ? $state->label() : $state)
                    ->color(fn ($state): string => match ($state instanceof StudentGroup ? $state->value : $state) {
                        'experimental' => 'success',
                        'control' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sessions_count')
                    ->label('عدد الجلسات')
                    ->counts('sessions')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('code')
            ->filters([
                SelectFilter::make('group')
                    ->label('المجموعة')
                    ->options([
                        'experimental' => 'مجموعة تجريبية',
                        'control' => 'مجموعة ضابطة',
                    ]),
                SelectFilter::make('school_name')
                    ->label('المدرسة')
                    ->options(fn () => Student::distinct()->pluck('school_name', 'school_name')->toArray()),
                SelectFilter::make('is_active')
                    ->label('الحالة')
                    ->options([
                        '1' => 'نشط',
                        '0' => 'غير نشط',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')
                ->label('رمز الطالب')
                ->required()
                ->unique(ignoreRecord: true)
                ->placeholder('مثال: STU1-001'),
            Forms\Components\Select::make('group')
                ->label('المجموعة')
                ->options([
                    'experimental' => 'مجموعة تجريبية',
                    'control' => 'مجموعة ضابطة',
                ])
                ->required(),
            Forms\Components\TextInput::make('school_name')
                ->label('اسم المدرسة')
                ->required()
                ->placeholder('مثال: ثانوية الملك عبدالله'),
            Forms\Components\Toggle::make('is_active')
                ->label('نشط')
                ->default(true),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
