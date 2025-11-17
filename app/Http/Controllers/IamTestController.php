<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Spatie\Permission\Models\Role;

class IamTestController extends Controller
{
    /**
     * Display IAM integration status and test page.
     */
    public function index()
    {
        $status = [
            'package' => [
                'installed' => class_exists('Juniyasyos\IamClient\IamClientServiceProvider'),
                'version' => 'dev-master',
            ],
            'config' => [
                'app_key' => config('iam.app_key'),
                'base_url' => config('iam.base_url'),
                'guard' => config('iam.guard'),
                'user_model' => config('iam.user_model'),
            ],
            'routes' => [
                'login' => route('iam.sso.login'),
                'callback' => route('iam.sso.callback'),
            ],
            'database' => [
                'users_table_has_iam_id' => $this->checkColumnExists('users', 'iam_id'),
                'users_table_has_active' => $this->checkColumnExists('users', 'active'),
                'permission_tables' => $this->checkPermissionTables(),
            ],
            'authentication' => [
                'is_authenticated' => Auth::check(),
                'current_user' => Auth::user(),
                'guard_class' => get_class(Auth::guard()),
            ],
            'roles' => [
                'available_roles' => Role::all()->pluck('name'),
                'user_can_have_roles' => method_exists(User::class, 'hasRole'),
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'IAM Client Package Integration Status',
            'data' => $status,
        ], 200);
    }

    /**
     * Test SSO login redirect.
     */
    public function testLogin(Request $request)
    {
        $intendedUrl = $request->input('intended', '/panel');

        return redirect()->route('iam.sso.login', [
            'intended' => $intendedUrl
        ]);
    }

    /**
     * Check if a database column exists.
     */
    private function checkColumnExists(string $table, string $column): bool
    {
        try {
            return \Schema::hasColumn($table, $column);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if Spatie Permission tables exist.
     */
    private function checkPermissionTables(): array
    {
        $tables = ['roles', 'permissions', 'model_has_roles', 'model_has_permissions', 'role_has_permissions'];
        $status = [];

        foreach ($tables as $table) {
            try {
                $status[$table] = \Schema::hasTable($table);
            } catch (\Exception $e) {
                $status[$table] = false;
            }
        }

        return $status;
    }
}
