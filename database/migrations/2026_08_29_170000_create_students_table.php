<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();                                    // رقم الطالب التلقائي — المرجع الأساسي في كل الجداول
            $table->string('code', 8)->unique();             // الكود الرمزي (مثل STD10101) — بديل اسم المستخدم وكلمة السر، الطالب بيدخل بيه بس
            $table->string('group');                         // المجموعة (experimental / control) — الباحث محتاج يفرّق بين المجموعة التجريبية والضابطة في التحليل
            $table->string('school_name');                   // اسم المدرسة — لأن التجربة على 5 مدارس في أبها، والباحث محتاج يحلل النتائج حسب المدرسة
            $table->boolean('is_active')->default(true);     // هل الطالب نشط؟ — لو طالب انسحب أو فيه مشكلة، الباحث يعطّله بدل ما يحذفه (نحافظ على البيانات)
            $table->timestamps();                           // created_at / updated_at — تلقائي من Laravel لتتبع وقت الإنشاء والتعديل
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
