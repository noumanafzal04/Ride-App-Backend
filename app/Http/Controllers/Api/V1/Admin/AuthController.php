<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $admin = AdminUser::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            throw ValidationException::withMessages(['email' => ['Invalid credentials.']]);
        }
        if (!$admin->isActive()) {
            throw ValidationException::withMessages(['email' => ['Your account is inactive.']]);
        }

        $admin->forceFill(['last_login_at' => now()])->save();
        $token = $admin->createToken('admin-panel')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data'    => ['token' => $token, 'admin' => $this->payload($admin)],
        ]);
    }

    public function me(Request $request)
    {
        return response()->json(['success' => true, 'data' => ['admin' => $this->payload($request->user())]]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['success' => true, 'message' => 'Logged out.']);
    }

    /** Admin profile + role + flattened permission keys for the frontend. */
    private function payload(AdminUser $admin): array
    {
        $admin->loadMissing('role.permissions');

        return [
            'id'          => $admin->id,
            'name'        => $admin->name,
            'email'       => $admin->email,
            'status'      => $admin->status,
            'role'        => $admin->role ? [
                'id'   => $admin->role->id,
                'name' => $admin->role->name,
                'slug' => $admin->role->slug,
            ] : null,
            'is_super'    => $admin->isSuper(),
            'permissions' => $admin->isSuper()
                ? Permission::pluck('key')->all()
                : $admin->permissionKeys(),
        ];
    }
}
