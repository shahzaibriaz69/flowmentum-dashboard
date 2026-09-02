<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Flowmentum Path - {{ $title ?? 'Dashboard' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { colors: { darkBg: '#080B11' } } }
        };
        if (localStorage.getItem('theme') === 'light' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: light)').matches)) {
            document.documentElement.classList.remove('dark');
        } else {
            document.documentElement.classList.add('dark');
        }
    </script>
    <style>body { font-family: Inter, system-ui, sans-serif; }</style>
</head>
<body class="min-h-screen bg-slate-100 dark:bg-darkBg text-slate-800 dark:text-slate-200 antialiased transition-colors duration-200">
    <header class="h-[72px] shrink-0 overflow-hidden bg-white dark:bg-[#0b0f19] border-b border-slate-200 dark:border-[#131B2E] px-6 flex items-center text-sm sticky top-0 z-50">
        <div class="flex items-center gap-4 min-w-0">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-bold text-slate-900 dark:text-white text-lg whitespace-nowrap shrink-0">
                <span class="w-8 h-8 rounded-xl bg-[#3d7fe9] flex items-center justify-center text-sm text-[#071427] shadow-sm shadow-blue-500/20"><i class="fa-solid fa-bolt"></i></span>
                <span>Flowmentum Path</span>
            </a>
            <nav class="hidden xl:flex items-center gap-1 whitespace-nowrap">
                @foreach(['dashboard' => ['Dashboard', 'fa-border-all'], 'people' => ['People', 'fa-users'], 'inbox' => ['Inbox', 'fa-comment-dots'], 'pipeline' => ['Pipeline', 'fa-bullseye'], 'marketing' => ['Marketing', 'fa-bullhorn'], 'automations' => ['Automations', 'fa-bolt'], 'sites' => ['Sites', 'fa-globe']] as $key => [$label, $icon])
                    @php($active = request()->routeIs($key))
                    <a href="{{ $key === 'dashboard' ? route('dashboard') : route('workspace', $key) }}" class="px-3 py-2 rounded-xl flex items-center gap-2 text-sm font-medium transition-colors {{ $active ? 'bg-blue-50 dark:bg-[#16233b] text-blue-600 dark:text-[#5b9bf6]' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.03]' }}">
                        <i class="fa-solid {{ $icon }}"></i>{{ $label }}
                    </a>
                @endforeach
            </nav>
        </div>
        <div class="flex items-center gap-4 shrink-0 ml-auto">
            <div class="h-9 bg-slate-50 dark:bg-[#0b101b] border border-slate-200 dark:border-[#1E293B] text-slate-700 dark:text-slate-200 text-sm px-3 rounded-xl flex items-center gap-2"><i class="fa-solid fa-location-dot text-blue-500"></i>Flowmentum HQ<i class="fa-solid fa-chevron-down text-[10px] text-slate-400 ml-1"></i></div>
            <div class="relative hidden sm:block"><i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 text-xs"></i><input placeholder="Search..." class="h-9 w-56 bg-slate-100 dark:bg-[#161d2c] border border-transparent text-sm text-slate-800 dark:text-slate-200 placeholder:text-slate-400 pl-8 pr-4 rounded-xl outline-none focus:border-blue-500/60"></div>
            <div class="flex items-center gap-1 text-slate-500 dark:text-slate-400 text-sm"><button class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-white/[0.04]"><i class="fa-solid fa-phone"></i></button><button class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-white/[0.04]"><i class="fa-solid fa-comments"></i></button><button class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-white/[0.04]"><i class="fa-solid fa-envelope"></i></button><button class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-white/[0.04]"><i class="fa-solid fa-calendar"></i></button></div>
            <div class="h-6 border-l border-slate-200 dark:border-[#1E293B] mx-1"></div>
            <button class="h-9 bg-[#3d7fe9] hover:bg-[#5b9bf6] text-white dark:text-[#06101f] text-sm font-medium px-4 rounded-xl transition-colors flex items-center gap-2"><i class="fa-solid fa-plus"></i>Create</button>
            <div class="flex items-center gap-1 text-slate-500 dark:text-slate-400 text-sm"><button class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-white/[0.04]"><i class="fa-solid fa-bell"></i></button><button class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-white/[0.04]"><i class="fa-solid fa-gear"></i></button><button id="themeToggle" class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-white/[0.04]"><i id="themeIcon" class="fa-solid fa-moon"></i></button><button class="w-8 h-8 rounded-lg hover:bg-red-500/20 hover:text-red-500"><i class="fa-solid fa-trash-can"></i></button><img src="https://i.pravatar.cc/100?img=12" alt="Avatar" class="w-8 h-8 rounded-full border border-blue-500/80 object-cover ml-1"></div>
        </div>
    </header>
    <main class="flex-1 w-full max-w-[1785px] mx-auto px-4 sm:px-6 py-9">{{ $slot }}</main>
    <script>
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                const light = document.documentElement.classList.toggle('light');
                document.documentElement.classList.toggle('dark', !light);
                localStorage.setItem('theme', light ? 'light' : 'dark');
                themeIcon.className = light ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
            });
        }
    </script>
</body>
</html>
