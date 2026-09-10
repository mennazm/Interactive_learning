<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();                                                               // رقم تلقائي
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();  // 🔗 ربط بالطالب (اختياري) — مين عمل الحدث. nullable لأن بعض الأحداث ممكن تكون من النظام نفسه
            $table->foreignId('session_id')->nullable()->constrained('learning_sessions')->nullOnDelete(); // 🔗 ربط بالجلسة (اختياري) — الحدث حصل في أنهي جلسة. nullable لأحداث خارج الجلسات (مثل تسجيل الدخول)
            $table->string('action');                                                   // نوع الحدث — مثل:
                                                                                        //   "login" = تسجيل دخول
                                                                                        //   "logout" = تسجيل خروج
                                                                                        //   "session_started" = بدأ جلسة
                                                                                        //   "session_completed" = أنهى جلسة
                                                                                        //   "mic_permission_granted" = وافق على الميكروفون
                                                                                        //   "scenario_selected" = اختار سيناريو
            $table->json('details')->nullable();                                        // تفاصيل إضافية بصيغة JSON — أي بيانات مفيدة (مثل: IP, متصفح, نوع الجهاز, الوقت الفعلي)
            $table->timestamps();                                                       // created_at / updated_at — وقت حدوث الحدث بالظبط
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
