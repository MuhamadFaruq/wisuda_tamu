<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Invitation;
use App\Models\InstitutionalGuest;
use App\Models\RegisteredGuest;
use App\Models\Student;
use App\Support\SeatNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('q'));
        $institutionSearch = trim((string) $request->get('qi'));
        $invitations = Invitation::with(['student', 'attendances', 'registeredGuests'])
            ->when($search, fn ($query) => $query->where('code', 'like', "%{$search}%")
                ->orWhereHas('student', fn ($student) => $student->where('name', 'like', "%{$search}%")->orWhere('nim', 'like', "%{$search}%")))
            ->latest()->paginate(8)->withQueryString();
        return view('dashboard', [
            'invitations' => $invitations,
            'totalInvitations' => Invitation::count(),
            'totalQuota' => Invitation::sum('base_quota') + Invitation::sum('extra_quota'),
            'totalPresent' => Attendance::count(),
            'recentAttendances' => Attendance::with('invitation.student')->latest('checked_in_at')->limit(6)->get(),
            'institutionalGuests' => InstitutionalGuest::query()
                ->when($institutionSearch, fn ($query) => $query
                    ->where(function ($filter) use ($institutionSearch) {
                        $filter->where('full_name', 'like', "%{$institutionSearch}%")
                            ->orWhere('institution', 'like', "%{$institutionSearch}%")
                            ->orWhere('position', 'like', "%{$institutionSearch}%")
                            ->orWhere('category', 'like', "%{$institutionSearch}%")
                            ->orWhere('code', 'like', "%{$institutionSearch}%");
                    }))
                ->latest()->paginate(8, ['*'], 'institusi')->withQueryString(),
            'institutionSearch' => $institutionSearch,
            'institutionalTotal' => InstitutionalGuest::count(),
            'institutionalPresent' => InstitutionalGuest::whereNotNull('checked_in_at')->count(),
        ]);
    }

    public function storeInvitation(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nim' => ['required','string','max:30','unique:students,nim'], 'name' => ['required','string','max:120'],
            'faculty' => ['required','string','max:120'], 'study_program' => ['required','string','max:120'],
            'extra_quota' => ['nullable','integer','min:0','max:8'],
            'package_name' => ['nullable','string','max:80'],
        ]);
        $student = Student::create($data);
        $student->invitation()->create([
            'code' => 'USH-'.now()->format('y').'-'.strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $data['nim']), -6)),
            'base_quota' => 2, 'extra_quota' => $data['extra_quota'] ?? 0, 'package_name' => $data['package_name'] ?? null,
        ]);
        return back()->with('success', 'Undangan dan barcode berhasil dibuat.');
    }

    public function addPackage(Request $request, Invitation $invitation): RedirectResponse
    {
        $data = $request->validate(['extra_quota' => ['required','integer','min:1','max:8'], 'package_name' => ['required','string','max:80']]);
        $invitation->update($data);
        return back()->with('success', 'Paket tambahan berhasil diperbarui.');
    }

    public function updateInvitation(Request $request, Invitation $invitation): RedirectResponse
    {
        $studentData = $request->validate([
            'nim' => ['required','string','max:30','unique:students,nim,'.$invitation->student_id],
            'name' => ['required','string','max:120'],
            'faculty' => ['required','string','max:120'],
            'study_program' => ['required','string','max:120'],
            'notes' => ['nullable','string','max:500'],
        ]);

        $invitation->student->update([
            'nim' => $studentData['nim'],
            'name' => $studentData['name'],
            'faculty' => $studentData['faculty'],
            'study_program' => $studentData['study_program'],
        ]);
        $invitation->update(['notes' => $studentData['notes'] ?? null]);

        return back()->with('success', 'Data undangan berhasil diperbarui.');
    }

    public function checkIn(Request $request): RedirectResponse
    {
        $data = $request->validate(['code' => ['required','string'], 'registered_guest_id' => ['nullable','integer'], 'institutional_guest_id' => ['nullable','integer'], 'guest_name' => ['required','string','max:120'], 'guest_type' => ['required','string','max:60']]);
        if (! empty($data['institutional_guest_id'])) {
            $guest = InstitutionalGuest::where('code', strtoupper(trim($data['code'])))->find($data['institutional_guest_id']);
            if (! $guest) return back()->withErrors(['code' => 'Barcode tamu institusi tidak valid.'])->withInput();
            if ($guest->checked_in_at) return back()->withErrors(['code' => 'Tamu institusi ini sudah melakukan check-in.'])->withInput();
            $guest->update(['checked_in_at' => now(), 'gate' => 'Pintu Utama']);
            return back()->with('success', "Check-in {$guest->full_name} dari {$guest->institution} berhasil. Nomor kursi: {$guest->seat_number}.");
        }
        $invitation = Invitation::where('code', strtoupper(trim($data['code'])))->first();
        if (! $invitation) return back()->withErrors(['code' => 'Barcode tidak terdaftar.'])->withInput();
        if ($invitation->attendances()->count() >= $invitation->total_quota) return back()->withErrors(['code' => 'Kuota undangan sudah penuh.'])->withInput();
        if ($data['guest_type'] === 'tamu_tambahan' && $invitation->extra_quota < 1) return back()->withErrors(['code' => 'Undangan ini tidak memiliki paket tambahan.'])->withInput();
        $registeredGuest = isset($data['registered_guest_id'])
            ? RegisteredGuest::where('invitation_id', $invitation->id)->whereNull('attended_at')->find($data['registered_guest_id'])
            : null;
        if ($registeredGuest) {
            $data['guest_name'] = $registeredGuest->full_name;
            $data['guest_type'] = $registeredGuest->guest_type;
            $registeredGuest->update(['attended_at' => now()]);
        }
        $invitation->attendances()->create(['registered_guest_id' => $registeredGuest?->id, 'guest_name' => $data['guest_name'], 'guest_type' => $data['guest_type'], 'checked_in_at' => now(), 'gate' => 'Pintu Utama']);
        return back()->with('success', "Check-in {$data['guest_name']} berhasil. Nomor kursi: {$registeredGuest?->seat_number}.");
    }

    public function lookup(string $code)
    {
        $normalizedCode = strtoupper(trim($code));
        $institutionalGuest = InstitutionalGuest::where('code', $normalizedCode)->first();
        if ($institutionalGuest) {
            if ($institutionalGuest->checked_in_at) return response()->json(['message' => 'Tamu institusi ini sudah melakukan check-in.'], 422);
            return response()->json([
                'kind' => 'institutional',
                'guest' => ['id' => $institutionalGuest->id, 'full_name' => $institutionalGuest->full_name, 'guest_type' => 'tamu_institusi', 'seat_number' => $institutionalGuest->seat_number],
                'institution' => $institutionalGuest->institution,
                'position' => $institutionalGuest->position,
                'category' => $institutionalGuest->category,
            ]);
        }

        $invitation = Invitation::with('student')->where('code', $normalizedCode)->first();
        if (! $invitation) return response()->json(['message' => 'Barcode tidak terdaftar.'], 404);
        if ($invitation->attendances()->count() >= $invitation->total_quota) return response()->json(['message' => 'Kuota undangan sudah penuh.'], 422);

        $guest = $invitation->registeredGuests()->whereNull('attended_at')->oldest()->first();
        if (! $guest) return response()->json(['message' => 'Belum ada tamu tersisa yang terdaftar pada undangan ini.'], 422);

        return response()->json([
            'kind' => 'student',
            'guest' => ['id' => $guest->id, 'full_name' => $guest->full_name, 'guest_type' => $guest->guest_type, 'seat_number' => $guest->seat_number],
            'student' => $invitation->student->name,
            'quota' => ['used' => $invitation->attendances()->count(), 'total' => $invitation->total_quota],
        ]);
    }

    public function storeGuest(Request $request, Invitation $invitation): RedirectResponse
    {
        if ($invitation->registeredGuests()->count() >= $invitation->total_quota) {
            return back()->withErrors(['guest' => 'Jumlah tamu terdaftar sudah mencapai kuota undangan.']);
        }
        $data = $request->validate(['full_name' => ['required','string','max:120'], 'guest_type' => ['required','in:orang_tua,wali,tamu_tambahan']]);
        if ($data['guest_type'] === 'tamu_tambahan' && $invitation->extra_quota < 1) {
            return back()->withErrors(['guest' => 'Tambahkan paket kuota sebelum mendaftarkan tamu tambahan.']);
        }
        $data['seat_number'] = SeatNumber::forRegisteredGuest($data['guest_type']);
        $invitation->registeredGuests()->create($data);
        return back()->with('success', "Nama tamu berhasil didaftarkan dengan nomor kursi {$data['seat_number']}.");
    }

    public function invitation(Invitation $invitation): View
    {
        $invitation->load('student', 'attendances', 'registeredGuests');
        return view('invitation', compact('invitation'));
    }
}
