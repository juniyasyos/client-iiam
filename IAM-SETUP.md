# Laravel IAM Client - Setup Guide

## Package Installed Successfully! ✅

Package `juniyasyos/laravel-iam-client` telah diinstall dan siap digunakan.

## Setup Steps

### 1. Environment Configuration

Tambahkan konfigurasi IAM di file `.env`:

```env
# IAM Configuration
IAM_APP_KEY=siimut
IAM_JWT_SECRET=your-secret-key-from-iam-server
IAM_BASE_URL=https://iam.example.com
IAM_LOGIN_ROUTE=/sso/login
IAM_CALLBACK_ROUTE=/sso/callback
IAM_DEFAULT_REDIRECT=/panel
IAM_GUARD=web
IAM_USER_MODEL=App\Models\User
IAM_ROLE_GUARD_NAME=web
IAM_STORE_TOKEN_IN_SESSION=true
```

### 2. Database Migrations ✅

Migration sudah dijalankan:
- ✅ Kolom `iam_id` dan `active` sudah ditambahkan ke tabel `users`
- ✅ Spatie Permission tables sudah dibuat

### 3. User Model ✅

Model `User` sudah diupdate dengan:
- ✅ `HasRoles` trait dari Spatie Permission
- ✅ Kolom `iam_id` dan `active` di fillable
- ✅ Cast `active` ke boolean

### 4. Routes Available

Package otomatis register routes:

- **Login**: `/sso/login` (route: `iam.sso.login`)
  - Redirect user ke IAM login page
  
- **Callback**: `/sso/callback` (route: `iam.sso.callback`)
  - Handle callback dari IAM setelah login berhasil

### 5. Test SSO Flow

#### A. Manual Testing

Akses route SSO login:
```
http://localhost:8000/sso/login
```

#### B. Integration in Application

Redirect ke SSO login dari aplikasi:

```php
// Dari controller
return redirect()->route('iam.sso.login');

// Dengan intended URL
return redirect()->route('iam.sso.login', ['intended' => '/admin/dashboard']);
```

#### C. Dalam Blade View

```blade
<a href="{{ route('iam.sso.login') }}" class="btn btn-primary">
    Login via IAM
</a>
```

### 6. Check Authenticated User

Setelah login via SSO:

```php
// Get current user
$user = Auth::user();

// Check roles (via Spatie Permission)
if ($user->hasRole('admin')) {
    // User is admin
}

// Check permissions
if ($user->can('view_dashboard')) {
    // User has permission
}

// Get all roles
$roles = $user->getRoleNames();

// Get access token (if stored in session)
$accessToken = session('iam_access_token');
```

## Integration Examples

### Filament Admin Panel

#### Option 1: Custom Login Page

Buat custom login page yang redirect ke IAM:

```php
// app/Filament/Pages/Auth/Login.php
namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    public function mount(): void
    {
        // Redirect directly to IAM login
        redirect()->route('iam.sso.login', ['intended' => '/panel'])->send();
    }
}
```

Register di Panel Provider:

```php
use App\Filament\Pages\Auth\Login;

public function panel(Panel $panel): Panel
{
    return $panel
        ->login(Login::class)
        // ... other config
}
```

#### Option 2: Middleware Redirect

Buat middleware untuk auto-redirect non-authenticated users:

```php
// app/Http/Middleware/RedirectToSsoIfNotAuthenticated.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectToSsoIfNotAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() && $request->is('panel*')) {
            return redirect()->route('iam.sso.login', [
                'intended' => $request->fullUrl()
            ]);
        }

        return $next($request);
    }
}
```

### Laravel Breeze/Jetstream

Replace login form dengan redirect button:

```blade
<!-- resources/views/auth/login.blade.php -->
<x-guest-layout>
    <div class="text-center">
        <h2 class="text-2xl font-bold mb-4">Login</h2>
        <p class="mb-6">Please login using IAM authentication</p>
        
        <a href="{{ route('iam.sso.login') }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-white">
            Login via IAM
        </a>
    </div>
</x-guest-layout>
```

## Troubleshooting

### 1. Check Package Installation

```bash
php artisan package:discover
php artisan route:list | grep iam
```

### 2. View Logs

```bash
tail -f storage/logs/laravel.log
```

### 3. Common Issues

#### Token Validation Error
- Pastikan `IAM_JWT_SECRET` sama dengan IAM server
- Check token belum expired

#### User Not Created
- Check migration sudah dijalankan
- Lihat logs untuk error details

#### Roles Not Synced
- Pastikan Spatie Permission migrations sudah dijalankan
- Check format roles di token payload

## Next Steps

1. ✅ Update `.env` dengan konfigurasi IAM yang benar
2. ✅ Test SSO flow dengan mengakses `/sso/login`
3. ✅ Integrate dengan Filament atau aplikasi Anda
4. ✅ Test role-based access control

## Documentation

Dokumentasi lengkap package tersedia di:
`/packages/juniyasyos/laravel-iam-client/README.md`

## Support

Jika ada pertanyaan atau issue, silakan check:
- Package README.md
- Laravel logs (`storage/logs/laravel.log`)
- IAM server documentation
