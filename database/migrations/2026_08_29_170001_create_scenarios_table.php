<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scenarios', function (Blueprint $table) {
            $table->id();                                    // رقم تلقائي — المرجع الأساسي للسيناريو
            $table->integer('number');                       // رقم السيناريو (1-8) — ترتيب السيناريوهات حسب خطة البحث (ADDIE)
            $table->string('title');                         // عنوان السيناريو بالإنجليزي (مثل "Self Introduction") — يظهر للطالب في واجهة الاختيار
            $table->string('title_ar');                      // عنوان السيناريو بالعربي (مثل "التعريف بالنفس") — لأن واجهة التنقل بالعربي حسب طلب الباحث
            $table->string('topic');                         // الموضوع العام (مثل "Personal Information & Greetings") — يوضح السياق اللي المحادثة هتدور فيه
            $table->string('communicative_function');         // الوظيفة التواصلية — المهارة اللغوية اللي الطالب المفروض يتدرب عليها (مش الموضوع، لكن إيه يقدر يعمل بالإنجليزي)
            $table->json('b1_axes');                         // محاور التقييم حسب CEFR B1 — مثل Grammar, Pronunciation, Discourse — الباحث بيقيّم الطالب على المحاور دي
            $table->json('vocabulary');                      // المفردات المستهدفة — كلمات المفروض الطالب يستخدمها في السيناريو ده، والـ AI بيشجعه عليها
            $table->longText('system_prompt');               // 🔑 الأهم — التعليمات اللي بتروح للـ AI (LLM) عشان يعرف شخصيته وأسلوبه وحدود ردوده
            $table->longText('scenario_module');             // تفاصيل الموقف الحواري — إيه بالظبط أحمد يسأل ويعمل (مثل: "اسأل الطالب عن اسمه ومدرسته")
            $table->text('completion_criteria');             // معيار الإكمال — امتى نقول إن الطالب نجح في المهمة (مثل: "الطالب قدم نفسه بالاسم والمكان وحقيقة واحدة")
            $table->boolean('is_active')->default(true);     // مفعّل/معطّل — الباحث يقدر يوقف سيناريو مؤقتاً بدون حذفه (مثلاً لو عايز يختبر 4 سيناريوهات بس)
            $table->integer('sort_order');                   // ترتيب العرض — بيتحكم في ترتيب ظهور السيناريوهات للطالب (ممكن يختلف عن الرقم)
            $table->timestamps();                           // created_at / updated_at — تتبع وقت إنشاء وآخر تعديل للسيناريو
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scenarios');
    }
};
