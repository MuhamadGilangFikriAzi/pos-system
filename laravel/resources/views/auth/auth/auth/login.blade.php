<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - POS System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h1 class="text-2xl font-bold text-center mb-6">?? POS System</h1>
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded px-3 py-2 @error('email') border-red-500 @enderror" required autofocus>
                @error('email')<p class="text-red-500 text-sm mt-1">{{  }}</p>@enderror
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Password</label>
                <input type="password" name="password" class="w-full border rounded px-3 py-2 @error('password') border-red-500 @enderror" required>
                @error('password')<p class="text-red-500 text-sm mt-1">{{  }}</p>@enderror
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700">Login</button>
        </form>
        <p class="text-xs text-gray-400 text-center mt-4">Demo: admin@pos.com / admin123</p>
    </div>
</body>
</html>
