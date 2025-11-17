# IAM Client Package - Quick Reference

## Installation (Already Done ✓)
```bash
composer require juniyasyos/laravel-iam-client
php artisan migrate
```

## Configuration (.env)
```env
IAM_APP_KEY=client-example
IAM_JWT_SECRET=your-secret-from-iam-server
IAM_BASE_URL=http://127.0.0.1:8000
IAM_DEFAULT_REDIRECT=/panel
```

## Routes
```php
route('iam.sso.login')     // /sso/login
route('iam.sso.callback')  // /sso/callback
```

## Usage in Controllers
```php
// Redirect to IAM login
return redirect()->route('iam.sso.login');

// With intended URL
return redirect()->route('iam.sso.login', ['intended' => '/admin']);
```

## Usage in Blade
```blade
<a href="{{ route('iam.sso.login') }}">Login via IAM</a>
```

## Check Authentication
```php
$user = auth()->user();

// Check if IAM user
if ($user->iam_id) { ... }

// Check roles
if ($user->hasRole('admin')) { ... }

// Check permissions
if ($user->can('manage_users')) { ... }

// Get access token
$token = session('iam_access_token');
```

## Testing
```bash
# Check integration status
curl http://127.0.0.1:8080/iam-test

# Test SSO redirect
curl -I http://127.0.0.1:8080/sso/login

# View logs
tail -f storage/logs/laravel.log | grep IAM
```

## Troubleshooting
```bash
# Clear caches
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# Check routes
php artisan route:list --name=iam

# Check migrations
php artisan migrate:status
```

## Token Payload Structure
```json
{
  "type": "access",
  "app_key": "client-example",
  "sub": 123,
  "name": "John Doe",
  "email": "john@example.com",
  "roles": [
    {"slug": "admin", "name": "Administrator"}
  ],
  "exp": 1234567890
}
```

## Custom Guard Integration
Package automatically uses `CustomSessionGuard` which preserves session ID during login (important for IAM).

## Documentation
- Package: `/packages/juniyasyos/laravel-iam-client/README.md`
- Setup: `/IAM-SETUP.md`
- Status: `/IAM-INTEGRATION-STATUS.md`
- Summary: `/IAM-PACKAGE-SUMMARY.md`
