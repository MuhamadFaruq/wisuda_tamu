<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('event_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->dateTime('event_date');
            $table->string('venue');
            $table->string('period');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        DB::table('event_settings')->insert([
            'name' => 'Wisuda Universitas Sugeng Hartono',
            'event_date' => '2026-07-30 08:00:00',
            'venue' => 'Auditorium USH',
            'period' => 'Periode II Tahun 2026',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::create('registered_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->string('full_name');
            $table->enum('guest_type', ['orang_tua', 'wali', 'tamu_tambahan']);
            $table->timestamp('attended_at')->nullable();
            $table->timestamps();
            $table->index(['invitation_id', 'attended_at']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('registered_guest_id')->nullable()->after('invitation_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', fn (Blueprint $table) => $table->dropConstrainedForeignId('registered_guest_id'));
        Schema::dropIfExists('registered_guests');
        Schema::dropIfExists('event_settings');
    }
};
