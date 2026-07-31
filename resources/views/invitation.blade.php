@extends('layouts.app')
@section('title','Undangan '.$invitation->student->name)
@section('heading','Detail undangan')
@section('content')
<a class="back-link" href="{{ route('dashboard') }}">← Kembali ke ringkasan</a>
<section class="invitation-card">
    <div class="ticket">
        <div class="ticket-brand"><img class="brand-logo" src="{{ asset('images/logo-ush.png') }}" alt="Logo USH"><div><span class="eyebrow">UNIVERSITAS SUGENG HARTONO</span><h2>Undangan Wisuda</h2></div><button class="btn btn-gold ticket-update" type="button" data-modal="updateInvitationModal">✎ Update data</button></div>
        <div class="ticket-body"><p>Dengan hormat mengundang orang tua/wali dari</p><h3>{{ $invitation->student->name }}</h3><span>{{ $invitation->student->nim }} · {{ $invitation->student->study_program }}</span>
            <div class="event-info"><div><small>Hari & tanggal</small><strong>{{ $activeAgenda?->event_date?->translatedFormat('l, d F Y · H:i') ?? 'Kamis, 30 Juli 2026' }}</strong></div><div><small>Tempat</small><strong>{{ $activeAgenda?->venue ?? 'Auditorium USH' }}</strong></div><div><small>Kuota tamu</small><strong>{{ $invitation->total_quota }} orang</strong></div></div>
        </div>
        <div class="ticket-barcode">{!! \App\Support\Code39::svg($invitation->code, 76) !!}<p>Tunjukkan barcode ini kepada petugas registrasi</p></div>
    </div>
    <aside class="detail-panel"><span class="eyebrow">STATUS UNDANGAN</span><h3>{{ $invitation->attendances->count() }} dari {{ $invitation->total_quota }} hadir</h3><div class="progress"><i style="width:{{ min(100,$invitation->total_quota ? $invitation->attendances->count()/$invitation->total_quota*100 : 0) }}%"></i></div>
        <ul>@foreach($invitation->attendances as $attendance)<li><span>✓</span><div><strong>{{ $attendance->guest_name }}</strong><small>{{ str_replace('_',' ',$attendance->guest_type) }} · {{ $attendance->checked_in_at->format('H:i') }}</small></div></li>@endforeach</ul>
        <div class="guest-roster">
            <h4>Daftar tamu untuk scan otomatis</h4>
            @forelse($invitation->registeredGuests as $guest)
                <div class="roster-row"><span class="{{ $guest->attended_at ? 'done' : '' }}">{{ $guest->attended_at ? '✓' : '○' }}</span><div><strong>{{ $guest->full_name }}</strong><small>{{ ucwords(str_replace('_',' ',$guest->guest_type)) }} · {{ $guest->attended_at ? 'Sudah hadir' : 'Belum hadir' }}</small></div></div>
            @empty <p class="roster-empty">Belum ada nama tamu. Daftarkan agar nama dan jenis muncul otomatis saat barcode dipindai.</p> @endforelse
            @if($invitation->registeredGuests->count() < $invitation->total_quota)
            <form method="post" action="{{ route('invitations.guests.store',$invitation) }}">@csrf
                <label>Nama lengkap tamu<input name="full_name" placeholder="Nama sesuai identitas" required></label>
                <label>Jenis tamu<select name="guest_type"><option value="orang_tua">Orang tua</option><option value="wali">Wali</option>@if($invitation->extra_quota)<option value="tamu_tambahan">Tamu tambahan</option>@endif</select></label>
                <button class="btn btn-outline full">＋ Daftarkan tamu</button>
            </form>
            @endif
        </div>
        <form method="post" action="{{ route('invitations.package',$invitation) }}">@csrf @method('PATCH')<h4>Atur paket tambahan</h4><label>Nama paket<input name="package_name" value="{{ $invitation->package_name }}" required></label><label>Kuota tambahan<input type="number" name="extra_quota" min="1" max="8" value="{{ max(1,$invitation->extra_quota) }}" required></label><button class="btn btn-navy full">Simpan perubahan</button></form>
    </aside>
</section>
<button class="btn btn-outline print-button" onclick="window.print()">▣ Cetak undangan</button>
@endsection

@section('modals')
<dialog id="updateInvitationModal" class="modal"><form method="post" action="{{ route('invitations.update',$invitation) }}">@csrf @method('PUT')
    <div class="modal-head"><div><span class="eyebrow">DETAIL DATABASE</span><h3>Update data undangan</h3></div><button type="button" class="modal-close">×</button></div>
    <div class="form-grid"><label>NIM<input name="nim" value="{{ $invitation->student->nim }}" required></label><label>Nama mahasiswa<input name="name" value="{{ $invitation->student->name }}" required></label></div>
    <label>Fakultas<input name="faculty" value="{{ $invitation->student->faculty }}" required></label>
    <label>Program studi<input name="study_program" value="{{ $invitation->student->study_program }}" required></label>
    <label>Catatan undangan<textarea name="notes" rows="3" placeholder="Catatan opsional">{{ $invitation->notes }}</textarea></label>
    <button class="btn btn-gold full">Simpan perubahan →</button>
</form></dialog>
@endsection
