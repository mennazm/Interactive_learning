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
                Tables\Actions\Action::make('clearSessions')
                    ->label('مسح السيشنز')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('مسح جميع سيشنز الطالب')
                    ->modalDescription(fn (Student $record) =>
                        "هل أنت متأكد من مسح جميع السيشنز للطالب {$record->code}؟ " .
                        "سيتم حذف {$record->sessions()->count()} جلسة وجميع المحادثات والبيانات المرتبطة بها. هذا الإجراء لا يمكن التراجع عنه."
                    )
                    ->modalSubmitActionLabel('نعم، امسح الكل')
                    ->visible(fn (Student $record) => $record->sessions()->count() > 0)
                    ->action(function (Student $record) {
                        $count = $record->sessions()->count();

                        // مسح كل البيانات المرتبطة بالسيشنز
                        $record->sessions()->each(function ($session) {
                            $session->conversationTurns()->delete();
                            $session->incidents()->delete();
                            $session->activityLogs()->delete();
                        });

                        // مسح السيشنز نفسها
                        $record->sessions()->delete();

                        // مسح activity logs المرتبطة بالطالب مباشرة
                        $record->activityLogs()->delete();

                        \Filament\Notifications\Notification::make()
                            ->title("تم مسح {$count} جلسة للطالب {$record->code}")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulkClearSessions')
                        ->label('مسح السيشنز')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('مسح سيشنز الطلاب المحددين')
                        ->modalDescription('سيتم مسح جميع السيشنز والبيانات المرتبطة بها للطلاب المحددين. هذا الإجراء لا يمكن التراجع عنه.')
                        ->modalSubmitActionLabel('نعم، امسح الكل')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $totalDeleted = 0;

                            foreach ($records as $student) {
                                $totalDeleted += $student->sessions()->count();

                                $student->sessions()->each(function ($session) {
                                    $session->conversationTurns()->delete();
                                    $session->incidents()->delete();
                                    $session->activityLogs()->delete();
                                });

                                $student->sessions()->delete();
                                $student->activityLogs()->delete();
                            }

                            \Filament\Notifications\Notification::make()
                                ->title("تم مسح {$totalDeleted} جلسة لـ {$records->count()} طالب")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
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
            Forms\Components\Hidden::make('group')
                ->default('experimental'),
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
