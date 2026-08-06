@extends('layouts.app')

@section('content')
<section class="hero">
    <div><span class="hero-kicker">PUSAT KENDALI REGISTRASI</span><h2>Kelola tamu wisuda<br><em>lebih cepat & tertib.</em></h2><p>Pindai QR Code undangan, validasi kuota, dan pantau kehadiran secara langsung dari satu tempat.</p></div>
    <button class="btn btn-gold" data-modal="scanModal">▣ <span>Pindai QR Code</span></button>
</section>

<section class="stats-grid">
    <article class="stat-card navy"><div class="stat-icon">♧</div><div><small>Total undangan</small><strong>{{ number_format($totalInvitations) }}</strong><span>Mahasiswa terdaftar</span></div><i>↗</i></article>
    <article class="stat-card"><div class="stat-icon gold">♙</div><div><small>Kuota tamu</small><strong>{{ number_format($totalQuota) }}</strong><span>Termasuk paket tambahan</span></div><i>↗</i></article>
    <article class="stat-card"><div class="stat-icon green">✓</div><div><small>Sudah hadir</small><strong>{{ number_format($totalPresent) }}</strong><span>{{ $totalQuota ? round($totalPresent / $totalQuota * 100) : 0 }}% dari total kuota</span></div><i>↗</i></article>
    <article class="stat-card"><div class="stat-icon blue">⌁</div><div><small>Belum hadir</small><strong>{{ number_format(max(0, $totalQuota-$totalPresent)) }}</strong><span>Menunggu check-in</span></div><i>↗</i></article>
</section>

<section class="institution-strip">
    <div><span class="stat-icon institution">☆</span><p><small>TAMU INSTITUSI</small><strong>{{ $institutionalPresent }} dari {{ $institutionalTotal }} sudah hadir</strong><span>LLDIKTI, yayasan, pejabat, mitra, dan tamu VIP</span></p></div>
    <button class="btn btn-gold" data-modal="institutionModal">＋ Tambah tamu institusi</button>
</section>

<section class="workspace-grid" id="scanner">
    <article class="panel scan-panel">
        <div class="panel-heading"><div><span class="eyebrow">CHECK-IN CEPAT</span><h3>Pindai QR Code undangan</h3></div><span class="status"><i></i> Scanner siap</span></div>
        <button class="scan-zone" data-modal="scanModal">
            <span class="scan-corners"><i></i></span>
            <span class="scan-icon">▣</span>
            <strong>Siapkan scanner QR Code</strong>
            <small>Klik di sini, lalu pindai QR Code menggunakan scanner 2D</small>
        </button>
        <div class="scan-help"><span>⌨</span><p><strong>Scanner QR USB 2D siap digunakan</strong><br><small>Scanner akan membaca kode seperti keyboard dan menekan Enter otomatis.</small></p><button data-modal="scanModal">Mulai scan</button></div>
    </article>
    <article class="panel" id="kehadiran">
        <div class="panel-heading"><div><span class="eyebrow">AKTIVITAS TERKINI</span><h3>Tamu baru hadir</h3></div><a href="#undangan">Lihat semua →</a></div>
        <div class="activity-list">
            @forelse($recentAttendances as $attendance)
            <div class="activity"><div class="avatar guest">{{ strtoupper(substr($attendance->guest_name,0,1)) }}</div><div><strong>{{ $attendance->guest_name }}</strong><small>Tamu {{ $attendance->invitation->student->name }}</small></div><time>{{ $attendance->checked_in_at->format('H:i') }}<small>{{ $attendance->checked_in_at->diffForHumans() }}</small></time></div>
            @empty<div class="empty">Belum ada tamu yang check-in.</div>@endforelse
        </div>
    </article>
</section>

<section class="panel invitations institutional-panel" id="tamu-institusi">
    <div class="panel-heading table-head"><div><span class="eyebrow">UNDANGAN KHUSUS</span><h3>Tamu institusi & VIP</h3></div><div class="table-tools"><form action="{{ route('dashboard') }}#tamu-institusi"><input name="qi" value="{{ $institutionSearch }}" placeholder="Cari nama, instansi, jabatan, kategori..."><button aria-label="Cari tamu institusi">⌕</button></form>@if($institutionSearch)<a class="btn btn-outline" href="{{ route('dashboard') }}#tamu-institusi">Reset</a>@endif<button class="btn btn-navy" data-modal="institutionModal">＋ Tambah tamu institusi</button></div></div>
    <div class="table-wrap"><table>
        <thead><tr><th>Nama tamu</th><th>Instansi / Jabatan</th><th>Kategori</th><th>Kode QR</th><th>Pendamping</th><th>Status</th><th></th></tr></thead>
        <tbody>@forelse($institutionalGuests as $guest)
            <tr>
                <td><strong>{{ $guest->full_name }}</strong><small>{{ $guest->phone ?: 'Nomor telepon belum diisi' }}</small></td>
                <td><strong>{{ $guest->institution }}</strong><small>{{ $guest->position ?: '-' }}</small></td>
                <td><span class="institution-badge">{{ $guest->category }}</span></td>
                <td><code>{{ $guest->code }}</code></td><td><strong>{{ $guest->companions }} orang</strong></td>
                <td><span class="quota {{ $guest->checked_in_at ? 'full' : '' }}">{{ $guest->checked_in_at ? 'Hadir '.$guest->checked_in_at->format('H:i') : 'Belum hadir' }}</span></td>
                <td><a class="detail-button" href="{{ route('institutional-guests.show',$guest) }}">Detail →</a></td>
            </tr>
        @empty <tr><td colspan="7" class="empty">{{ $institutionSearch ? 'Tamu institusi tidak ditemukan.' : 'Belum ada tamu institusi.' }}</td></tr> @endforelse</tbody>
    </table></div>
    <div class="pagination">{{ $institutionalGuests->links() }}</div>
</section>

<section class="panel invitations" id="undangan">
    <div class="panel-heading table-head"><div><span class="eyebrow">MANAJEMEN UNDANGAN</span><h3>Daftar mahasiswa & kuota tamu</h3></div><div class="table-tools"><form><input name="q" value="{{ request('q') }}" placeholder="Cari nama, NIM, atau kode..."><button>⌕</button></form><a class="btn btn-outline" href="{{ route('reports.guests.pdf') }}">⇩ Export PDF</a><a class="btn btn-outline" href="{{ route('reports.invitations.xlsx') }}">⇩ Export Excel + QR Code</a><button class="btn btn-navy" data-modal="newModal">＋ Tambah undangan</button></div></div>
    <div class="table-wrap"><table>
        <thead><tr><th>Mahasiswa</th><th>Program studi</th><th>Kode undangan</th><th>Kuota</th><th>Kehadiran</th><th></th></tr></thead>
        <tbody>@forelse($invitations as $invitation)
            <tr><td><div class="student"><div class="avatar student-avatar">{{ strtoupper(substr($invitation->student->name,0,1)) }}</div><div><strong>{{ $invitation->student->name }}</strong><small>{{ $invitation->student->nim }}</small></div></div></td>
            <td><strong>{{ $invitation->student->study_program }}</strong><small>{{ $invitation->student->faculty }}</small></td>
            <td><code>{{ $invitation->code }}</code><small>{{ $invitation->package_name ?: 'Kuota reguler' }}</small></td>
            <td><strong>{{ $invitation->total_quota }} orang</strong><small>2 utama{{ $invitation->extra_quota ? ' + '.$invitation->extra_quota.' tambahan' : '' }}</small></td>
            <td><span class="quota {{ $invitation->attendances->count() >= $invitation->total_quota ? 'full' : '' }}">{{ $invitation->attendances->count() }}/{{ $invitation->total_quota }}</span></td>
            <td><a class="detail-button" href="{{ route('invitations.show',$invitation) }}">Detail →</a></td></tr>
        @empty<tr><td colspan="6" class="empty">Data undangan tidak ditemukan.</td></tr>@endforelse</tbody>
    </table></div>
    <div class="pagination">{{ $invitations->links() }}</div>
</section>
@endsection

@section('modals')
<dialog id="scanModal" class="modal"><form method="post" action="{{ route('checkin') }}">@csrf
    <div class="modal-head"><div><span class="eyebrow">REGISTRASI TAMU</span><h3>Scanner QR Code</h3></div><button type="button" class="modal-close">×</button></div>
    <div class="hardware-scanner"><span class="scanner-beam"></span><div class="scan-device">▣</div><div><strong>Scanner QR siap</strong><small>Hubungkan scanner QR 2D, klik kolom kode, lalu pindai QR Code.</small></div><i id="scannerStatus">MENUNGGU SCAN</i></div>
    <label>Kode undangan<input id="barcodeInput" class="barcode-field" name="code" value="{{ old('code') }}" placeholder="Klik di sini lalu scan QR Code" autocomplete="off" autofocus required></label>
    <small class="field-hint">Kode akan terisi otomatis. Tekan Enter untuk lanjut jika alat tidak mengirim Enter.</small>
    <input type="hidden" id="registeredGuestId" name="registered_guest_id">
    <div id="registeredGuestIds"></div>
    <input type="hidden" id="institutionalGuestId" name="institutional_guest_id">
    <div id="guestLookupMessage" class="lookup-message">Nama, jenis tamu, dan nomor kursi akan muncul setelah QR Code terbaca.</div>
    <div class="form-grid"><label>Nama tamu<input id="guestNameInput" name="guest_name" value="{{ old('guest_name') }}" placeholder="Otomatis dari QR Code" readonly required></label><label>Nomor kursi<input id="seatNumberInput" placeholder="Otomatis dari QR Code" readonly></label></div>
    <label>Jenis tamu<select id="guestTypeInput" name="guest_type" disabled><option value="orang_tua">Orang tua</option><option value="wali">Wali</option><option value="tamu_tambahan">Tamu tambahan</option><option value="tamu_institusi">Tamu institusi</option></select><input type="hidden" id="guestTypeHidden" name="guest_type"></label>
    <button class="btn btn-gold full">Konfirmasi check-in tamu yang tampil →</button>
</form></dialog>

<dialog id="newModal" class="modal"><form method="post" action="{{ route('invitations.store') }}">@csrf
    <div class="modal-head"><div><span class="eyebrow">UNDANGAN BARU</span><h3>Daftarkan mahasiswa</h3></div><button type="button" class="modal-close">×</button></div>
    <div class="form-grid"><label>NIM<input name="nim" required placeholder="062501004"></label><label>Nama mahasiswa<input name="name" required placeholder="Nama lengkap"></label></div>
    <label>Fakultas<input name="faculty" required placeholder="Fakultas Teknologi Hukum dan Bisnis"></label>
    <label>Program studi<input name="study_program" required placeholder="Ilmu Komputer"></label>
    <div class="package-box"><div><strong>Kuota dasar</strong><small>Maksimal 2 orang tua/wali</small></div><b>2 orang</b></div>
    <div class="form-grid"><label>Kuota tambahan<input type="number" name="extra_quota" min="0" max="8" value="0"></label><label>Nama paket<input name="package_name" placeholder="Opsional"></label></div>
    <button class="btn btn-gold full">Buat undangan & QR Code →</button>
</form></dialog>

<dialog id="institutionModal" class="modal"><form method="post" action="{{ route('institutional-guests.store') }}">@csrf
    <div class="modal-head"><div><span class="eyebrow">UNDANGAN INSTITUSI</span><h3>Tambah tamu khusus</h3></div><button type="button" class="modal-close">×</button></div>
    <div class="form-grid"><label>Nama lengkap<input name="full_name" required placeholder="Nama sesuai gelar"></label><label>Instansi<input name="institution" required placeholder="Contoh: LLDIKTI Wilayah VI"></label></div>
    <div class="form-grid"><label>Jabatan<input name="position" placeholder="Contoh: Kepala LLDIKTI"></label><label>Kategori<select name="category"><option>LLDIKTI</option><option>Yayasan</option><option>Pejabat Pemerintah</option><option>Mitra Universitas</option><option>Pimpinan Perguruan Tinggi</option><option>VIP/VVIP</option><option>Media</option><option>Tamu Institusi Lainnya</option></select></label></div>
    <div class="form-grid"><label>Nomor telepon<input name="phone" placeholder="Opsional"></label><label>Jumlah pendamping<input type="number" name="companions" min="0" max="10" value="0"></label></div>
    <label>Catatan protokoler<textarea name="notes" rows="3" placeholder="Contoh: Kursi baris depan, penyambutan khusus"></textarea></label>
    <button class="btn btn-gold full">Buat undangan & QR Code →</button>
</form></dialog>

@endsection
