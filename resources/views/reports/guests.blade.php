<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<style>
    @page{margin:88px 28px 42px}*{box-sizing:border-box}body{font-family:DejaVu Sans,sans-serif;color:#182136;font-size:8px;margin:0}
    header{position:fixed;top:-66px;left:0;right:0;height:58px;border-bottom:3px solid #e8bf22;display:table;width:100%}
    .logo,.identity,.meta{display:table-cell;vertical-align:middle}.logo{width:56px}.logo img{width:48px;height:48px}.identity h1{font-size:16px;color:#182b55;margin:0 0 3px}.identity p,.meta p{margin:2px 0;color:#677187}.meta{text-align:right;width:250px}
    footer{position:fixed;bottom:-27px;left:0;right:0;border-top:1px solid #dfe3eb;padding-top:7px;color:#7b8495}.page-number{float:right}.page-number:after{content:"Halaman " counter(page)}
    .summary{width:100%;border-collapse:separate;border-spacing:7px 0;margin:0 -7px 15px}.summary td{border:1px solid #dfe3eb;border-left:4px solid #182b55;border-radius:4px;padding:9px 11px;width:20%}.summary small{color:#758096;text-transform:uppercase;letter-spacing:.5px}.summary strong{display:block;font-size:15px;color:#182b55;margin-top:3px}
    table.data{width:100%;border-collapse:collapse}.data thead{display:table-header-group}.data tr{page-break-inside:avoid}.data th{background:#182b55;color:#fff;padding:7px 6px;text-align:left;font-size:7px;text-transform:uppercase;letter-spacing:.3px}.data td{border-bottom:1px solid #e4e7ed;padding:6px}.data tbody tr:nth-child(even){background:#f7f8fb}.center{text-align:center}.status{font-weight:bold}.present{color:#178556}.waiting{color:#9a7010}.type{text-transform:capitalize}.empty{text-align:center;padding:30px;color:#7b8495}.section-title{font-size:11px;color:#182b55;margin:15px 0 7px;border-left:4px solid #e8bf22;padding-left:7px}.institution-table{page-break-before:auto}
</style>
</head>
<body>
<header>
    <div class="logo">@if($logoData)<img src="{{ $logoData }}" alt="Logo USH">@endif</div>
    <div class="identity"><h1>LAPORAN TAMU WISUDA</h1><p>Universitas Sugeng Hartono - {{ $agenda?->period ?? 'Agenda Aktif' }}</p></div>
    <div class="meta"><p><strong>{{ $agenda?->name ?? 'Wisuda Universitas Sugeng Hartono' }}</strong></p><p>{{ $agenda?->event_date?->translatedFormat('d F Y, H:i') }} | {{ $agenda?->venue }}</p></div>
</header>
<footer><span>Dicetak {{ $printedAt->translatedFormat('d F Y, H:i') }} oleh Sistem Absensi Wisuda USH</span><span class="page-number"></span></footer>

<table class="summary"><tr>
    <td><small>Mahasiswa</small><strong>{{ $invitations->count() }}</strong></td>
    <td><small>Tamu mahasiswa</small><strong>{{ $totalStudentGuests }}</strong></td>
    <td><small>Tamu institusi</small><strong>{{ $totalInstitutionalGuests }}</strong></td>
    <td><small>Sudah hadir</small><strong>{{ $totalPresent }}</strong></td>
    <td><small>Belum hadir</small><strong>{{ max(0,$totalStudentGuests+$totalInstitutionalGuests-$totalPresent) }}</strong></td>
</tr></table>

<h2 class="section-title">A. TAMU MAHASISWA</h2>
<table class="data">
    <thead><tr><th style="width:25px">No.</th><th>Kode</th><th>Mahasiswa</th><th>NIM</th><th>Nama lengkap tamu</th><th>Jenis tamu</th><th>Status</th><th>Waktu check-in</th><th>Pintu</th></tr></thead>
    <tbody>
    @php($number=1)
    @forelse($invitations as $invitation)
        @foreach($invitation->registeredGuests as $guest)
            @php($attendance=$invitation->attendances->firstWhere('registered_guest_id',$guest->id))
            <tr>
                <td class="center">{{ $number++ }}</td><td>{{ $invitation->code }}</td><td><strong>{{ $invitation->student->name }}</strong><br><small>{{ $invitation->student->study_program }}</small></td>
                <td>{{ $invitation->student->nim }}</td><td>{{ $guest->full_name }}</td><td class="type">{{ str_replace('_',' ',$guest->guest_type) }}</td>
                <td class="status {{ $attendance ? 'present' : 'waiting' }}">{{ $attendance ? 'Hadir' : 'Belum hadir' }}</td>
                <td>{{ $attendance?->checked_in_at?->format('d/m/Y H:i') ?? '-' }}</td><td>{{ $attendance?->gate ?? '-' }}</td>
            </tr>
        @endforeach
    @empty <tr><td colspan="9" class="empty">Belum ada data tamu.</td></tr> @endforelse
    </tbody>
</table>

<h2 class="section-title">B. TAMU INSTITUSI, VIP, DAN MITRA</h2>
<table class="data institution-table">
    <thead><tr><th style="width:25px">No.</th><th>Kode</th><th>Nama lengkap</th><th>Instansi</th><th>Jabatan</th><th>Kategori</th><th>Pendamping</th><th>Status</th><th>Check-in / Pintu</th></tr></thead>
    <tbody>
    @forelse($institutionalGuests as $guest)
        <tr>
            <td class="center">{{ $loop->iteration }}</td><td>{{ $guest->code }}</td><td><strong>{{ $guest->full_name }}</strong></td>
            <td>{{ $guest->institution }}</td><td>{{ $guest->position ?: '-' }}</td><td>{{ $guest->category }}</td><td class="center">{{ $guest->companions }}</td>
            <td class="status {{ $guest->checked_in_at ? 'present' : 'waiting' }}">{{ $guest->checked_in_at ? 'Hadir' : 'Belum hadir' }}</td>
            <td>{{ $guest->checked_in_at?->format('d/m/Y H:i') ?? '-' }}<br><small>{{ $guest->gate ?: '-' }}</small></td>
        </tr>
    @empty <tr><td colspan="9" class="empty">Belum ada data tamu institusi.</td></tr> @endforelse
    </tbody>
</table>
</body>
</html>
