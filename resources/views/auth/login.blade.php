<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin — SAPA Wisuda</title>
    <meta name="description" content="Login admin Sistem Absensi Wisuda Universitas Sugeng Hartono">
    <link rel="icon" type="image/png" href="{{ asset('images/logo-ush.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-page">
    <main class="login-shell">
        <section class="login-visual">
            <div class="login-brand"><img src="{{ asset('images/logo-ush.png') }}" alt="Logo Universitas Sugeng Hartono"><div><strong>SAPA</strong><small>Sistem Absensi Wisuda</small></div></div>
            <div class="login-message"><span class="hero-kicker">UNIVERSITAS SUGENG HARTONO</span><h1>Selamat datang<br>di <em>meja registrasi.</em></h1><p>Kelola kehadiran tamu wisuda dengan cepat, aman, dan tertib melalui satu sistem terpadu.</p></div>
            <div class="login-event"><span>Wisuda Periode II</span><small>30 Juli 2026 · Auditorium USH</small></div>
        </section>
        <section class="login-form-wrap">
            <form class="login-form" method="post" action="{{ route('login.store') }}">@csrf
                <img class="login-mobile-logo" src="{{ asset('images/logo-ush.png') }}" alt="Logo USH">
                <span class="eyebrow">AKSES PETUGAS</span><h2>Masuk ke akun admin</h2><p>Gunakan akun yang telah terdaftar untuk melanjutkan.</p>
                @if(session('status'))<div class="login-alert success">{{ session('status') }}</div>@endif
                @if($errors->any())<div class="login-alert error">{{ $errors->first() }}</div>@endif
                <label>Username<div class="input-with-icon"><span>@</span><input type="text" name="username" value="{{ old('username') }}" placeholder="Masukkan username" autocomplete="username" required autofocus></div></label>
                <label>Kata sandi<div class="input-with-icon"><span>◇</span><input id="password" type="password" name="password" placeholder="Masukkan kata sandi" autocomplete="current-password" required><button type="button" id="passwordToggle" aria-label="Tampilkan kata sandi">◎</button></div></label>
                <label class="remember"><input type="checkbox" name="remember" value="1"> <span>Ingat saya di perangkat ini</span></label>
                <button class="btn btn-gold full login-submit">Masuk ke dashboard →</button>
                <small class="login-help">Butuh bantuan akses? Hubungi administrator sistem.</small>
            </form>
        </section>
    </main>
</body>
</html>
