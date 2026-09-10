<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();                                                               // رقم تلقائي
            $table->foreignId('session_id')->constrained('learning_sessions')->cascadeOnDelete(); // 🔗 ربط بالجلسة — الحادثة حصلت في أنهي جلسة
            $table->string('type');                                                     // نوع الحادثة التقنية:
                                                                                        //   mic_failure = الميكروفون بايظ أو مش شغال
                                                                                        //   connection_lost = الإنترنت وقع أثناء الجلسة
                                                                                        //   timeout = الطالب مرد في الوقت المحدد
                                                                                        //   off_topic = الطالب خرج عن الموضوع
                                                                                        //   inappropriate = محتوى غير لائق
                                                                                        //   facilitator_intervention = المشرف تدخل يدوياً
                                                                                        //   student_absent = الطالب ساب الجلسة
                                                                                        //   other = أي حاجة تانية
            $table->text('description')->nullable();                                    // وصف تفصيلي للحادثة — إيه بالظبط اللي حصل (بيتسجل تلقائي أو المشرف يكتبه)
            $table->integer('duration_seconds')->nullable();                             // مدة الانقطاع بالثواني — قد إيه الجلسة اتعطلت بسبب الحادثة (مهم لتنقية بيانات البحث)
            $table->text('action_taken')->nullable();                                   // الإجراء اللي اتاخد — إيه اللي حصل بعدها (مثل: "تم إعادة تشغيل الميكروفون")
            $table->text('impact')->nullable();                                         // تأثير الحادثة على الجلسة — هل الجلسة كملت عادي ولا اتقطعت (الباحث بيستبعد الجلسات المتأثرة)
            $table->timestamps();                                                       // created_at / updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
