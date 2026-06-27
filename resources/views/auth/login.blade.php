<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به پنل</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="bg-white rounded-xl shadow-md p-8 w-full max-w-md">
<h1 class="text-xl font-bold text-center text-blue-800 mb-6">
    {{ config('app.name') }}
</h1>
        <p class="text-center text-gray-500 text-sm mb-6">ورود به پنل کارشناسان</p>

        @if($errors->any())
            <div class="bg-red-50 text-red-600 text-sm p-3 rounded mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm text-gray-600 mb-1">ایمیل</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                       required>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">رمز عبور</label>
                <input type="password" name="password"
                       class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                       required>
            </div>
            <button type="submit"
                    class="w-full bg-blue-800 text-white py-2 rounded-lg hover:bg-blue-700 transition">
                ورود
            </button>
        </form>
    </div>

</body>
</html>