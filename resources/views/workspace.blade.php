@php
$pages = [
    'people' => ['label' => 'People', 'icon' => 'fa-users', 'description' => 'Manage your contacts and customer relationships.'],
    'inbox' => ['label' => 'Inbox', 'icon' => 'fa-comment-dots', 'description' => 'Keep every conversation in one shared inbox.'],
    'pipeline' => ['label' => 'Pipeline', 'icon' => 'fa-bullseye', 'description' => 'Track opportunities from first conversation to closed deal.'],
    'marketing' => ['label' => 'Marketing', 'icon' => 'fa-bullhorn', 'description' => 'Create campaigns and measure the results that matter.'],
    'automations' => ['label' => 'Automations', 'icon' => 'fa-bolt', 'description' => 'Build workflows that keep your business moving.'],
    'sites' => ['label' => 'Sites', 'icon' => 'fa-globe', 'description' => 'Manage your landing pages, forms, and websites.'],
];
$current = $pages[$page] ?? ['label' => '404'];
@endphp
<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $current['label'] }} - Flowmentum Path</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        darkBg: '#080B11',
                        cardBg: '#0F1523',
                        cardBorder: '#1A2337'
                    }
                }
            }
        }
    </script>
    <script>
        if (localStorage.getItem('theme') === 'light') {
            document.documentElement.classList.remove('dark');
        } else {
            document.documentElement.classList.add('dark');
        }
    </script>
    <style>
        body { font-family: Inter, system-ui, sans-serif }
    </style>
</head>

<body class="min-h-screen bg-slate-50 dark:bg-darkBg text-slate-800 dark:text-slate-200 antialiased transition-colors duration-200">

    <!-- Header Navigation -->
    <header class="h-[72px] overflow-hidden bg-white dark:bg-[#0b0f19] border-b border-slate-200 dark:border-[#131B2E] px-6 flex items-center text-sm sticky top-0 z-50 transition-colors">
        <div class="flex items-center gap-4 min-w-0">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-bold text-slate-900 dark:text-white text-lg whitespace-nowrap shrink-0">
                <span class="w-8 h-8 rounded-xl bg-[#3d7fe9] flex items-center justify-center text-sm text-[#071427]"><i class="fa-solid fa-bolt"></i></span>Flowmentum Path
            </a>
            <nav class="hidden xl:flex items-center gap-1 whitespace-nowrap">
                @foreach(['dashboard'=>'Dashboard', 'people'=>'People', 'inbox'=>'Inbox', 'pipeline'=>'Pipeline', 'marketing'=>'Marketing', 'automations'=>'Automations', 'sites'=>'Sites'] as $key => $label)
                @php $icons=['dashboard'=>'fa-border-all','people'=>'fa-users','inbox'=>'fa-comment-dots','pipeline'=>'fa-bullseye','marketing'=>'fa-bullhorn','automations'=>'fa-bolt','sites'=>'fa-globe']; $active=$key===$page; @endphp
                <a href="{{ $key === 'dashboard' ? route('dashboard') : route($key) }}" class="px-3 py-2 rounded-xl flex items-center gap-2 text-sm font-medium transition-colors {{ $active ? 'bg-blue-50 dark:bg-[#16233b] text-blue-600 dark:text-[#5b9bf6] border border-blue-500/10 dark:border-blue-500/5' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.03]' }}"><i class="fa-solid {{ $icons[$key] }}"></i>{{ $label }}</a>
                @endforeach
            </nav>
        </div>
        <div class="flex items-center gap-4 shrink-0 ml-auto">
            <div class="h-9 bg-slate-100 dark:bg-[#0b101b] border border-slate-200 dark:border-[#1E293B] text-slate-700 dark:text-slate-200 text-sm px-3 rounded-xl flex items-center gap-2 cursor-pointer hover:border-slate-300 dark:hover:border-slate-700 transition-colors"><i class="fa-solid fa-location-dot text-blue-500 dark:text-blue-400"></i>Flowmentum HQ<i class="fa-solid fa-chevron-down text-[10px] text-slate-400 dark:text-slate-500 ml-1"></i></div>
            <div class="relative hidden sm:block"><i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 dark:text-slate-500 text-xs"></i><input placeholder="Search..." class="h-9 w-56 bg-slate-100 dark:bg-[#161d2c] border border-slate-200 dark:border-transparent text-sm text-slate-800 dark:text-slate-200 placeholder:text-slate-400 pl-8 pr-4 rounded-xl outline-none focus:border-blue-500/60 transition-colors"></div>
            <div class="flex items-center gap-1 text-slate-500 dark:text-slate-400 text-sm"><button class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-white/[0.04] hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition-colors"><i class="fa-solid fa-phone"></i></button><button class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-white/[0.04] hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition-colors"><i class="fa-solid fa-comments"></i></button><button class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-white/[0.04] hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition-colors"><i class="fa-solid fa-envelope"></i></button><button class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-white/[0.04] hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition-colors"><i class="fa-solid fa-calendar"></i></button></div>
            <div class="h-6 border-l border-slate-200 dark:border-[#1E293B] mx-1"></div>
            <button class="h-9 bg-[#3d7fe9] hover:bg-[#5b9bf6] text-white dark:text-[#06101f] text-sm font-medium px-4 rounded-xl transition-colors flex items-center gap-2"><i class="fa-solid fa-plus"></i>Create</button>
            <div class="flex items-center gap-1 text-slate-500 dark:text-slate-400 text-sm">
                <button class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-white/[0.04] hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition-colors"><i class="fa-solid fa-bell"></i></button>
                <button class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-white/[0.04] hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition-colors"><i class="fa-solid fa-gear"></i></button>
                <button id="themeToggle" class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-white/[0.04] hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition-colors">
                    <i id="themeIcon" class="fa-solid fa-moon"></i>
                </button>
                <img src="https://i.pravatar.cc/100?img=12" class="w-8 h-8 rounded-full border border-blue-500/80 object-cover" alt="Profile">
            </div>
        </div>
    </header>

    @if(array_key_exists($page, $pages))
    <main class="w-full max-w-[1785px] mx-auto px-4 sm:px-6 py-9">
        <div class="flex items-start justify-between gap-4 mb-7">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $current['label'] }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $current['description'] }}</p>
            </div>
            <div class="flex gap-2">
                <button class="h-10 px-4 bg-white dark:bg-transparent border border-slate-200 dark:border-cardBorder hover:bg-slate-100 dark:hover:bg-white/[0.04] text-slate-700 dark:text-slate-200 rounded-xl text-sm font-medium transition-colors"><i class="fa-solid fa-filter mr-3"></i>Filter</button>
                <button class="h-10 px-4 bg-[#5b9bf6] hover:bg-[#6ca7fa] text-white dark:text-[#06101f] rounded-xl text-sm font-medium transition-colors"><i class="fa-solid fa-plus mr-3"></i>Add {{ $current['label'] }}</button>
            </div>
        </div>

        <!-- PEOPLE SECTION -->
        @if($page === 'people')
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 mt-8">
            @forelse($people ?? [] as $person)
            <article class="min-h-[186px] bg-white dark:bg-cardBg border border-slate-200 dark:border-cardBorder rounded-xl p-5 hover:border-blue-500/40 transition-all shadow-sm">
                <div class="flex gap-3">
                    <img src="https://i.pravatar.cc/100?img={{ ($loop->index % 70) + 1 }}" class="w-11 h-11 rounded-full object-cover">
                    <div class="min-w-0 flex-1">
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white truncate">{{ $person->first_name ?? '' }} {{ $person->last_name ?? '' }}</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate">{{ $person->company_name ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="space-y-2 mt-5 text-sm text-slate-600 dark:text-slate-400">
                    <p class="truncate"><i class="fa-regular fa-envelope w-5"></i>{{ $person->email ?? 'N/A' }}</p>
                    <p><i class="fa-solid fa-phone w-5"></i>{{ $person->phone ?? 'N/A' }}</p>
                </div>
            </article>
            @empty
            <div class="col-span-full text-center py-10 text-slate-500">No people records found.</div>
            @endforelse
        </div>

        <!-- INBOX SECTION -->
        @elseif($page === 'inbox')
        <div class="space-y-3 mt-6">
            @forelse($messages ?? [] as $msg)
            <div class="p-4 bg-white dark:bg-cardBg border border-slate-200 dark:border-cardBorder rounded-xl flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-slate-900 dark:text-white">{{ $msg->sender_name ?? 'Sender' }}</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $msg->body ?? 'No message body' }}</p>
                </div>
                <span class="text-xs text-slate-400">{{ $msg->created_at?->diffForHumans() }}</span>
            </div>
            @empty
            <div class="text-center py-10 text-slate-500">No inbox messages found.</div>
            @endforelse
        </div>

        <!-- PIPELINE SECTION -->
        @elseif($page === 'pipeline')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            @forelse($deals ?? [] as $deal)
            <div class="p-4 bg-white dark:bg-cardBg border border-slate-200 dark:border-cardBorder rounded-xl">
                <h3 class="font-semibold text-slate-900 dark:text-white">{{ $deal->title ?? 'Deal Name' }}</h3>
                <p class="text-sm text-blue-500 font-bold mt-2">${{ number_format($deal->value ?? 0) }}</p>
            </div>
            @empty
            <div class="col-span-full text-center py-10 text-slate-500">No pipeline deals found.</div>
            @endforelse
        </div>

        <!-- OTHER PAGES FALLBACK -->
        @else
        <div class="p-8 bg-white dark:bg-cardBg border border-slate-200 dark:border-cardBorder rounded-xl text-center text-slate-500">
            No data connected for {{ $current['label'] }} yet.
        </div>
        @endif
    </main>

    @else
    <main class="min-h-[calc(100vh-72px)] px-4 flex items-center justify-center">
        <section class="-mt-24 text-center max-w-xl">
            <div class="w-24 h-24 rounded-full bg-slate-200 dark:bg-[#202b3d] text-slate-600 dark:text-[#8baad7] flex items-center justify-center mx-auto text-[36px] font-bold">404</div>
            <h1 class="mt-7 text-[30px] leading-tight font-bold text-slate-900 dark:text-white">Page not found</h1>
            <a href="{{ route('dashboard') }}" class="inline-flex mt-8 h-10 items-center rounded-xl bg-[#5b9bf6] hover:bg-[#6ca7fa] px-4 text-sm font-medium text-white dark:text-[#06101f]">Return to Dashboard</a>
        </section>
    </main>
    @endif

    <script>
        const themeToggleBtn = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        function updateThemeUI() {
            const isDark = document.documentElement.classList.contains('dark');
            themeIcon.className = isDark ? 'fa-solid fa-sun text-amber-400' : 'fa-solid fa-moon text-slate-600';
        }
        updateThemeUI();
        themeToggleBtn.addEventListener('click', () => {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
            updateThemeUI();
        });
    </script>
</body>
</html>