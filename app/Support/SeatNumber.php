<?php

namespace App\Support;

use App\Models\InstitutionalGuest;
use App\Models\RegisteredGuest;

class SeatNumber
{
    public static function forRegisteredGuest(string $guestType): string
    {
        if ($guestType === 'tamu_tambahan') {
            return 'T'.(self::highestNumber(RegisteredGuest::where('guest_type', 'tamu_tambahan')->pluck('seat_number'), '/^T(\d+)$/') + 1);
        }

        $highestPosition = 0;
        foreach (RegisteredGuest::whereIn('guest_type', ['orang_tua', 'wali'])->pluck('seat_number') as $seat) {
            if (preg_match('/^(\d+)([AB])$/', (string) $seat, $matches)) {
                $position = (((int) $matches[1] - 1) * 2) + ($matches[2] === 'B' ? 2 : 1);
                $highestPosition = max($highestPosition, $position);
            }
        }

        $nextPosition = $highestPosition + 1;

        return (string) (intdiv($nextPosition - 1, 2) + 1).($nextPosition % 2 === 1 ? 'A' : 'B');
    }

    public static function forInstitutionalGuest(): string
    {
        return 'V'.(self::highestNumber(InstitutionalGuest::pluck('seat_number'), '/^V(\d+)$/') + 1);
    }

    private static function highestNumber(iterable $seats, string $pattern): int
    {
        $highest = 0;
        foreach ($seats as $seat) {
            if (preg_match($pattern, (string) $seat, $matches)) {
                $highest = max($highest, (int) $matches[1]);
            }
        }

        return $highest;
    }
}
