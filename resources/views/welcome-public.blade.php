<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SSO Client - Welcome</title>
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
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        h1 { color: #333; margin-bottom: 20px; }
        p { color: #666; line-height: 1.6; margin-bottom: 30px; }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: background 0.3s;
        }
        .btn:hover { background: #5a6fd8; }
        .status {
            margin-top: 30px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to SSO Client</h1>

        @if(session('message'))
            <div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                {{ session('message') }}
            </div>
        @endif

        <p>You are not currently logged in. Please click the button below to authenticate via Single Sign-On.</p>

        <a href="{{ route('login') }}" class="btn">Login via SSO</a>

        <div class="status">
            <strong>Current Status:</strong> Not Authenticated<br>
            <strong>Session ID:</strong> {{ session()->getId() }}<br>
            <strong>Timestamp:</strong> {{ now() }}
        </div>

        <div style="margin-top: 20px;">
            <a href="{{ route('debug.session') }}" style="color: #666; font-size: 12px;">Debug Session</a> |
            <a href="{{ route('debug.auth') }}" style="color: #666; font-size: 12px;">Debug Auth</a>
        </div>
    </div>
</body>
</html>
