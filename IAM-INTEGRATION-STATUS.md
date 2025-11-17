# IAM Client Integration - Test & Verification

## ✅ Package Installation Status

### Dependencies Installed
- ✅ `juniyasyos/laravel-iam-client` - Local package
- ✅ `firebase/php-jwt` ^6.0 - JWT token handling
- ✅ `spatie/laravel-permission` ^6.0 - Role management

### Database Migrations
```bash
✅ 2024_01_01_000000_add_iam_columns_to_users_table
✅ 2025_11_17_061911_create_permission_tables
```

### Routes Registered
```
✅ GET  /sso/login     → iam.sso.login (SsoLoginRedirectController)
✅ GET|POST /sso/callback → iam.sso.callback (SsoCallbackController)
```

### Configuration
```env
✅ IAM_APP_KEY=client-example
✅ IAM_JWT_SECRET=change-me-to-match-iam-server-secret
✅ IAM_BASE_URL=http://127.0.0.1:8000
✅ IAM_GUARD=web (using CustomSessionGuard)
```

## 🔧 Compatibility Features

### Custom Session Guard Integration
Package IAM client sudah diintegrasikan dengan `CustomSessionGuard`:

**Benefit**: Session ID **tidak di-regenerate** saat login, penting untuk IAM flow yang mengandalkan session consistency.

```php
// IamUserProvisioner.php
if ($guard instanceof CustomSessionGuard) {
    $guard->loginWithoutRegeneration($user);  // ✅ Preserve session ID
} else {
    Auth::guard($guard)->login($user);         // Fallback standard
}
```

### User Model Ready
```php
// app/Models/User.php
✅ use HasRoles;                  // Spatie Permission
✅ 'iam_id' in fillable          // IAM user identifier
✅ 'active' in fillable          // User status
✅ 'active' => 'boolean' cast    // Type casting
```

## 🧪 Testing the Integration

### 1. Manual Route Test

Test redirect ke IAM:
```bash
curl -I http://127.0.0.1:8080/sso/login
```

Expected: HTTP 302 redirect to IAM server

### 2. Check Config Loading

```bash
php artisan tinker
```

```php
config('iam.app_key');        // Should return: client-example
config('iam.base_url');       // Should return: http://127.0.0.1:8000
config('iam.guard');          // Should return: web
config('iam.user_model');     // Should return: App\Models\User
```

### 3. Test User Model with Roles

```bash
php artisan tinker
```

```php
$user = App\Models\User::first();
$user->assignRole('admin');
$user->hasRole('admin');  // Should return true
$user->getRoleNames();    // Should return collection with 'admin'
```

### 4. Full SSO Flow Test

**Prerequisites:**
- IAM server running on `http://127.0.0.1:8000`
- User created in IAM server
- JWT secret matched between servers

**Steps:**

1. Access login route:
   ```
   http://127.0.0.1:8080/sso/login
   ```

2. You'll be redirected to IAM server login page

3. Login with IAM credentials

4. IAM will redirect back to callback with access_token

5. User will be created/updated locally and logged in

6. Check logs:
   ```bash
   tail -f storage/logs/laravel.log | grep IAM
   ```

   Expected log entries:
   ```
   [INFO] IAM user provisioning started
   [INFO] IAM user provisioned
   [INFO] Syncing roles for user
   [INFO] Roles synced successfully
   [INFO] IAM user logged in via CustomSessionGuard
   ```

## 📋 Integration Checklist

### Backend ✅
- [x] Package installed via composer
- [x] Migrations run successfully
- [x] User model configured with HasRoles trait
- [x] Config values set in .env
- [x] Routes registered and accessible
- [x] CustomSessionGuard integration
- [x] Service provider auto-discovered

### Configuration ⚠️
- [ ] Update `IAM_JWT_SECRET` with actual secret from IAM server
- [ ] Verify `IAM_BASE_URL` points to correct IAM server
- [ ] Test IAM server connectivity

### Frontend (Optional)
- [ ] Add "Login via IAM" button in login page
- [ ] Redirect unauthorized users to /sso/login
- [ ] Display user roles in UI
- [ ] Handle SSO errors gracefully

## 🚀 Usage Examples

### Redirect to SSO Login

**In Controller:**
```php
public function showLogin()
{
    return redirect()->route('iam.sso.login');
}
```

**With Intended URL:**
```php
return redirect()->route('iam.sso.login', [
    'intended' => '/admin/dashboard'
]);
```

**In Blade:**
```blade
<a href="{{ route('iam.sso.login') }}" class="btn btn-primary">
    Login via IAM
</a>
```

### Check User After Login

```php
// Get authenticated user
$user = auth()->user();

// Check if user came from IAM
if ($user->iam_id) {
    // User authenticated via IAM
}

// Check roles
if ($user->hasRole('admin')) {
    // User is admin
}

// Check permissions
if ($user->can('manage_users')) {
    // User has permission
}

// Get access token
$token = session('iam_access_token');
```

### Middleware Protection

Create middleware to force SSO login:

```php
// app/Http/Middleware/RequireIamAuth.php
public function handle(Request $request, Closure $next)
{
    if (!auth()->check()) {
        return redirect()->route('iam.sso.login', [
            'intended' => $request->fullUrl()
        ]);
    }
    
    return $next($request);
}
```

Register in `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'iam.auth' => \App\Http\Middleware\RequireIamAuth::class,
    ]);
})
```

Use in routes:
```php
Route::middleware('iam.auth')->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

## 🐛 Troubleshooting

### Issue: Routes not found
```bash
php artisan route:clear
php artisan config:clear
php artisan optimize:clear
```

### Issue: User not created
Check logs:
```bash
tail -f storage/logs/laravel.log
```

Common causes:
- JWT secret mismatch
- Token expired
- Invalid app_key in token
- Missing iam_id in token payload

### Issue: Roles not syncing
Verify Spatie Permission installed:
```bash
php artisan migrate:status | grep permission
```

Check token payload structure:
```json
{
  "roles": [
    {"slug": "admin", "name": "Administrator"}
  ]
}
```

### Issue: Session lost after login
✅ Already fixed! Package uses `loginWithoutRegeneration()` to preserve session ID.

## 📊 Compatibility Matrix

| Component | Version | Status |
|-----------|---------|--------|
| Laravel | 12.38.1 | ✅ Compatible |
| PHP | 8.4.11 | ✅ Compatible |
| Spatie Permission | 6.23.0 | ✅ Installed |
| Firebase JWT | 6.11.1 | ✅ Installed |
| Custom Session Guard | ✓ | ✅ Integrated |

## ✅ Package Ready to Use!

The IAM client package is now fully integrated and compatible with your Laravel application. 

**Next Steps:**
1. Update `IAM_JWT_SECRET` in `.env` with actual secret
2. Test SSO flow with IAM server
3. Implement frontend login UI
4. Configure role-based access control

For detailed documentation, see:
- `/packages/juniyasyos/laravel-iam-client/README.md`
- `/IAM-SETUP.md`
