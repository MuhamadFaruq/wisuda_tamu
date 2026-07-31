<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('nim')->unique();
            $table->string('name');
            $table->string('faculty');
            $table->string('study_program');
            $table->string('graduation_session')->default('Sesi 1');
            $table->timestamps();
        });
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->unsignedTinyInteger('base_quota')->default(2);
            $table->unsignedTinyInteger('extra_quota')->default(0);
            $table->string('package_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->string('guest_name');
            $table->enum('guest_type', ['orang_tua', 'wali', 'tamu_tambahan']);
            $table->timestamp('checked_in_at');
            $table->string('gate')->default('Pintu Utama');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('invitations');
        Schema::dropIfExists('students');
    }
};
