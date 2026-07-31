<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\EventSetting;
use App\Models\Invitation;
use App\Models\InstitutionalGuest;
use App\Models\RegisteredGuest;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;

class GuestReportPdf
{
    public function make(): DomPdf
    {
        $invitations = Invitation::with([
            'student',
            'attendances',
            'registeredGuests' => fn ($query) => $query->orderBy('full_name'),
        ])->orderBy('code')->get();

        $logoPath = public_path('images/logo-ush.png');
        $logoData = file_exists($logoPath)
            ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath))
            : null;

        return Pdf::loadView('reports.guests', [
            'agenda' => EventSetting::where('is_active', true)->first(),
            'invitations' => $invitations,
            'institutionalGuests' => InstitutionalGuest::orderBy('category')->orderBy('full_name')->get(),
            'totalStudentGuests' => RegisteredGuest::count(),
            'totalInstitutionalGuests' => InstitutionalGuest::count(),
            'totalPresent' => Attendance::count() + InstitutionalGuest::whereNotNull('checked_in_at')->count(),
            'logoData' => $logoData,
            'printedAt' => now(),
        ])->setPaper('a4', 'landscape');
    }
}
