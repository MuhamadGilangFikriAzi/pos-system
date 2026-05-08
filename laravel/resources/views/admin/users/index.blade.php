@extends("layouts.app")
@section("title", "Manajemen User")
@section("content")
<div class="bg-white rounded-xl shadow-sm p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="font-semibold text-gray-800 text-lg">Manajemen User</h3>
        <a href="{{ route('admin.users.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">+ Tambah User</a>
    </div>

    @if(isset($outlets))
    <div class="flex gap-2 mb-4">
        @foreach($outlets as $o)
        <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs font-medium">{{ $o->name }}</span>
        @endforeach
    </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left">
                    <th class="px-3 py-2">Nama</th>
                    <th class="px-3 py-2">Email</th>
                    <th class="px-3 py-2">Role</th>
                    <th class="px-3 py-2">Outlet</th>
                    <th class="px-3 py-2">Status</th>
                    <th class="px-3 py-2">Login Terakhir</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-3 py-2 font-medium">{{ $u->name }}</td>
                    <td class="px-3 py-2 text-gray-500">{{ $u->email }}</td>
                    <td class="px-3 py-2">
                        @php
                            $rc = ['admin'=>'purple','supervisor'=>'amber','kasir'=>'blue'];
                            $c = $rc[$u->role] ?? 'gray';
                        @endphp
                        <span class="bg-{{$c}}-100 text-{{$c}}-700 px-2 py-0.5 rounded-full text-xs font-medium">{{ ucfirst($u->role) }}</span>
                    </td>
                    <td class="px-3 py-2 text-xs">{{ $u->outlet->name ?? 'Pusat' }}</td>
                    <td class="px-3 py-2">
                        @if($u->is_active)
                        <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs font-medium">Aktif</span>
                        @else
                        <span class="bg-red-100 text-red-500 px-2 py-0.5 rounded-full text-xs font-medium">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-3 py-2 text-xs text-gray-400">{{ $u->last_login_at ? $u->last_login_at->diffForHumans() : '-' }}</td>
                    <td class="px-3 py-2">
                        <div class="flex gap-2">
                            <a href="{{ route('admin.users.edit', $u) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Edit</a>
                            @if($u->id !== Auth::id())
                            <form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('Hapus user {{ $u->name }}?')">
                                @csrf @method('DELETE')
                                <button class="text-red-500 hover:text-red-700 text-xs font-medium">Hapus</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-8 text-gray-400">Belum ada user</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
</div>
@endsection
