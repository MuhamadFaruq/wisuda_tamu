<?php

namespace App\Http\Controllers;

use App\Models\InstitutionalGuest;
use App\Support\SeatNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstitutionalGuestController extends Controller
{
    public function show(InstitutionalGuest $institutionalGuest): View
    {
        return view('institutional-guests.show', compact('institutionalGuest'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => ['required','string','max:120'],
            'institution' => ['required','string','max:150'],
            'position' => ['nullable','string','max:120'],
            'category' => ['required','string','max:60'],
            'phone' => ['nullable','string','max:30'],
            'companions' => ['nullable','integer','min:0','max:10'],
            'notes' => ['nullable','string','max:500'],
        ]);

        $next = (InstitutionalGuest::max('id') ?? 0) + 1;
        $data['code'] = 'USH-INS-'.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
        $data['seat_number'] = SeatNumber::forInstitutionalGuest();
        InstitutionalGuest::create($data);

        return back()->with('success', "Tamu institusi dan barcode berhasil dibuat. Nomor kursi: {$data['seat_number']}.");
    }

    public function update(Request $request, InstitutionalGuest $institutionalGuest): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => ['required','string','max:120'],
            'institution' => ['required','string','max:150'],
            'position' => ['nullable','string','max:120'],
            'category' => ['required','string','max:60'],
            'phone' => ['nullable','string','max:30'],
            'companions' => ['nullable','integer','min:0','max:10'],
            'notes' => ['nullable','string','max:500'],
        ]);
        $institutionalGuest->update($data);

        return back()->with('success', 'Data tamu institusi berhasil diperbarui.');
    }
}
