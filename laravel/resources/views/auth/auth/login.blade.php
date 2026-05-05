<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - POS System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-indigo-900 to-slate-900 min-h-screen flex items-center justify-center">
    <div class="bg-white/10 backdrop-blur-lg p-8 rounded-2xl shadow-2xl w-full max-w-md border border-white/20">
        <div class="text-center mb-8">
            <div class="text-4xl mb-2">??</div>
            <h1 class="text-2xl font-bold text-white">POS System</h1>
            <p class="text-gray-400 text-sm">Point of Sale</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-300 mb-2 text-sm font-medium">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full bg-white/20 border border-white/30 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-500 @enderror" placeholder="admin@pos.com" required autofocus>
                @error('email')<p class="text-red-400 text-sm mt-1">{{  }}</p>@enderror
            </div>
            <div class="mb-6">
                <label class="block text-gray-300 mb-2 text-sm font-medium">Password</label>
                <input type="password" name="password" class="w-full bg-white/20 border border-white/30 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('password') border-red-500 @enderror" placeholder="admin123" required>
                @error('password')<p class="text-red-400 text-sm mt-1">{{  }}</p>@enderror
            </div>
            <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-3 rounded-lg font-bold hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-lg">Login</button>
        </form>
        <p class="text-xs text-gray-500 text-center mt-6">Demo: admin@pos.com / admin123</p>
    </div>
</body>
</html>