@extends("layouts.app")
@section("title", "Log Aktivitas")
@section("content")
<div class="bg-white rounded-xl shadow-sm p-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="font-semibold text-gray-800 text-lg">Log Aktivitas</h3>
        <form method="GET" class="flex gap-2">
            <select name="action" class="border rounded-lg px-3 py-2 text-sm" onchange="this.form.submit()">
                <option value="">Semua Aksi</option>
                @foreach($actions as $a)
                <option value="{{ $a }}" {{ request('action') == $a ? 'selected' : '' }}>{{ $a }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="space-y-1 max-h-[70vh] overflow-y-auto">
        @forelse($logs as $log)
        <div class="flex items-start gap-3 p-3 border-b border-gray-50 hover:bg-gray-50 rounded-lg transition">
            <div class="text-xl mt-0.5">
                @switch($log->action)
                    @case('login') 🔓 @break
                    @case('logout') 🔒 @break
                    @case('auto_logout') ⚠️ @break
                    @case('open_shift') 🟢 @break
                    @case('close_shift') 🔴 @break
                    @case('create_transaction') 🧾 @break
                    @case('void_transaction') ❌ @break
                    @case('create_user') 👤 @break
                    @case('update_user') ✏️ @break
                    @case('delete_user') 🗑️ @break
                    @default 📝
                @endswitch
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-800">{{ $log->description ?? $log->action }}</p>
                <div class="flex gap-3 text-xs text-gray-400 mt-1">
                    <span>{{ $log->user->name ?? '-' }}</span>
                    <span>{{ $log->created_at->diffForHumans() }}</span>
                    @if($log->ip_address)
                    <span>IP: {{ $log->ip_address }}</span>
                    @endif
                </div>
            </div>
            <div class="text-xs text-gray-400 whitespace-nowrap">{{ $log->created_at->format('H:i:s') }}</div>
        </div>
        @empty
        <div class="text-center py-10 text-gray-400">Belum ada aktivitas</div>
        @endforelse
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>
</div>
@endsection
