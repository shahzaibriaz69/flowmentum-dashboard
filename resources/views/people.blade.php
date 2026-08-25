<x-app-layout>
    <div class="flex items-start justify-between gap-4 mb-7">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">People</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage your contacts and customer relationships.</p>
        </div>
        <div class="flex gap-2">
            <button class="h-10 px-4 bg-white dark:bg-transparent border border-slate-200 dark:border-cardBorder hover:bg-slate-100 dark:hover:bg-white/[0.04] text-slate-700 dark:text-slate-200 rounded-xl text-sm font-medium transition-colors"><i class="fa-solid fa-filter mr-3"></i>Filter</button>
            <button class="h-10 px-4 bg-[#5b9bf6] hover:bg-[#6ca7fa] text-white dark:text-[#06101f] rounded-xl text-sm font-medium transition-colors"><i class="fa-solid fa-plus mr-3"></i>Add Person</button>
        </div>
    </div>

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
</x-app-layout>