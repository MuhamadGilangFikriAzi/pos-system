<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield("title", "POS System")</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>.loading-bar{position:fixed;top:0;left:0;width:100%;height:3px;background:linear-gradient(90deg,#6366f1,#8b5cf6);z-index:9999;display:none}</style>
</head>
<body class="bg-gray-50">
    <div x-data="{ sidebar: true, loading: false }" class="min-h-screen flex">
        <!-- Sidebar -->
        <div x-show="sidebar" class="w-64 bg-gradient-to-b from-indigo-900 to-slate-900 text-white min-h-screen flex-shrink-0">
            <div class="p-6 border-b border-white/20">
                <h1 class="text-xl font-bold">🧾 POS System</h1>
                <p class="text-xs text-gray-400 mt-1">Point of Sale</p>
            </div>
            <nav class="p-4 space-y-1">
                <a href="{{ route("pos.dashboard") }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition @if(request()->routeIs("pos.dashboard")) bg-white/20 @endif">
                    <span>📊</span> Dashboard
                </a>
                <a href="{{ route("pos.index") }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition @if(request()->routeIs("pos.index")) bg-white/20 @endif">
                    <span>🧾</span> POS Kasir
                </a>
                <a href="{{ route("products.index") }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition @if(request()->routeIs("products.*")) bg-white/20 @endif">
                    <span>📦</span> Produk
                </a>
                <a href="{{ route("categories.index") }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition @if(request()->routeIs("categories.*")) bg-white/20 @endif">
                    <span>📁</span> Kategori
                </a>
                <a href="{{ route("reports.index") }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition @if(request()->routeIs("reports.index")) bg-white/20 @endif">
                    <span>📈</span> Laporan
                </a>
                <hr class="border-white/20 my-4">
                <form method="POST" action="{{ route("logout") }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-red-500/20 transition w-full text-left text-red-300">
                        <span>🚪</span> Logout
                    </button>
                </form>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-h-screen">
            <header class="bg-white shadow-sm px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button @click="sidebar = !sidebar" class="text-gray-500 hover:text-gray-700">☰</button>
                    <h2 class="text-lg font-semibold text-gray-800">@yield("title")</h2>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-500">{{ Auth::user()->name }}</span>
                </div>
            </header>
            <main class="flex-1 p-6">
                @if(session("success"))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">{{ session("success") }}</div>
                @endif
                @if(session("error"))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">{{ session("error") }}</div>
                @endif
                @yield("content")
            </main>
        </div>

        <!-- Loading Bar -->
        <div class="loading-bar" x-show="loading" x-transition></div>
    </div>

    <script>
        document.addEventListener("alpine:init", () => {
            Alpine.data("layoutApp", () => ({
                init() {
                    document.querySelectorAll("a:not([target=_blank])").forEach(a => {
                        a.addEventListener("click", () => {
                            if (a.getAttribute("href") !== "#") this.loading = true;
                        });
                    });
                }
            }));
        });
    </script>
</body>
</html>