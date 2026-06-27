<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">

    <!-- Navbar -->
    <nav class="bg-blue-800 text-white px-4 py-3 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-3">
            <!-- Hamburger برای موبایل -->
            <button id="sidebar-toggle" class="md:hidden text-white focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <div class="font-bold text-sm md:text-lg">{{ config('app.name') }}</div>
        </div>
        <div class="flex items-center gap-2 md:gap-4">
            <span class="text-xs md:text-sm hidden sm:block">{{ Auth::user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="bg-red-500 hover:bg-red-600 px-2 md:px-3 py-1 rounded text-xs md:text-sm">خروج</button>
            </form>
        </div>
    </nav>

    <div class="flex min-h-screen relative">

        <!-- Overlay موبایل -->
        <div id="sidebar-overlay"
             class="fixed inset-0 bg-black bg-opacity-40 z-30 hidden md:hidden"
             onclick="closeSidebar()"></div>

        <!-- Sidebar -->
        <aside id="sidebar"
               class="fixed md:sticky top-0 md:top-0 right-0 h-full md:h-auto w-56 bg-white shadow-md p-4 z-40
                      transform translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out
                      shrink-0 overflow-y-auto">

            <div class="flex justify-between items-center mb-4 md:hidden">
                <span class="font-bold text-sm text-gray-700">{{ Auth::user()->name }}</span>
                <button onclick="closeSidebar()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <ul class="space-y-2">
                <li>
                    <a href="{{ route('panel.dashboard') }}"
                    class="flex items-center gap-2 px-4 py-2 rounded hover:bg-blue-50 text-sm
                            {{ request()->routeIs('panel.dashboard') ? 'bg-blue-100 text-blue-800 font-bold' : 'text-gray-700' }}">
                        📊 داشبورد
                    </a>
                </li>
                <li>
                    <a href="{{ route('panel.requests.index') }}"
                    class="flex items-center gap-2 px-4 py-2 rounded hover:bg-blue-50 text-sm
                            {{ request()->routeIs('panel.requests.index') ? 'bg-blue-100 text-blue-800 font-bold' : 'text-gray-700' }}">
                        📋 درخواست‌ها
                    </a>
                </li>                
                <li>
                    <a href="{{ route('panel.reports.index') }}"
                    class="flex items-center gap-2 px-4 py-2 rounded hover:bg-blue-50 text-sm
                            {{ request()->routeIs('panel.reports.index') ? 'bg-blue-100 text-blue-800 font-bold' : 'text-gray-700' }}">
                        📊 گزارشات
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-3 md:p-6 overflow-x-auto min-w-0">

            <!-- Breadcrumb -->
            <nav class="flex items-center gap-2 text-xs text-gray-500 mb-4">
                <a href="{{ route('panel.dashboard') }}" class="hover:text-blue-600">🏠 داشبورد</a>
                @yield('breadcrumb')
            </nav>

            @yield('content')
        </main>

    </div>

    <script>
        const sidebar       = document.getElementById('sidebar');
        const overlay       = document.getElementById('sidebar-overlay');
        const toggleBtn     = document.getElementById('sidebar-toggle');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.remove('translate-x-full');
            overlay.classList.remove('hidden');
        });

        function closeSidebar() {
            sidebar.classList.add('translate-x-full');
            overlay.classList.add('hidden');
        }
    </script>

</body>
</html>