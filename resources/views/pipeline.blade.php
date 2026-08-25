<x-app-layout>
    <!-- Top Bar Header & Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-7">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Pipeline</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage your deals and opportunities.</p>
        </div>

        <div class="flex items-center gap-3">
            <!-- Pipeline Select Dropdown -->
            @if(isset($pipelines) && $pipelines->isNotEmpty())
                <div class="flex items-center gap-2">
                    <label for="pipeline_select" class="text-sm font-medium text-slate-600 dark:text-slate-400 hidden sm:inline">Pipeline:</label>
                    <select 
                        id="pipeline_select" 
                        onchange="window.location.href='?pipeline_id=' + this.value"
                        class="h-10 pl-3 pr-8 bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/50 shadow-sm"
                    >
                        @foreach($pipelines as $p)
                            <option value="{{ $p->ghl_pipeline_id }}" {{ (isset($selectedPipelineId) && $selectedPipelineId == $p->ghl_pipeline_id) ? 'selected' : '' }}>
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <button class="h-10 px-4 bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors shadow-sm">
                Filter
            </button>
            <button class="h-10 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition-colors shadow-sm">
                + New Deal
            </button>
        </div>
    </div>

    <!-- Dynamic Flowmentum Stages Kanban Columns -->
    @if(isset($activePipeline) && $activePipeline)
        <div class="flex gap-5 overflow-x-auto pb-6 items-start">
            @forelse($activePipeline->stages as $stage)
                @php
                    $totalStageValue = $stage->opportunities->sum('monetary_value');
                    $colors = ['#3b82f6', '#f59e0b', '#a855f7', '#10b981', '#ec4899', '#6366f1'];
                    $stageColor = $colors[$loop->index % count($colors)];
                @endphp

                <div class="w-[300px] min-w-[300px] bg-slate-100/70 dark:bg-[#0b1329] border border-slate-200/80 dark:border-slate-800/60 rounded-2xl p-3.5 flex flex-col shrink-0">
                    
                    <!-- Stage Category Header -->
                    <div class="flex items-center justify-between mb-4 px-1">
                        <div class="flex items-center gap-2 truncate">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $stageColor }}"></span>
                            <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200 truncate">{{ $stage->name }}</h3>
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 bg-slate-200/60 dark:bg-slate-800 px-2 py-0.5 rounded-full">
                                {{ $stage->opportunities->count() }}
                            </span>
                        </div>
                        <span class="text-xs font-bold text-slate-600 dark:text-slate-300 shrink-0 ml-2">
                            ${{ number_format($totalStageValue) }}
                        </span>
                    </div>

                    <!-- Opportunities Card List -->
                    <div class="space-y-3 flex-1 overflow-y-auto max-h-[calc(100vh-250px)] pr-0.5">
                        @forelse($stage->opportunities as $opp)
                            <article class="relative bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800/80 rounded-xl p-4 shadow-sm hover:shadow-md transition-all overflow-hidden" style="border-left: 4px solid {{ $stageColor }}">
                                
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 truncate">
                                        {{ $opp->company_name ?? 'Client' }}
                                    </span>
                                </div>

                                <h4 class="font-bold text-sm text-slate-900 dark:text-white mb-4 truncate">
                                    {{ $opp->name }}
                                </h4>

                                <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-100 dark:border-slate-800/60 text-slate-500 dark:text-slate-400">
                                    <div class="flex items-center gap-1.5">
                                        <span>{{ $opp->created_at ? $opp->created_at->format('M d') : 'Oct 12' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-900 dark:text-white">
                                            ${{ number_format($opp->monetary_value ?? 0) }}
                                        </span>
                                    </div>
                                </div>

                            </article>
                        @empty
                            <div class="text-xs text-slate-400 dark:text-slate-500 text-center py-8 border border-dashed border-slate-200 dark:border-slate-800 rounded-xl">
                                No deals in this stage
                            </div>
                        @endforelse
                    </div>

                </div>
            @empty
                <div class="w-full bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-800 rounded-2xl p-10 text-center text-slate-500">
                    No stages configured for this pipeline.
                </div>
            @endforelse
        </div>
    @else
        <div class="bg-white dark:bg-cardBg border border-slate-200 dark:border-cardBorder rounded-xl p-6 min-h-[400px] flex items-center justify-center">
            <p class="text-slate-500 dark:text-slate-400">No pipeline records found in database.</p>
        </div>
    @endif
</x-app-layout>