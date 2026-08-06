<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('registered_guests', function (Blueprint $table) {
            $table->string('seat_number', 20)->nullable()->after('guest_type')->unique();
        });
        Schema::table('institutional_guests', function (Blueprint $table) {
            $table->string('seat_number', 20)->nullable()->after('companions')->unique();
        });

        $basePosition = 0;
        $additionalPosition = 0;
        DB::table('registered_guests')->orderBy('id')->get(['id', 'guest_type'])->each(
            function ($guest) use (&$basePosition, &$additionalPosition): void {
                if ($guest->guest_type === 'tamu_tambahan') {
                    $seat = 'T'.++$additionalPosition;
                } else {
                    $position = ++$basePosition;
                    $seat = (string) (intdiv($position - 1, 2) + 1).($position % 2 === 1 ? 'A' : 'B');
                }
                DB::table('registered_guests')->where('id', $guest->id)->update(['seat_number' => $seat]);
            }
        );

        DB::table('institutional_guests')->orderBy('id')->pluck('id')->each(
            fn ($id, $index) => DB::table('institutional_guests')->where('id', $id)->update(['seat_number' => 'V'.($index + 1)])
        );
    }

    public function down(): void
    {
        Schema::table('registered_guests', fn (Blueprint $table) => $table->dropColumn('seat_number'));
        Schema::table('institutional_guests', fn (Blueprint $table) => $table->dropColumn('seat_number'));
    }
};
