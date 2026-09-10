<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversation_turns', function (Blueprint $table) {
            $table->id();                                                               // رقم الدور التلقائي
            $table->foreignId('session_id')->constrained('learning_sessions')->cascadeOnDelete(); // 🔗 ربط بالجلسة — كل دور حواري تابع لجلسة معينة
            $table->integer('turn_number');                                              // رقم الدور في المحادثة (1, 2, 3...) — لترتيب الحوار زمنياً. الأرقام الفردية غالباً أحمد، الزوجية الطالب
            $table->string('speaker');                                                   // مين اللي اتكلم؟ ('student' أو 'avatar') — عشان نعرف نفرّق بين كلام الطالب ورد أحمد
            $table->text('text_content')->nullable();                                    // النص اللي اتقال — كلام الطالب (بعد التحويل من صوت لنص) أو رد أحمد المكتوب
            $table->string('audio_url')->nullable();                                     // رابط ملف الصوت (لو سجلنا) — مش أساسي دلوقتي بس مفيد لو الباحث عايز يراجع نطق الطالب
            $table->float('asr_confidence')->nullable();                                 // نسبة ثقة التعرف على الكلام (0.0 → 1.0) — لو منخفضة يعني الميكروفون مش واضح أو الطالب همس
            $table->string('feedback_type')->default('none');                            // نوع التغذية الراجعة اللي أحمد أداها:
                                                                                        //   none = كلام الطالب صح، رد عادي
                                                                                        //   recast = تصحيح ضمني (أحمد يعيد الجملة صح بدون ما يقول "غلط")
                                                                                        //   clarification = طلب توضيح (أحمد يقول "ممكن توضح أكتر؟")
                                                                                        //   model = نموذج (أحمد يقول الجملة الصحيحة ويطلب الطالب يكررها — آخر محاولة)
            $table->text('feedback_text')->nullable();                                   // نص التغذية الراجعة التفصيلي — شرح مختصر للخطأ أو التلميح (مثل: "نستخدم am مع العمر")
            $table->boolean('is_correct')->nullable();                                   // هل كلام الطالب كان صحيح لغوياً؟ — مهم جداً للباحث في التحليل الإحصائي (نسبة الصح vs الغلط)
            $table->integer('attempt_number')->default(1);                               // رقم المحاولة (1, 2, 3) — الطالب عنده 3 محاولات كحد أقصى لكل مهمة (Recast → Clarification → Model)
            $table->json('llm_response_json')->nullable();                               // الرد الخام الكامل من الـ AI — بيتخزن كـ JSON عشان لو حبينا نحلل قرارات الـ AI لاحقاً (للبحث)
            $table->integer('latency_ms')->nullable();                                   // زمن استجابة الـ AI بالميلي ثانية — مهم لقياس أداء النظام وتجربة المستخدم
            $table->timestamps();                                                       // created_at / updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_turns');
    }
};
