<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_sessions', function (Blueprint $table) {
            $table->id();                                                    // رقم الجلسة التلقائي
            $table->foreignId('student_id')->constrained()->cascadeOnDelete(); // 🔗 ربط بالطالب — كل جلسة لازم تكون لطالب معين. لو الطالب اتحذف، جلساته تتحذف معاه
            $table->foreignId('scenario_id')->nullable()->constrained()->nullOnDelete(); // 🔗 ربط بالسيناريو — أي سيناريو من الـ 8 الطالب بيتدرب عليه. nullable لجلسات المراجعة (9) والتأمل (10)
            $table->integer('session_number');                               // رقم الجلسة (1-10) — 8 سيناريوهات + جلسة 9 تكاملية + جلسة 10 تأملية. مهم لتتبع التقدم
            $table->string('status')->default('not_started');                // حالة الجلسة (State Machine) — بيتحكم في الشاشة اللي الطالب يشوفها:
                                                                            //   not_started → mic_check → intro → conversation → feedback → closing → completed
                                                                            //   أو interrupted لو حصلت مشكلة
            $table->timestamp('started_at')->nullable();                     // وقت بدء الجلسة — بيتسجل لما الطالب يضغط "ابدأ". مهم لحساب المدة
            $table->timestamp('ended_at')->nullable();                       // وقت انتهاء الجلسة — بيتسجل لما الجلسة تخلص أو تنقطع
            $table->integer('duration_seconds')->nullable();                 // مدة الجلسة بالثواني — بيتحسب تلقائياً (ended_at - started_at). الباحث محتاجه في التحليل الإحصائي
            $table->text('notes')->nullable();                               // ملاحظات — الباحث أو المشرف يقدر يكتب ملاحظة على الجلسة (مثل: "الطالب كان متوتر")
            $table->timestamps();                                           // created_at / updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_sessions');
    }
};
