<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Embroidery Converter') }} - @yield('title', 'Dashboard')</title>
    <meta name="description" content="Convert embroidery files online - PES, DST, JEF, VP3 and more.">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <!-- Tailwind CSS via CDN (production: use Vite/npm build) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { 50:'#eef2ff',100:'#e0e7ff',200:'#c7d2fe',300:'#a5b4fc',400:'#818cf8',500:'#6366f1',600:'#4f46e5',700:'#4338ca',800:'#3730a3',900:'#312e81' }
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @yield('head')
</head>
<body class="h-full font-sans antialiased">
<div class="min-h-full">
    <!-- Sidebar + Main layout -->
    <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden bg-gray-100">

        <!-- Mobile sidebar backdrop -->
        <div x-show="sidebarOpen" x-cloak
             @click="sidebarOpen = false"
             class="fixed inset-0 z-20 bg-black bg-opacity-50 lg:hidden"></div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-30 w-64 bg-primary-700 text-white flex flex-col transition-transform duration-300 lg:relative lg:translate-x-0">
            <!-- Logo -->
            <div class="flex items-center h-16 px-6 bg-primary-800">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <svg class="w-8 h-8 text-primary-200" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/>
                    </svg>
                    <span class="text-lg font-bold text-white">EmbroideryConv</span>
                </a>
            </div>

            <!-- Nav -->
            <nav class="flex-1 py-4 overflow-y-auto">
                <div class="px-3 space-y-1">
                    @php $navItems = [
                        ['route' => 'dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'label' => 'Dashboard'],
                        ['route' => 'files.upload', 'icon' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12', 'label' => 'Upload Files'],
                        ['route' => 'files.index', 'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z', 'label' => 'My Files'],
                        ['route' => 'conversions.create', 'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4', 'label' => 'Convert'],
                        ['route' => 'conversions.index', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'label' => 'History'],
                        ['route' => 'plans.index', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', 'label' => 'Upgrade Plan'],
                    ] @endphp

                    @foreach($navItems as $item)
                        <a href="{{ route($item['route']) }}"
                           class="group flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs($item['route']) ? 'bg-primary-600 text-white' : 'text-primary-200 hover:bg-primary-600 hover:text-white' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                            </svg>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>

                @if(auth()->user()?->isAdmin())
                    <div class="mt-6 px-3">
                        <p class="px-3 text-xs font-semibold text-primary-400 uppercase tracking-wider">Admin</p>
                        <div class="mt-2 space-y-1">
                            <a href="{{ route('admin.dashboard') }}"
                               class="group flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-primary-200 hover:bg-primary-600 hover:text-white transition-colors {{ request()->routeIs('admin.*') ? 'bg-primary-600 text-white' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                Admin Panel
                            </a>
                        </div>
                    </div>
                @endif
            </nav>

            <!-- User menu -->
            <div class="p-4 border-t border-primary-600">
                <div class="flex items-center gap-3">
                    <img src="{{ auth()->user()?->avatar_url }}" alt="Avatar" class="w-9 h-9 rounded-full">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ auth()->user()?->name }}</p>
                        <p class="text-xs text-primary-300 truncate">{{ auth()->user()?->activePlan()?->name ?? 'Free' }} plan</p>
                    </div>
                </div>
                <div class="mt-3 flex gap-2">
                    <a href="{{ route('profile.edit') }}" class="flex-1 text-center text-xs text-primary-300 hover:text-white py-1">Settings</a>
                    <form method="POST" action="{{ route('logout') }}" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full text-center text-xs text-primary-300 hover:text-white py-1">Logout</button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main content area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top bar -->
            <header class="bg-white shadow-sm h-16 flex items-center justify-between px-4 lg:px-6">
                <!-- Mobile menu toggle -->
                <button @click="sidebarOpen = !sidebarOpen"
                        class="lg:hidden text-gray-500 hover:text-gray-700 p-2 rounded-md">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <div class="flex items-center gap-4 ml-auto">
                    <!-- Notifications bell -->
                    @php $unreadCount = auth()->user()?->unreadNotifications()->count() ?? 0; @endphp
                    <a href="{{ route('notifications.index') }}" class="relative p-2 text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        @if($unreadCount > 0)
                            <span class="absolute top-1 right-1 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">{{ $unreadCount }}</span>
                        @endif
                    </a>

                    <!-- User avatar -->
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2">
                        <img src="{{ auth()->user()?->avatar_url }}" alt="Avatar" class="w-8 h-8 rounded-full">
                        <span class="hidden md:block text-sm font-medium text-gray-700">{{ auth()->user()?->name }}</span>
                    </a>
                </div>
            </header>

            <!-- Flash messages -->
            <div class="px-4 lg:px-6 pt-4 space-y-2">
                @if(session('success'))
                    <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3">
                        <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <p class="text-sm">{{ session('success') }}</p>
                    </div>
                @endif
                @if(session('error') || $errors->any())
                    <div class="flex items-start gap-2 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        <div class="text-sm">
                            @if(session('error')){{ session('error') }}@endif
                            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                        </div>
                    </div>
                @endif
                @if(session('warning') || session('warnings'))
                    <div class="flex items-start gap-2 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg px-4 py-3">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        <div class="text-sm">
                            @if(session('warning')){{ session('warning') }}@endif
                            @if(session('warnings'))@foreach(session('warnings') as $w)<p>{{ $w }}</p>@endforeach@endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto px-4 lg:px-6 py-4">
                @yield('content')
            </main>
        </div>
    </div>
</div>

<!-- Alpine.js for interactive components -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@yield('scripts')
</body>
</html>
