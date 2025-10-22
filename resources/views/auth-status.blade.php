<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Autentikasi</title>
</head>
<body>
    <main>
        <h1>Status Autentikasi</h1>

        @if(auth()->check())
            <p>Anda sudah login sebagai <strong>{{ auth()->user()->name }}</strong>.</p>
            <p>Halaman seperti <a href="{{ route('home') }}">beranda</a> dilindungi oleh middleware SSO.</p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Logout</button>
            </form>
        @else
            <p>Anda belum login. Halaman ini tetap dapat diakses tanpa autentikasi.</p>
            <p>Halaman dalam grup middleware, misalnya <a href="{{ route('home') }}">beranda</a>, akan mengarahkan Anda ke login.</p>
            <a href="{{ route('login') }}">Login via IAM</a>
        @endif
    </main>
</body>
</html>
