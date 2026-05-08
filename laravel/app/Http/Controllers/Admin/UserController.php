<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Outlet;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('outlet')
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(20);

        $outlets = Outlet::where('is_active', true)->get();

        return view('admin.users.index', compact('users', 'outlets'));
    }

    public function create()
    {
        $outlets = Outlet::where('is_active', true)->get();
        return view('admin.users.form', compact('outlets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,supervisor,kasir',
            'outlet_id' => 'nullable|exists:outlets,id',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'outlet_id' => $validated['outlet_id'] ?? 1,
            'is_active' => $request->boolean('is_active', true),
        ]);

        ActivityLog::log(
            'create_user',
            "Membuat user {$user->name} ({$user->email}) role {$user->role}",
            $user,
            ['role' => $user->role]
        );

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} berhasil ditambahkan.");
    }

    public function edit(User $user)
    {
        $outlets = Outlet::where('is_active', true)->get();
        return view('admin.users.form', compact('user', 'outlets'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6',
            'role' => 'required|in:admin,supervisor,kasir',
            'outlet_id' => 'nullable|exists:outlets,id',
            'is_active' => 'boolean',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'outlet_id' => $validated['outlet_id'] ?? $user->outlet_id,
            'is_active' => $request->boolean('is_active', $user->is_active),
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        ActivityLog::log(
            'update_user',
            "Update user {$user->name} ({$user->email}) role {$user->role}",
            $user,
            ['changes' => array_keys($data)]
        );

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} berhasil diperbarui.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        $name = $user->name;
        $user->delete();

        ActivityLog::log('delete_user', "Menghapus user {$name}");

        return redirect()->route('admin.users.index')
            ->with('success', "User {$name} berhasil dihapus.");
    }
}
