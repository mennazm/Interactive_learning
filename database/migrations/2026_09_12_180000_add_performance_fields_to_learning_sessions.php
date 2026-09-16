<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_sessions', function (Blueprint $table) {
            $table->string('performance_status')->nullable()->after('notes');   // achieved, partially_achieved, not_achieved, not_assessable
            $table->text('strength_note')->nullable()->after('performance_status');
            $table->text('improvement_note')->nullable()->after('strength_note');
            $table->string('current_phase')->nullable()->after('improvement_note');
            $table->boolean('is_compensation')->default(false)->after('current_phase');
            $table->json('resume_point')->nullable()->after('is_compensation');
        });
    }

    public function down(): void
    {
        Schema::table('learning_sessions', function (Blueprint $table) {
            $table->dropColumn(['performance_status', 'strength_note', 'improvement_note', 'current_phase', 'is_compensation', 'resume_point']);
        });
    }
};
