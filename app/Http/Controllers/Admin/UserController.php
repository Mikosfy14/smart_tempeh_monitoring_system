<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Show the user management page.
     */
    public function index()
    {
        $users = User::withCount('devices')->orderBy('created_at', 'desc')->get();
        return view('admin.users', compact('users'));
    }

    /**
     * Register a new user (AJAX).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'whatsapp_number' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'whatsapp_number' => $request->whatsapp_number,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User berhasil didaftarkan.',
                'user' => $user->loadCount('devices'),
            ]);
        }

        return redirect()->route('admin.users')->with('success', 'User berhasil didaftarkan.');
    }

    /**
     * Update user data (AJAX).
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'whatsapp_number' => 'nullable|string|max:20',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'whatsapp_number' => $request->whatsapp_number,
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User berhasil diperbarui.',
                'user' => $user->loadCount('devices'),
            ]);
        }

        return redirect()->route('admin.users')->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Delete a user (AJAX).
     */
    public function destroy(Request $request, User $user)
    {
        $user->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.users')->with('success', 'User berhasil dihapus.');
    }
}
