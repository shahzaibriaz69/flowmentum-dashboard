<x-dashboard-shell>
    <div class="space-y-6 text-slate-800 dark:text-slate-100">
        
        <!-- Top Bar Header & Controls -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-2">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Pipeline</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Manage your deals and opportunities.</p>
            </div>

            <div class="flex items-center gap-3">
                <!-- Dropdown Form -->
                @if(isset($pipelines) && count($pipelines) > 0)
                    <form method="GET" action="{{ route('pipeline') }}" class="flex items-center gap-2">
                        <label for="pipeline_select" class="text-xs font-semibold text-slate-500 dark:text-slate-400 hidden sm:inline">Pipeline:</label>
                        <select 
                            id="pipeline_select" 
                            name="pipeline_id"
                            onchange="this.form.submit()"
                            class="bg-white dark:bg-[#161c28] border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-200 text-xs font-semibold rounded-xl px-3 py-2.5 focus:ring-1 focus:ring-blue-500 focus:outline-none cursor-pointer shadow-sm min-w-[200px]"
                        >
                            @foreach($pipelines as $p)
                                <option value="{{ $p->ghl_pipeline_id }}" {{ (isset($selectedPipelineId) && $selectedPipelineId == $p->ghl_pipeline_id) ? 'selected' : '' }}>
                                    {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @endif

                <button class="px-3.5 py-2 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#161c28] hover:bg-slate-100 dark:hover:bg-slate-800 transition shadow-sm">
                    Filter
                </button>
                <button class="px-3.5 py-2 text-xs font-semibold rounded-xl bg-blue-600 hover:bg-blue-500 text-white transition shadow-sm">
                    + New Deal
                </button>
            </div>
        </div>

        @php
            $pipelineData = $activePipeline ?? $currentPipeline ?? null;
        @endphp

        <!-- Kanban Board Columns -->
        @if($pipelineData && count($pipelineData->stages) > 0)
            @php
                $colors = ['#3b82f6', '#f59e0b', '#a855f7', '#10b981', '#ec4899', '#6366f1'];
            @endphp

            <div class="flex gap-4 overflow-x-auto pb-6 items-start">
                @foreach($pipelineData->stages as $index => $stage)
                    @php
                        $stageOpportunities = $stage->opportunities ?? collect();
                        $totalStageValue = $stageOpportunities->sum('monetary_value');
                        $stageColor = $colors[$index % count($colors)];
                    @endphp

                    <!-- Stage Column -->
                    <div class="w-72 min-w-[280px] bg-slate-100/70 dark:bg-[#111622] border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-3 flex flex-col shrink-0">
                        
                        <!-- Stage Header -->
                        <div class="flex items-center justify-between mb-3 px-1">
                            <div class="flex items-center gap-2 truncate">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $stageColor }}"></span>
                                <h3 class="font-bold text-xs text-slate-800 dark:text-slate-200 truncate max-w-[130px]">{{ $stage->name }}</h3>
                                <span class="text-[10px] font-bold text-slate-600 dark:text-slate-400 bg-slate-200/80 dark:bg-slate-800 px-1.5 py-0.5 rounded">
                                    {{ $stageOpportunities->count() }}
                                </span>
                            </div>
                            <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 shrink-0">
                                ${{ number_format((float)$totalStageValue) }}
                            </span>
                        </div>

                        <!-- Card List -->
                        <div class="space-y-2.5 flex-1 min-h-[180px]">
                            @forelse($stageOpportunities as $opp)
                                <article class="relative bg-white dark:bg-[#161c2b] border-t border-r border-b border-slate-200/90 dark:border-slate-800/80 rounded-xl p-3.5 shadow-sm hover:shadow-md transition-all cursor-pointer group" style="border-left: 4px solid {{ $stageColor }}">
                                    
                                    <div class="flex items-center justify-between gap-2 mb-1">
                                        <span class="text-[10px] font-semibold text-slate-400 dark:text-slate-500 truncate">
                                            {{ $opp->contact->company ?? $opp->contact->name ?? 'Lead' }}
                                        </span>
                                    </div>

                                    <h4 class="font-bold text-xs text-slate-900 dark:text-white mb-3 truncate">
                                        {{ $opp->name }}
                                    </h4>

                                    <div class="flex items-center justify-between text-[11px] pt-2 border-t border-slate-100 dark:border-slate-800/60 text-slate-400 dark:text-slate-500">
                                        <div class="flex items-center gap-1">
                                            <span>{{ $opp->created_at ? $opp->created_at->format('M d') : 'Oct 12' }}</span>
                                        </div>
                                        <span class="font-extrabold text-slate-800 dark:text-slate-100">
                                            ${{ number_format((float)($opp->monetary_value ?? 0)) }}
                                        </span>
                                    </div>

                                </article>
                            @empty
                                <div class="text-[11px] text-slate-400 dark:text-slate-500 text-center py-8 border border-dashed border-slate-200 dark:border-slate-800/80 rounded-xl">
                                    No deals
                                </div>
                            @endforelse
                        </div>

                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white dark:bg-[#161c28] border border-slate-200 dark:border-slate-800 rounded-xl p-12 text-center text-slate-500 dark:text-slate-400">
                <p class="text-sm font-medium">No pipeline stages found.</p>
            </div>
        @endif

    </div>
</x-dashboard-shell>