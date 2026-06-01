<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AdminManagementController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware(function ($request, $next) {
                if (!Auth::guard('admin')->check() || !Auth::guard('admin')->user()->is_master) {
                    abort(403, 'Hanya Master Admin yang dapat mengakses halaman ini.');
                }
                return $next($request);
            }),
        ];
    }

    /**
     * Display the admin management page.
     */
    public function index()
    {
        $admins = Admin::orderByDesc('is_master')->orderBy('username')->get();
        return view('admin.admins', compact('admins'));
    }

    /**
     * Store a new admin (AJAX).
     * New admins are always created with is_master = false.
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:admins,username',
            'password' => 'required|string|min:6',
        ]);

        $admin = Admin::create([
            'username' => $request->username,
            'password' => $request->password, // Hashed automatically via $casts
            'is_master' => false,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Admin baru berhasil ditambahkan.',
                'admin' => $admin,
            ]);
        }

        return redirect()->route('admin.admins')->with('success', 'Admin baru berhasil ditambahkan.');
    }

    /**
     * Update an existing admin (AJAX).
     * Prevents changing is_master status (only DB/tinker can promote).
     * Prevents editing a Master Admin from another admin.
     */
    public function update(Request $request, Admin $admin)
    {
        // Prevent editing a Master Admin (unless it's yourself)
        if ($admin->is_master && $admin->id !== Auth::guard('admin')->id()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat mengedit Master Admin lain.',
                ], 403);
            }
            return redirect()->route('admin.admins')->with('error', 'Tidak dapat mengedit Master Admin lain.');
        }

        $request->validate([
            'username' => 'required|string|max:255|unique:admins,username,' . $admin->id,
        ]);

        $data = ['username' => $request->username];

        // Only update password if provided
        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:6']);
            $data['password'] = $request->password; // Hashed automatically via $casts
        }

        $admin->update($data);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Admin berhasil diperbarui.',
                'admin' => $admin->fresh(),
            ]);
        }

        return redirect()->route('admin.admins')->with('success', 'Admin berhasil diperbarui.');
    }

    /**
     * Delete an admin (AJAX).
     * Cannot delete a Master Admin or yourself.
     */
    public function destroy(Request $request, Admin $admin)
    {
        // Cannot delete a Master Admin
        if ($admin->is_master) {
            $message = 'Tidak dapat menghapus Master Admin.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }
            return redirect()->route('admin.admins')->with('error', $message);
        }

        // Cannot delete yourself
        if ($admin->id === Auth::guard('admin')->id()) {
            $message = 'Tidak dapat menghapus akun Anda sendiri.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return redirect()->route('admin.admins')->with('error', $message);
        }

        $admin->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Admin berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.admins')->with('success', 'Admin berhasil dihapus.');
    }
}
