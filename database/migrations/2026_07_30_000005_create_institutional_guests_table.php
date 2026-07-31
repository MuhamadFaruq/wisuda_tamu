<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('institutional_guests', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('full_name');
            $table->string('institution');
            $table->string('position')->nullable();
            $table->string('category')->default('Tamu Institusi');
            $table->string('phone', 30)->nullable();
            $table->unsignedTinyInteger('companions')->default(0);
            $table->timestamp('checked_in_at')->nullable();
            $table->string('gate')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institutional_guests');
    }
};
