<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
</head>
<body>
    <main>
        @if(auth()->check())
            <p>Login OK: {{ auth()->user()->name }}</p>
            <p>SID: {{ session()->getId() }}</p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Logout</button>
            </form>
        @else
            <a href="{{ route('login') }}">Login via IAM</a>
        @endif
    </main>
</body>
</html>
