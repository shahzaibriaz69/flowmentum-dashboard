<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flowmentum Path - Dashboard</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            darkMode: 'class', // Class strategy for smooth switching
            theme: {
                extend: {
                    colors: {
                        darkBg: '#080B11',
                        cardBg: '#0F1523',
                        cardBorder: '#1A2337',
                        accentBlue: '#5B9BF6',
                        accentDarkBlue: '#3478E8',
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
    </style>

    <!-- Page Load / Refresh Fix Script -->
    <script>
        if (localStorage.getItem('theme') === 'light' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: light)').matches)) {
            document.documentElement.classList.remove('dark');
        } else {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>
<body class="bg-slate-100 dark:bg-darkBg text-slate-800 dark:text-slate-200 min-h-screen flex flex-col antialiased transition-colors duration-200">

    <!-- Top Navigation Bar -->
    <header class="h-[72px] shrink-0 overflow-hidden bg-white dark:bg-[#0b0f19] border-b border-slate-200 dark:border-[#131B2E] px-6 flex items-center text-sm sticky top-0 z-50">
        <!-- Left Section: Logo & Nav Links -->
        <div class="flex items-center gap-4 min-w-0">
            <!-- Logo -->
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-bold text-slate-900 dark:text-white text-lg whitespace-nowrap shrink-0">
                <div class="w-8 h-8 rounded-xl bg-[#3d7fe9] flex items-center justify-center text-sm text-[#071427] shadow-sm shadow-blue-500/20">
                    <i class="fa-solid fa-bolt"></i>
                </div>
                <span>Flowmentum Path</span>
            </a>

            <!-- Navigation Links -->
            <nav class="hidden xl:flex items-center gap-1 whitespace-nowrap">
                <a href="{{ route('dashboard') }}" class="bg-blue-50 dark:bg-[#16233b] text-[#3478E8] dark:text-[#5b9bf6] px-3 py-2 rounded-xl flex items-center gap-2 font-medium border border-blue-500/10 dark:border-blue-500/5 text-sm transition-colors">
                    <i class="fa-solid fa-border-all text-blue-500 dark:text-blue-400"></i> Dashboard
                </a>
                <a href="{{ route('workspace', 'people') }}" class="text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.03] px-3 py-2 rounded-xl flex items-center gap-2 text-sm transition-colors">
                    <i class="fa-solid fa-users"></i> People
                </a>
                <a href="{{ route('workspace', 'inbox') }}" class="text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.03] px-3 py-2 rounded-xl flex items-center gap-2 text-sm transition-colors">
                    <i class="fa-solid fa-comment-dots"></i> Inbox
                </a>
                <a href="{{ route('workspace', 'pipeline') }}" class="text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.03] px-3 py-2 rounded-xl flex items-center gap-2 text-sm transition-colors">
                    <i class="fa-solid fa-bullseye"></i> Pipeline
                </a>
                <a href="{{ route('workspace', 'marketing') }}" class="text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.03] px-3 py-2 rounded-xl flex items-center gap-2 text-sm transition-colors">
                    <i class="fa-solid fa-bullhorn"></i> Marketing
                </a>
                <a href="{{ route('workspace', 'automations') }}" class="text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.03] px-3 py-2 rounded-xl flex items-center gap-2 text-sm transition-colors">
                    <i class="fa-solid fa-bolt"></i> Automations
                </a>
                <a href="{{ route('workspace', 'sites') }}" class="text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.03] px-3 py-2 rounded-xl flex items-center gap-2 text-sm transition-colors">
                    <i class="fa-solid fa-globe"></i> Sites
                </a>
            </nav>
        </div>

        <!-- Right Section -->
        <div class="flex items-center gap-4 shrink-0 ml-auto">
            <!-- Location Selector -->
            <div class="h-9 bg-slate-50 dark:bg-[#0b101b] border border-slate-200 dark:border-[#1E293B] text-slate-700 dark:text-slate-200 text-sm px-3 rounded-xl flex items-center gap-2 cursor-pointer hover:border-slate-300 dark:hover:border-slate-700 transition-colors">
                <i class="fa-solid fa-location-dot text-blue-500 dark:text-blue-400"></i>
                <span>Flowmentum HQ</span>
                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 dark:text-slate-500 ml-1"></i>
            </div>

            <!-- Search Input -->
            <div class="relative hidden sm:block">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 dark:text-slate-500 text-xs"></i>
                <input type="text" placeholder="Search..." class="h-9 bg-slate-100 dark:bg-[#161d2c] border border-transparent text-sm text-slate-800 dark:text-slate-200 placeholder:text-slate-400 dark:placeholder:text-slate-400 pl-8 pr-4 rounded-xl w-56 focus:outline-none focus:border-blue-500/60 transition-colors">
            </div>

            <!-- Quick Utility Icons -->
            <div class="flex items-center gap-1 text-slate-500 dark:text-slate-400 text-sm">
                <button class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-white/[0.04] hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition-colors"><i class="fa-solid fa-phone"></i></button>
                <button class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-white/[0.04] hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition-colors"><i class="fa-solid fa-comments"></i></button>
                <button class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-white/[0.04] hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition-colors"><i class="fa-solid fa-envelope"></i></button>
                <button class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-white/[0.04] hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition-colors"><i class="fa-solid fa-calendar"></i></button>
            </div>

            <!-- Divider Line -->
            <div class="h-6 border-l border-slate-200 dark:border-[#1E293B] mx-1"></div>

            <!-- Create Button -->
            <button class="h-9 bg-[#3d7fe9] hover:bg-[#5b9bf6] text-white dark:text-[#06101f] text-sm font-medium px-4 rounded-xl transition-colors flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Create
            </button>

            <!-- Right Actions & Profile -->
            <div class="flex items-center gap-1 text-slate-500 dark:text-slate-400 text-sm">
                <button class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-white/[0.04] hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition-colors"><i class="fa-solid fa-bell"></i></button>
                <button class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-white/[0.04] hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition-colors"><i class="fa-solid fa-gear"></i></button>
                
                <!-- Updated Theme Toggle Button -->
                <button id="themeToggle" class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-white/[0.04] hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition-colors">
                    <i id="themeIcon" class="fa-solid fa-moon"></i>
                </button>

                <button class="w-8 h-8 rounded-lg hover:bg-red-500/20 hover:text-red-500 dark:hover:text-red-400 flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
                <img src="https://i.pravatar.cc/100?img=12" alt="Avatar" class="w-8 h-8 rounded-full border border-blue-500/80 object-cover ml-1">
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 w-full max-w-[1785px] mx-auto px-4 sm:px-6 py-9 space-y-6">

        <!-- Header Title & Actions -->
        <div class="flex flex-col md:flex-row justify-between md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Dashboard</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Welcome back, Sarah. Here's what's happening today.</p>
            </div>
            <div class="flex items-center gap-3">
                <button class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-cardBorder hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs px-4 py-2 rounded-lg font-medium transition">
                    Download Report
                </button>
                <a href="{{ route('marketplace.authenticate') }}" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-cardBorder hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs px-4 py-2 rounded-lg font-medium transition">
                    Authenticate Location
</a>
                <button class="bg-[#5b9bf6] hover:bg-[#6ca7fa] text-white dark:text-[#06101f] text-xs px-4 py-2 rounded-lg font-medium transition">
                    New Campaign
                </button>
            </div>
        </div>

        <!-- 4 Stat Cards Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Card 1 -->
            <div class="bg-white dark:bg-cardBg border border-slate-200 dark:border-cardBorder p-4 rounded-xl flex justify-between items-start shadow-sm dark:shadow-none">
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Revenue</p>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-2">$45,231.89</h2>
                    <p class="text-[11px] text-emerald-500 dark:text-emerald-400 mt-2 font-medium">↗ +20.1% <span class="text-slate-400 dark:text-slate-500">from last month</span></p>
                </div>
                <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-500 dark:text-blue-400 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-dollar-sign"></i>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="bg-white dark:bg-cardBg border border-slate-200 dark:border-cardBorder p-4 rounded-xl flex justify-between items-start shadow-sm dark:shadow-none">
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Active Contacts</p>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-2">+2350</h2>
                    <p class="text-[11px] text-emerald-500 dark:text-emerald-400 mt-2 font-medium">↗ +180 <span class="text-slate-400 dark:text-slate-500">new this week</span></p>
                </div>
                <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-500 dark:text-blue-400 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-user-group"></i>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="bg-white dark:bg-cardBg border border-slate-200 dark:border-cardBorder p-4 rounded-xl flex justify-between items-start shadow-sm dark:shadow-none">
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Win Rate</p>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-2">34.2%</h2>
                    <p class="text-[11px] text-emerald-500 dark:text-emerald-400 mt-2 font-medium">↗ +4.1% <span class="text-slate-400 dark:text-slate-500">from last month</span></p>
                </div>
                <div class="w-8 h-8 rounded-lg bg-violet-500/10 text-violet-500 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-bullseye"></i>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="bg-white dark:bg-cardBg border border-slate-200 dark:border-cardBorder p-4 rounded-xl flex justify-between items-start shadow-sm dark:shadow-none">
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Pipeline Value</p>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-2">$124,500</h2>
                    <p class="text-[11px] text-rose-500 mt-2 font-medium">↘ -2.5% <span class="text-slate-400 dark:text-slate-500">from last month</span></p>
                </div>
                <div class="w-8 h-8 rounded-lg bg-orange-500/10 text-orange-500 dark:text-orange-400 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
            </div>
        </div>

        <!-- Middle Section: Chart + Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Revenue Overview Chart -->
            <div class="lg:col-span-2 bg-white dark:bg-cardBg border border-slate-200 dark:border-cardBorder p-5 rounded-xl flex flex-col justify-between shadow-sm dark:shadow-none">
                <div>
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white">Revenue Overview</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Monthly revenue performance for the current year</p>
                </div>
                <div class="mt-6 h-64 w-full">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white dark:bg-cardBg border border-slate-200 dark:border-cardBorder p-5 rounded-xl flex flex-col justify-between shadow-sm dark:shadow-none">
                <div>
                    <div class="flex justify-between items-center">
                        <h3 class="text-base font-semibold text-slate-900 dark:text-white">Recent Activity</h3>
                        <button class="text-slate-400 dark:text-slate-500 hover:text-slate-900 dark:hover:text-white text-xs"><i class="fa-solid fa-ellipsis"></i></button>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Latest actions across your team</p>

                    <div class="mt-4 space-y-4 text-xs">
                        <div class="flex gap-3">
                            <img src="https://i.pravatar.cc/100?img=32" class="w-8 h-8 rounded-full border border-slate-200 dark:border-cardBorder">
                            <div>
                                <p class="text-slate-600 dark:text-slate-300"><strong class="text-slate-900 dark:text-white">Olivia Martin</strong> closed a deal <strong class="text-slate-900 dark:text-white">Acme Corp</strong></p>
                                <span class="inline-block px-2 py-0.5 my-1 text-[10px] bg-blue-500/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 rounded-md font-medium">$4,500</span>
                                <p class="text-[10px] text-slate-400 dark:text-slate-500"><i class="fa-regular fa-clock"></i> 2 hours ago</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <img src="https://i.pravatar.cc/100?img=60" class="w-8 h-8 rounded-full border border-slate-200 dark:border-cardBorder">
                            <div>
                                <p class="text-slate-600 dark:text-slate-300"><strong class="text-slate-900 dark:text-white">Jackson Lee</strong> added a new contact <strong class="text-slate-900 dark:text-white">Sarah Williams</strong></p>
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1"><i class="fa-regular fa-clock"></i> 4 hours ago</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <img src="https://i.pravatar.cc/100?img=47" class="w-8 h-8 rounded-full border border-slate-200 dark:border-cardBorder">
                            <div>
                                <p class="text-slate-600 dark:text-slate-300"><strong class="text-slate-900 dark:text-white">Isabella Nguyen</strong> sent an email campaign <strong class="text-slate-900 dark:text-white">Q3 Newsletter</strong></p>
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1"><i class="fa-regular fa-clock"></i> 5 hours ago</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <img src="https://i.pravatar.cc/100?img=11" class="w-8 h-8 rounded-full border border-slate-200 dark:border-cardBorder">
                            <div>
                                <p class="text-slate-600 dark:text-slate-300"><strong class="text-slate-900 dark:text-white">William Kim</strong> lost an opportunity <strong class="text-slate-900 dark:text-white">TechFlow Inc</strong></p>
                                <span class="inline-block px-2 py-0.5 my-1 text-[10px] bg-rose-500/10 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 rounded-md font-medium">$12,000</span>
                                <p class="text-[10px] text-slate-400 dark:text-slate-500"><i class="fa-regular fa-clock"></i> Yesterday</p>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="w-full mt-4 py-2 border border-slate-200 dark:border-cardBorder text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white text-xs font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                    View All Activity
                </button>
            </div>
        </div>

        <!-- Bottom Section: Appointments + Team Status -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Upcoming Appointments -->
            <div class="bg-white dark:bg-cardBg border border-slate-200 dark:border-cardBorder p-5 rounded-xl shadow-sm dark:shadow-none">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900 dark:text-white">Upcoming Appointments</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Your schedule for the next few days</p>
                    </div>
                    <button class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-cardBorder text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white text-xs px-3 py-1.5 rounded-lg flex items-center gap-1.5">
                        <i class="fa-regular fa-calendar"></i> View Calendar
                    </button>
                </div>

                <div class="space-y-3">
                    <div class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-cardBorder p-3 rounded-lg flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-500 dark:text-blue-400 flex items-center justify-center text-xs">
                                <i class="fa-solid fa-video"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-semibold text-slate-900 dark:text-white">Product Demo</h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Sarah Williams</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-semibold text-slate-900 dark:text-white">10:00 AM</p>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500">Today • 45m</p>
                        </div>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-cardBorder p-3 rounded-lg flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-500 dark:text-blue-400 flex items-center justify-center text-xs">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-semibold text-slate-900 dark:text-white">Onboarding Session</h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">John Doe</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-semibold text-slate-900 dark:text-white">1:00 PM</p>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500">Today • 1h</p>
                        </div>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-cardBorder p-3 rounded-lg flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-500 dark:text-blue-400 flex items-center justify-center text-xs">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-semibold text-slate-900 dark:text-white">Quarterly Review</h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Emma Smith</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-semibold text-slate-900 dark:text-white">3:30 PM</p>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500">Tomorrow • 30m</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Status & Audit -->
            <div class="bg-white dark:bg-cardBg border border-slate-200 dark:border-cardBorder p-5 rounded-xl shadow-sm dark:shadow-none">
                <div class="mb-4">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white">Team Status & Audit</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">See who is online and track recent logins</p>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <img src="https://i.pravatar.cc/100?img=12" class="w-9 h-9 rounded-full">
                                <span class="w-2.5 h-2.5 bg-emerald-500 border-2 border-white dark:border-cardBg rounded-full absolute bottom-0 right-0"></span>
                            </div>
                            <div>
                                <h4 class="text-xs font-semibold text-slate-900 dark:text-white">Sarah Connor</h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Admin</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-medium text-emerald-500 dark:text-emerald-400">Online</p>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500"><i class="fa-regular fa-clock"></i> Just now</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <img src="https://i.pravatar.cc/100?img=33" class="w-9 h-9 rounded-full">
                                <span class="w-2.5 h-2.5 bg-emerald-500 border-2 border-white dark:border-cardBg rounded-full absolute bottom-0 right-0"></span>
                            </div>
                            <div>
                                <h4 class="text-xs font-semibold text-slate-900 dark:text-white">John Smith</h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Sales Rep</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-medium text-emerald-500 dark:text-emerald-400">Online</p>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500"><i class="fa-regular fa-clock"></i> Just now</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <img src="https://i.pravatar.cc/100?img=44" class="w-9 h-9 rounded-full">
                                <span class="w-2.5 h-2.5 bg-slate-400 border-2 border-white dark:border-cardBg rounded-full absolute bottom-0 right-0"></span>
                            </div>
                            <div>
                                <h4 class="text-xs font-semibold text-slate-900 dark:text-white">Emily Davis</h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Manager</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-medium text-slate-400">Offline</p>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500"><i class="fa-regular fa-clock"></i> 2 hours ago</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <img src="https://i.pravatar.cc/100?img=68" class="w-9 h-9 rounded-full">
                                <span class="w-2.5 h-2.5 bg-amber-500 border-2 border-white dark:border-cardBg rounded-full absolute bottom-0 right-0"></span>
                            </div>
                            <div>
                                <h4 class="text-xs font-semibold text-slate-900 dark:text-white">Michael Chang</h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Support</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-medium text-amber-500 dark:text-amber-400">Away</p>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500"><i class="fa-regular fa-clock"></i> 15 mins ago</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- Theme Switcher & Chart Script -->
    <script>
        // --- 1. Theme Toggle Logic ---
        const themeToggleBtn = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');

        function updateIcon() {
            if (document.documentElement.classList.contains('dark')) {
                themeIcon.className = 'fa-solid fa-sun text-amber-400';
            } else {
                themeIcon.className = 'fa-solid fa-moon text-slate-600';
            }
        }

        updateIcon();

        themeToggleBtn.addEventListener('click', () => {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
            updateIcon();
            updateChartColors();
        });

        // --- 2. Dynamic Chart JS Setup ---
        const ctx = document.getElementById('revenueChart').getContext('2d');
        let chart;

        function getChartColors() {
            const isDark = document.documentElement.classList.contains('dark');
            return {
                gridColor: isDark ? '#1A2337' : '#e2e8f0',
                tickColor: isDark ? '#64748B' : '#94a3b8'
            };
        }

        function initChart() {
            const colors = getChartColors();
            chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        data: [5100, 4800, 5200, 5900, 1200, 3200, 4500, 3400, 4800, 5900, 5100, 4900],
                        backgroundColor: '#5B9BF6',
                        borderRadius: 4,
                        barThickness: 28,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: colors.tickColor, font: { size: 10 } }
                        },
                        y: {
                            grid: { color: colors.gridColor },
                            ticks: {
                                color: colors.tickColor,
                                font: { size: 10 },
                                callback: (value) => '$' + value
                            },
                            min: 0,
                            max: 6000,
                            stepSize: 1500
                        }
                    }
                }
            });
        }

        function updateChartColors() {
            if (!chart) return;
            const colors = getChartColors();
            chart.options.scales.x.ticks.color = colors.tickColor;
            chart.options.scales.y.ticks.color = colors.tickColor;
            chart.options.scales.y.grid.color = colors.gridColor;
            chart.update();
        }

        initChart();
    </script>
</body>
</html>