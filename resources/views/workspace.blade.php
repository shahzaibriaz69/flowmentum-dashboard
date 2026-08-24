@php
$pages = [
'people' => ['label' => 'People', 'icon' => 'fa-users', 'description' => 'Manage your contacts and customer relationships.', 'button' => 'Add Contact', 'stats' => ['2,350 Contacts', '186 New This Month', '68% Engaged']],
'inbox' => ['label' => 'Inbox', 'icon' => 'fa-comment-dots', 'description' => 'Keep every conversation in one shared inbox.', 'button' => 'New Message', 'stats' => ['24 Unread', '8 Assigned to You', '4 Awaiting Reply']],
'pipeline' => ['label' => 'Pipeline', 'icon' => 'fa-bullseye', 'description' => 'Track opportunities from first conversation to closed deal.', 'button' => 'Add Opportunity', 'stats' => ['$124,500 Pipeline Value', '18 Open Opportunities', '34.2% Win Rate']],
'marketing' => ['label' => 'Marketing', 'icon' => 'fa-bullhorn', 'description' => 'Create campaigns and measure the results that matter.', 'button' => 'New Campaign', 'stats' => ['12 Active Campaigns', '18.4K Contacts Reached', '42.8% Open Rate']],
'automations' => ['label' => 'Automations', 'icon' => 'fa-bolt', 'description' => 'Build workflows that keep your business moving.', 'button' => 'Create Workflow', 'stats' => ['18 Active Workflows', '3,892 Runs This Week', '99.8% Success Rate']],
'sites' => ['label' => 'Sites', 'icon' => 'fa-globe', 'description' => 'Manage your landing pages, forms, and websites.', 'button' => 'Create Site', 'stats' => ['4 Published Sites', '26 Active Forms', '8,240 Page Views']],
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
        // FOUC Preventer & LocalStorage Load
        if (localStorage.getItem('theme') === 'light') {
            document.documentElement.classList.remove('dark');
        } else {
            document.documentElement.classList.add('dark');
        }
    </script>
    <style>
        body {
            font-family: Inter, system-ui, sans-serif
        }
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

                <!-- Dark / Light Mode Switcher Button -->
                <button id="themeToggle" class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-white/[0.04] hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition-colors">
                    <i id="themeIcon" class="fa-solid fa-moon"></i>
                </button>

                <button class="w-8 h-8 rounded-lg hover:bg-red-500/10 dark:hover:bg-red-500/20 hover:text-red-500 dark:hover:text-red-400 flex items-center justify-center transition-colors"><i class="fa-solid fa-trash-can"></i></button>
                <img src="https://i.pravatar.cc/100?img=12" class="w-8 h-8 rounded-full border border-blue-500/80 object-cover" alt="Profile">
            </div>
        </div>
    </header>

    @if($page === 'people')
    @php $people = [
    ['Alice Johnson', 'CEO at TechCorp', 'alice@techcorp.com', '+1 234 567 890', '12', ['VIP', 'Lead']],
    ['Bob Smith', 'Marketing Director at Acme Inc', 'bob@acme.com', '+1 987 654 321', '33', ['Customer']],
    ['Charlie Davis', 'Founder at Startup LLC', 'charlie@startup.com', '+1 555 123 456', '44', ['Lead', 'Hot']],
    ['Diana Prince', 'Consultant at Freelance', 'diana@freelance.com', '+1 444 555 666', '60', ['Partner']],
    ['Ethan Hunt', 'Operations at IMF', 'ethan@imf.com', '+1 777 888 999', '68', ['Customer']],
    ]; @endphp
    <main class="w-full max-w-[1785px] mx-auto px-4 sm:px-6 py-9">
        <div class="flex items-start justify-between gap-4 mb-7">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">People</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage your contacts and relationships.</p>
            </div>
            <div class="flex gap-2"><button class="h-10 px-4 bg-white dark:bg-transparent border border-slate-200 dark:border-cardBorder hover:bg-slate-100 dark:hover:bg-white/[0.04] text-slate-700 dark:text-slate-200 rounded-xl text-sm font-medium transition-colors"><i class="fa-solid fa-filter mr-3"></i>Filter</button><button class="h-10 px-4 bg-[#5b9bf6] hover:bg-[#6ca7fa] text-white dark:text-[#06101f] rounded-xl text-sm font-medium transition-colors"><i class="fa-solid fa-plus mr-3"></i>Add Person</button></div>
        </div>
        <label class="h-10 w-full bg-white dark:bg-[#111927] border border-slate-200 dark:border-cardBorder rounded-xl flex items-center px-3 text-sm text-slate-400"><i class="fa-solid fa-magnifying-glass mr-3"></i><input class="bg-transparent outline-none text-slate-800 dark:text-slate-200 placeholder:text-slate-400 w-full" placeholder="Search people by name, email, or company..."></label>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 mt-8">
            @foreach($people as [$name, $role, $email, $phone, $image, $tags])
            <article class="min-h-[186px] bg-white dark:bg-cardBg border border-slate-200 dark:border-cardBorder rounded-xl p-5 hover:border-blue-500/40 hover:-translate-y-0.5 transition-all shadow-sm dark:shadow-none">
                <div class="flex gap-3"><img src="https://i.pravatar.cc/100?img={{ $image }}" class="w-11 h-11 rounded-full object-cover" alt="{{ $name }}">
                    <div class="min-w-0 flex-1">
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white truncate">{{ $name }}</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate">{{ $role }}</p>
                    </div><button class="text-slate-400 hover:text-slate-600 dark:hover:text-white self-start"><i class="fa-solid fa-ellipsis"></i></button>
                </div>
                <div class="space-y-2 mt-5 text-sm text-slate-600 dark:text-slate-400">
                    <p class="truncate"><i class="fa-regular fa-envelope w-5"></i>{{ $email }}</p>
                    <p><i class="fa-solid fa-phone w-5"></i>{{ $phone }}</p>
                </div>
                <div class="flex gap-1.5 mt-4">@foreach($tags as $tag)<span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 dark:bg-slate-700/70 text-slate-700 dark:text-slate-200">{{ $tag }}</span>@endforeach</div>
            </article>
            @endforeach
        </div>
    </main>

    @elseif($page === 'inbox')
    @php $conversations = [
    ['Alice Johnson', 'Can we schedule a call?', '10:23 AM', '12', 'online', true],
    ['Bob Smith', 'The proposal looks great.', 'Yesterday', '33', 'away', false],
    ['Charlie Davis', 'Thanks for the update!', 'Mon', '44', 'online', false],
    ['Diana Prince', 'I need to change my plan.', 'Last week', '60', 'busy', true],
    ]; @endphp
    <main class="w-full max-w-[1785px] mx-auto px-4 sm:px-6 py-5">
        <section class="h-[824px] bg-white dark:bg-cardBg border border-slate-200 dark:border-cardBorder rounded-xl overflow-hidden grid grid-cols-1 md:grid-cols-[320px_1fr]">
            <aside class="border-r border-slate-200 dark:border-cardBorder flex flex-col">
                <div class="p-4 border-b border-slate-200 dark:border-cardBorder">
                    <h1 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Inbox</h1><label class="h-10 bg-slate-100 dark:bg-[#0b101b] border border-slate-200 dark:border-cardBorder rounded-xl flex items-center px-3 text-sm text-slate-400"><i class="fa-solid fa-magnifying-glass mr-3"></i><input class="w-full bg-transparent outline-none text-slate-800 dark:text-slate-200 placeholder:text-slate-400" placeholder="Search messages..."></label>
                </div>
                <div class="p-3 space-y-1">
                    @foreach($conversations as [$name, $preview, $time, $image, $status, $unread])
                    <button type="button" data-name="{{ $name }}" data-image="{{ $image }}" data-status="{{ $status === 'online' ? 'Online recently' : ($status === 'away' ? 'Away' : 'Last active recently') }}" class="conversation w-full text-left rounded-xl px-3 py-3 flex gap-3 transition-colors {{ $loop->first ? 'bg-blue-50 dark:bg-[#192b47] border border-blue-500/30' : 'hover:bg-slate-100 dark:hover:bg-white/[0.035]' }}">
                        <span class="relative shrink-0"><img src="https://i.pravatar.cc/100?img={{ $image }}" class="w-10 h-10 rounded-full" alt="{{ $name }}"><i class="absolute -right-0.5 bottom-0 w-2.5 h-2.5 rounded-full border-2 border-white dark:border-cardBg {{ $status === 'online' ? 'bg-emerald-400' : ($status === 'away' ? 'bg-amber-400' : 'bg-rose-500') }}"></i></span>
                        <span class="min-w-0 flex-1"><span class="flex justify-between gap-2"><strong class="text-sm text-slate-900 dark:text-white truncate">{{ $name }}</strong><small class="text-[10px] text-blue-500 dark:text-blue-400 whitespace-nowrap">{{ $time }}</small></span><span class="flex justify-between gap-2 mt-1"><small class="text-xs {{ $unread ? 'text-slate-900 dark:text-white font-semibold' : 'text-slate-500 dark:text-slate-400' }} truncate">{{ $preview }}</small>@if($unread)<i class="fa-solid fa-circle text-[8px] text-blue-500 dark:text-blue-400"></i>@endif</span></span>
                    </button>
                    @endforeach
                </div>
            </aside>
            <div class="min-w-0 flex flex-col">
                <div class="h-16 px-6 border-b border-slate-200 dark:border-cardBorder flex items-center justify-between">
                    <div class="flex items-center gap-3"><img id="chat-avatar" src="https://i.pravatar.cc/100?img=12" class="w-10 h-10 rounded-full" alt="Contact">
                        <div>
                            <h2 id="chat-name" class="text-sm font-semibold text-slate-900 dark:text-white">Alice Johnson</h2>
                            <p id="chat-status" class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Online recently</p>
                        </div>
                    </div>
                    <div class="flex gap-5 text-slate-500 dark:text-slate-300"><button><i class="fa-solid fa-phone"></i></button><button><i class="fa-solid fa-video"></i></button><button><i class="fa-solid fa-ellipsis-vertical"></i></button></div>
                </div>
                <div class="flex-1 p-6 space-y-6 overflow-y-auto bg-slate-50/50 dark:bg-transparent">
                    <div class="flex">
                        <div class="max-w-md bg-slate-200 dark:bg-[#202c3c] rounded-2xl rounded-tl-md px-4 py-3 text-sm text-slate-800 dark:text-white">Hi, I have a question about the pricing.<small class="block text-right text-[10px] text-slate-500 dark:text-slate-400 mt-2">10:00 AM</small></div>
                    </div>
                    <div class="flex justify-end">
                        <div class="max-w-md bg-[#5b9bf6] rounded-2xl rounded-tr-md px-4 py-3 text-sm text-white dark:text-[#071427]">Hello Alice! I'd be happy to help. What would you like to know?<small class="block text-right text-[10px] text-blue-100 dark:text-[#234f8e] mt-2">10:05 AM</small></div>
                    </div>
                    <div class="flex">
                        <div class="max-w-md bg-slate-200 dark:bg-[#202c3c] rounded-2xl rounded-tl-md px-4 py-3 text-sm text-slate-800 dark:text-white">Can we schedule a call?<small class="block text-right text-[10px] text-slate-500 dark:text-slate-400 mt-2">10:23 AM</small></div>
                    </div>
                </div>
                <div class="p-4 border-t border-slate-200 dark:border-cardBorder">
                    <div class="h-12 bg-slate-100 dark:bg-[#121b2a] border border-slate-200 dark:border-cardBorder rounded-full px-4 flex items-center gap-4 text-slate-400"><button><i class="fa-solid fa-paperclip"></i></button><input class="flex-1 bg-transparent outline-none text-sm text-slate-800 dark:text-slate-200 placeholder:text-slate-400" placeholder="Type a message..."><button><i class="fa-regular fa-face-smile"></i></button><button class="w-8 h-8 rounded-full bg-[#5b9bf6] text-white dark:text-[#071427]"><i class="fa-solid fa-paper-plane"></i></button></div>
                </div>
            </div>
        </section>
    </main>
    <script>
        document.querySelectorAll('.conversation').forEach((item) => item.addEventListener('click', () => {
            document.querySelectorAll('.conversation').forEach((row) => row.classList.remove('bg-blue-50', 'dark:bg-[#192b47]', 'border', 'border-blue-500/30'));
            item.classList.add('bg-blue-50', 'dark:bg-[#192b47]', 'border', 'border-blue-500/30');
            document.getElementById('chat-name').textContent = item.dataset.name;
            document.getElementById('chat-status').textContent = item.dataset.status;
            document.getElementById('chat-avatar').src = `https://i.pravatar.cc/100?img=${item.dataset.image}`;
        }));
    </script>

    @elseif($page === 'pipeline')
    @php
    $stages = [
    ['New Lead', 'bg-blue-500', '$12,500', [['Acme Corp', 'Website Redesign', 'Oct 12', '$4,500', '12', 'border-l-red-500 bg-white dark:bg-[#151420]'], ['TechFlow', 'SEO Campaign', 'Oct 14', '$3,000', '33', 'border-l-yellow-400 bg-white dark:bg-[#1d1e20]'], ['Global Inc', 'Consulting', 'Oct 15', '$5,000', '60', 'border-l-blue-500 bg-white dark:bg-[#111b2c]']]],
    ['Contacted', 'bg-yellow-400', '$8,000', [['StartUp LLC', 'App Development', 'Oct 10', '$6,500', '44', 'border-l-red-500 bg-white dark:bg-[#151420]'], ['Retail Co', 'Marketing Retainer', 'Oct 11', '$1,500', '68', 'border-l-blue-500 bg-white dark:bg-[#111b2c]']]],
    ['Proposal Sent', 'bg-violet-500', '$45,000', [['Big Corp', 'Enterprise Setup', 'Oct 05', '$25,000', '47', 'border-l-red-500 bg-white dark:bg-[#151420]'], ['Data Systems', 'Cloud Migration', 'Oct 08', '$12,000', '32', 'border-l-yellow-400 bg-white dark:bg-[#1d1e20]'], ['FinTech Inc', 'Security Audit', 'Oct 09', '$4,000', '11', 'border-l-yellow-400 bg-white dark:bg-[#1d1e20]'], ['Creative Agency', 'UI/UX Design', 'Oct 10', '$4,000', '68', 'border-l-blue-500 bg-white dark:bg-[#111b2c]']]],
    ['Closed Won', 'bg-emerald-500', '$5,000', [['Loyal Client', 'Q3 Retainer', 'Oct 01', '$5,000', '25', 'border-l-emerald-400 bg-white dark:bg-[#10202a]']]],
    ];
    @endphp
    <main class="w-full max-w-[1785px] mx-auto px-4 sm:px-6 py-9">
        <div class="flex items-start justify-between gap-4 mb-7">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Pipeline</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage your deals and opportunities.</p>
            </div>
            <div class="flex gap-2"><button class="h-10 px-4 bg-white dark:bg-transparent border border-slate-200 dark:border-cardBorder hover:bg-slate-100 dark:hover:bg-white/[0.04] text-slate-700 dark:text-slate-200 rounded-xl text-sm font-medium"><i class="fa-solid fa-filter mr-3"></i>Filter</button><button class="h-10 px-4 bg-[#5b9bf6] hover:bg-[#6ca7fa] text-white dark:text-[#06101f] rounded-xl text-sm font-medium"><i class="fa-solid fa-plus mr-3"></i>New Deal</button></div>
        </div>
        <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 items-start">
            @foreach($stages as [$stage, $color, $total, $deals])
            <div class="min-h-[730px] bg-slate-100/70 dark:bg-[#101624] border border-slate-200 dark:border-cardBorder rounded-xl overflow-hidden">
                <div class="h-12 px-3 border-b border-slate-200 dark:border-cardBorder flex items-center justify-between">
                    <div class="flex items-center gap-2 text-sm font-semibold text-slate-800 dark:text-slate-200"><span class="w-2.5 h-2.5 rounded-full {{ $color }}"></span>{{ $stage }}<span class="ml-1 px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700/70 text-[11px] text-slate-700 dark:text-slate-300">{{ count($deals) }}</span></div><span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $total }}</span>
                </div>
                <div class="p-3 space-y-3">
                    @foreach($deals as [$company, $title, $date, $amount, $avatar, $card])
                    <article class="border border-slate-200 dark:border-cardBorder border-l-4 {{ $card }} rounded-xl p-3.5 shadow-sm hover:-translate-y-0.5 hover:border-blue-500/40 transition-all">
                        <div class="flex justify-between gap-2"><span class="max-w-[130px] truncate rounded-full border border-blue-400/50 px-1.5 py-0.5 text-[9px] font-medium text-slate-800 dark:text-white">{{ $company }}</span><button class="text-slate-400 hover:text-slate-600 dark:hover:text-white"><i class="fa-solid fa-ellipsis"></i></button></div>
                        <h2 class="mt-3 text-sm font-semibold text-slate-900 dark:text-white">{{ $title }}</h2>
                        <div class="mt-4 flex items-center justify-between text-xs"><span class="text-slate-500 dark:text-slate-400"><i class="fa-regular fa-calendar mr-2"></i>{{ $date }}</span><span class="flex items-center gap-3"><strong class="text-slate-900 dark:text-white">{{ $amount }}</strong><img src="https://i.pravatar.cc/100?img={{ $avatar }}" class="w-6 h-6 rounded-full" alt="Assignee"></span></div>
                    </article>
                    @endforeach
                </div>
            </div>
            @endforeach
        </section>
    </main>

    @else
    <main class="min-h-[calc(100vh-72px)] px-4 flex items-center justify-center">
        <section class="-mt-24 text-center max-w-xl">
            <div class="w-24 h-24 rounded-full bg-slate-200 dark:bg-[#202b3d] text-slate-600 dark:text-[#8baad7] flex items-center justify-center mx-auto text-[36px] font-bold">404</div>
            <h1 class="mt-7 text-[30px] leading-tight font-bold text-slate-900 dark:text-white">Page not found</h1>
            <p class="mt-2 text-lg leading-7 text-slate-600 dark:text-[#8baad7]">The page you're looking for doesn't exist or has been moved.<br>Please check the URL or navigate back to the dashboard.</p>
            <a href="{{ route('dashboard') }}" class="inline-flex mt-8 h-10 items-center rounded-xl bg-[#5b9bf6] hover:bg-[#6ca7fa] px-4 text-sm font-medium text-white dark:text-[#06101f] transition-colors">Return to Dashboard</a>
        </section>
    </main>
    @endif

    <!-- Theme Switcher Script -->
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