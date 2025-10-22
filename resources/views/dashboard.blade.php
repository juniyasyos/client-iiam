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
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        h1 { color: #333; margin-bottom: 30px; text-align: center; }
        .nav {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .user-badge {
            background: #007bff;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
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
        .btn-logout { background: #dc3545; }
        .btn-logout:hover { background: #c82333; }
        .btn-home { background: #6c757d; }
        .btn-home:hover { background: #5a6268; }
        .dashboard-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            border-left: 4px solid #007bff;
        }
        .card h3 { margin-top: 0; color: #333; }
        .card p { color: #666; margin-bottom: 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav">
            <div>
                <a href="{{ route('home') }}" class="btn btn-home">← Home</a>
            </div>
            <div class="user-badge">
                {{ Auth::user()->name }}
            </div>
            <div>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-logout">Logout</button>
                </form>
            </div>
        </div>

        <h1>User Dashboard</h1>

        <div class="dashboard-content">
            <div class="card">
                <h3>Profile Information</h3>
                <p><strong>Name:</strong> {{ Auth::user()->name }}</p>
                <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
                <p><strong>User ID:</strong> {{ Auth::user()->id }}</p>
            </div>

            @if(session('iam'))
            <div class="card">
                <h3>SSO Information</h3>
                <p><strong>Subject:</strong> {{ session('iam.sub') }}</p>
                <p><strong>Application:</strong> {{ session('iam.app') }}</p>
                <p><strong>Roles:</strong> {{ implode(', ', session('iam.roles', [])) ?: 'None' }}</p>
                <p><strong>Permissions:</strong> {{ implode(', ', session('iam.perms', [])) ?: 'None' }}</p>
            </div>
            @endif

            <div class="card">
                <h3>Session Details</h3>
                <p><strong>Session ID:</strong> {{ session()->getId() }}</p>
                <p><strong>Authenticated:</strong> {{ Auth::check() ? 'Yes ✅' : 'No ❌' }}</p>
                <p><strong>Login Time:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>
            </div>

            <div class="card">
                <h3>Debug Tools</h3>
                <p>
                    <a href="{{ route('debug.session') }}" target="_blank" style="color: #007bff;">Session Debug</a><br>
                    <a href="{{ route('debug.auth') }}" target="_blank" style="color: #007bff;">Auth Debug</a><br>
                    <a href="{{ route('status') }}" target="_blank" style="color: #007bff;">Auth Status</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
