<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SSO Client - Dashboard</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            text-align: center;
            max-width: 600px;
            width: 100%;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
        }

        .welcome {
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .user-info {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: left;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 5px;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-logout {
            background: #dc3545;
        }

        .btn-logout:hover {
            background: #c82333;
        }

        .btn-dashboard {
            background: #28a745;
        }

        .btn-dashboard:hover {
            background: #218838;
        }

        .status {
            margin-top: 20px;
            padding: 15px;
            background: #d4edda;
            border-radius: 6px;
            font-size: 14px;
            text-align: left;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Welcome Back!</h1>
        <div class="welcome">You are successfully authenticated via SSO.</div>

        <div class="user-info">
            <h3>User Information:</h3>
            <p><strong>Name:</strong> {{ Auth::user()->name }}</p>
            <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
            <p><strong>User ID:</strong> {{ Auth::user()->id }}</p>
            @if (session('iam'))
                <p>{{ Auth::user() }}</p>
                <h4>SSO Data:</h4>
                <p><strong>Subject:</strong> {{ session('iam.sub') }}</p>
                <p><strong>App:</strong> {{ session('iam.app') }}</p>
            @endif
        </div>

        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-dashboard">Dashboard</a>

            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-logout">Logout</button>
            </form>
        </div>

        <div class="status">
            <strong>Session Status:</strong> Authenticated ✅<br>
            <strong>Session ID:</strong> {{ session()->getId() }}<br>
            <strong>Auth Check:</strong> {{ Auth::check() ? 'Yes' : 'No' }}<br>
            <strong>Last Login:</strong> {{ now() }}
        </div>

        <div style="margin-top: 20px;">
            <a href="{{ route('debug.session') }}" style="color: #666; font-size: 12px;">Debug Session</a> |
            <a href="{{ route('debug.auth') }}" style="color: #666; font-size: 12px;">Debug Auth</a>
        </div>
    </div>
</body>

</html>
