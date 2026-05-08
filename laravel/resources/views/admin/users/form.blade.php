@extends("layouts.app")
@section("title", isset($user) ? "Edit User" : "Tambah User")
@section("content")
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 text-lg mb-6">{{ isset($user) ? '✏️ Edit User' : '👤 Tambah User Baru' }}</h3>

        <form method="POST" action="{{ isset($user) ? route('admin.users.update', $user) : route('admin.users.store') }}" class="space-y-5">
            @csrf
            @if(isset($user)) @method('PUT') @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required
                       class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-indigo-500 outline-none transition text-sm">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required
                       class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-indigo-500 outline-none transition text-sm">
                @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password {{ isset($user) ? '(kosongkan jika tidak diubah)' : '' }}</label>
                <input type="password" name="password" {{ isset($user) ? '' : 'required' }}
                       class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-indigo-500 outline-none transition text-sm">
                @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select name="role" required class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-indigo-500 outline-none transition text-sm">
                    <option value="kasir" {{ old('role', $user->role ?? '') == 'kasir' ? 'selected' : '' }}>Kasir</option>
                    <option value="supervisor" {{ old('role', $user->role ?? '') == 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                    <option value="admin" {{ old('role', $user->role ?? '') == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                @error('role')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Outlet</label>
                <select name="outlet_id" class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-indigo-500 outline-none transition text-sm">
                    @foreach($outlets as $o)
                    <option value="{{ $o->id }}" {{ old('outlet_id', $user->outlet_id ?? 1) == $o->id ? 'selected' : '' }}>{{ $o->name }} ({{ $o->code }})</option>
                    @endforeach
                </select>
            </div>

            @if(isset($user) && $user->id !== Auth::id())
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 border-gray-300 rounded">
                <label for="is_active" class="text-sm text-gray-700">Akun Aktif</label>
            </div>
            @endif

            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-lg transition">
                    {{ isset($user) ? 'Simpan Perubahan' : 'Tambah User' }}
                </button>
                <a href="{{ route('admin.users.index') }}" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition text-sm font-medium">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
