<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SAPA Wisuda') — Universitas Sugeng Hartono</title>
    <meta name="description" content="Sistem absensi tamu wisuda Universitas Sugeng Hartono">
    <link rel="icon" type="image/png" href="{{ asset('images/logo-ush.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="{{ route('dashboard') }}">
            <img class="brand-logo" src="{{ asset('images/logo-ush.png') }}" alt="Logo Universitas Sugeng Hartono">
            <span><strong>SAPA</strong><small>Sistem Absensi Wisuda</small></span>
        </a>
        <nav>
            <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><span>⌂</span> Ringkasan</a>
            <a href="{{ route('dashboard') }}#scanner"><span>▣</span> Pindai QR Code</a>
            <a href="{{ route('dashboard') }}#undangan"><span>♧</span> Data Undangan</a>
            <a href="{{ route('dashboard') }}#tamu-institusi"><span>☆</span> Tamu Institusi</a>
            <a href="{{ route('dashboard') }}#kehadiran"><span>✓</span> Kehadiran</a>
        </nav>
        <div class="sidebar-event">
            <span class="eyebrow">Agenda aktif</span>
            <strong>{{ $activeAgenda?->name ?? 'Wisuda Periode II' }}</strong>
            <small>{{ $activeAgenda?->event_date?->translatedFormat('d F Y · H:i') ?? '30 Juli 2026 · 08:00' }}<br>{{ $activeAgenda?->venue ?? 'Auditorium USH' }}</small>
            <span class="live"><i></i> Acara berlangsung</span>
            <button class="agenda-edit" type="button" data-modal="agendaModal">⚙ Ubah agenda aktif</button>
        </div>
        <div class="sidebar-user">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
            <div><strong>{{ auth()->user()->name }}</strong><small>Pintu Utama</small></div>
            <form method="post" action="{{ route('logout') }}">@csrf<button class="logout-button" title="Keluar dari aplikasi" aria-label="Keluar dari aplikasi"><span>↪</span> Keluar</button></form>
        </div>
    </aside>
    <main class="main">
        <header class="topbar">
            <button class="icon-button menu-button" id="menuButton" aria-label="Buka menu">☰</button>
            <div><span class="eyebrow">UNIVERSITAS SUGENG HARTONO</span><h1>@yield('heading', 'Selamat datang, '.auth()->user()->name)</h1></div>
            <div class="top-actions">
                <div class="date-pill">
                    <small>{{ $activeAgenda?->event_date?->translatedFormat('l') ?? 'Agenda' }}</small>
                    <strong>{{ $activeAgenda?->event_date?->translatedFormat('d F Y') ?? 'Belum diatur' }}</strong>
                </div>
                <button class="icon-button" aria-label="Notifikasi">♢<b></b></button>
            </div>
        </header>
        <div class="content">
            @if(session('success'))<div class="toast success">✓ <span>{{ session('success') }}</span><button>×</button></div>@endif
            @if($errors->any())<div class="toast error">! <span>{{ $errors->first() }}</span><button>×</button></div>@endif
            @yield('content')
        </div>
    </main>
    <div class="overlay" id="overlay"></div>
    @yield('modals')
    <dialog id="agendaModal" class="modal"><form method="post" action="{{ route('agenda.update') }}">
        @csrf
        @method('PUT')
        <div class="modal-head"><div><span class="eyebrow">PENGATURAN ACARA</span><h3>Ubah agenda aktif</h3></div><button type="button" class="modal-close">×</button></div>
        <label>Nama agenda<input name="name" value="{{ $activeAgenda?->name ?? 'Wisuda Universitas Sugeng Hartono' }}" required></label>
        <div class="form-grid"><label>Periode<input name="period" value="{{ $activeAgenda?->period ?? 'Periode II Tahun 2026' }}" required></label><label>Tanggal dan waktu<input type="datetime-local" name="event_date" value="{{ $activeAgenda?->event_date?->format('Y-m-d\TH:i') ?? '2026-07-30T08:00' }}" required></label></div>
        <label>Lokasi<input name="venue" value="{{ $activeAgenda?->venue ?? 'Auditorium USH' }}" required></label>
        <button class="btn btn-gold full">Simpan sebagai agenda aktif</button>
    </form></dialog>
</body>
</html>
