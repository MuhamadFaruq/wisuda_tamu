@extends('layouts.app')
@section('title','Undangan '.$institutionalGuest->full_name)
@section('heading','Undangan tamu institusi')
@section('content')
<a class="back-link" href="{{ route('dashboard') }}#tamu-institusi">← Kembali ke tamu institusi</a>
<section class="institution-invitation">
    <div class="institution-ticket">
        <div class="ticket-brand"><img class="brand-logo" src="{{ asset('images/logo-ush.png') }}" alt="Logo USH"><div><span class="eyebrow">UNIVERSITAS SUGENG HARTONO</span><h2>Undangan Kehormatan</h2></div><span class="vip-label">{{ $institutionalGuest->category }}</span></div>
        <div class="institution-ticket-body">
            <span class="eyebrow">DENGAN HORMAT MENGUNDANG</span>
            <h3>{{ $institutionalGuest->full_name }}</h3>
            <p>{{ $institutionalGuest->position ?: 'Tamu Undangan' }}<br><strong>{{ $institutionalGuest->institution }}</strong></p>
            <div class="event-info"><div><small>Hari & tanggal</small><strong>{{ $activeAgenda?->event_date?->translatedFormat('l, d F Y · H:i') }}</strong></div><div><small>Tempat</small><strong>{{ $activeAgenda?->venue }}</strong></div><div><small>Pendamping</small><strong>{{ $institutionalGuest->companions }} orang</strong></div></div>
        </div>
        <div class="ticket-barcode">{!! \App\Support\QrCodeGenerator::svg($institutionalGuest->code) !!}<p>Tunjukkan QR Code ini kepada petugas registrasi</p></div>
    </div>
    <aside class="detail-panel"><span class="eyebrow">STATUS KEHADIRAN</span><h3>{{ $institutionalGuest->checked_in_at ? 'Sudah hadir' : 'Menunggu kehadiran' }}</h3>
        <div class="institution-status {{ $institutionalGuest->checked_in_at ? 'present' : '' }}">{{ $institutionalGuest->checked_in_at ? 'Check-in '.$institutionalGuest->checked_in_at->format('d/m/Y H:i').' · '.$institutionalGuest->gate : 'QR Code belum digunakan' }}</div>
        <dl><dt>Kode undangan</dt><dd>{{ $institutionalGuest->code }}</dd><dt>Kategori</dt><dd>{{ $institutionalGuest->category }}</dd><dt>Catatan protokoler</dt><dd>{{ $institutionalGuest->notes ?: '-' }}</dd></dl>
        <button class="btn btn-gold full" data-modal="editInstitutionDetail">✎ Update data</button>
    </aside>
</section>
<button class="btn btn-outline print-button" onclick="window.print()">▣ Cetak undangan</button>
@endsection

@section('modals')
<dialog id="editInstitutionDetail" class="modal"><form method="post" action="{{ route('institutional-guests.update',$institutionalGuest) }}">@csrf @method('PUT')
    <div class="modal-head"><div><span class="eyebrow">{{ $institutionalGuest->code }}</span><h3>Update tamu institusi</h3></div><button type="button" class="modal-close">×</button></div>
    <div class="form-grid"><label>Nama lengkap<input name="full_name" value="{{ $institutionalGuest->full_name }}" required></label><label>Instansi<input name="institution" value="{{ $institutionalGuest->institution }}" required></label></div>
    <div class="form-grid"><label>Jabatan<input name="position" value="{{ $institutionalGuest->position }}"></label><label>Kategori<input name="category" value="{{ $institutionalGuest->category }}" required></label></div>
    <div class="form-grid"><label>Nomor telepon<input name="phone" value="{{ $institutionalGuest->phone }}"></label><label>Jumlah pendamping<input type="number" name="companions" min="0" max="10" value="{{ $institutionalGuest->companions }}"></label></div>
    <label>Catatan protokoler<textarea name="notes" rows="3">{{ $institutionalGuest->notes }}</textarea></label>
    <button class="btn btn-gold full">Simpan perubahan →</button>
</form></dialog>
@endsection
