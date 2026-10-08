<x-app-layout>
    @php
        $user = auth()->user();
        $carbonDate = \Carbon\Carbon::parse($selectedDate);
        $prevDate = $carbonDate->copy()->subDay()->format('Y-m-d');
        $nextDate = $carbonDate->copy()->addDay()->format('Y-m-d');
        $isToday = $selectedDate === now()->format('Y-m-d');

        $totalTeams = $teams->count();
        $complainCount = $groupedTeams->get('complain', collect())->count();
        $newConnCount = $groupedTeams->get('new_connection', collect())->count();
        $lineTransCount = $groupedTeams->get('line_transfer', collect())->count();
        $assignedStaffIds = $teams->flatMap(fn($t) => [$t->leader_id, $t->member_1_id, $t->member_2_id])->filter()->unique();
        $assignedStaffCount = $assignedStaffIds->count();
    @endphp

    <div x-data="{
        showModal: {{ $errors->any() ? 'true' : 'false' }},
        isEdit: {{ old('_method') === 'PUT' ? 'true' : 'false' }},
        editActionUrl: '{{ old('_method') === 'PUT' ? session('edit_action_url', route('technician-teams.store')) : route('technician-teams.store') }}',
        form: {
            duty_date: '{{ old('duty_date', $selectedDate) }}',
            category: '{{ old('category', 'complain') }}',
            team_name: '{{ old('team_name', '') }}',
            leader_id: '{{ old('leader_id', '') }}',
            member_1_id: '{{ old('member_1_id', '') }}',
            member_2_id: '{{ old('member_2_id', '') }}',
            area: '{{ old('area', '') }}',
            vehicle_no: '{{ old('vehicle_no', '') }}',
            notes: '{{ old('notes', '') }}'
        },
        openCreate(cat = 'complain') {
            this.isEdit = false;
            this.editActionUrl = '{{ route('technician-teams.store') }}';
            this.form = {
                duty_date: '{{ $selectedDate }}',
                category: (cat === 'transfer' ? 'line_transfer' : cat),
                team_name: '',
                leader_id: '',
                member_1_id: '',
                member_2_id: '',
                area: '',
                vehicle_no: '',
                notes: ''
            };
            this.showModal = true;
        },
        openEdit(t) {
            this.isEdit = true;
            this.editActionUrl = '/technician-teams/' + t.id;
            this.form = {
                duty_date: t.duty_date ? t.duty_date.substring(0, 10) : '{{ $selectedDate }}',
                category: t.category,
                team_name: t.team_name || '',
                leader_id: t.leader_id || '',
                member_1_id: t.member_1_id || '',
                member_2_id: t.member_2_id || '',
                area: t.area || '',
                vehicle_no: t.vehicle_no || '',
                notes: t.notes || ''
            };
            this.showModal = true;
        }
    }">
        {{-- Header & Date Navigation --}}
        <div class="mb-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-2xl">👷‍♂️</span>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ __('Daily Technician Teams') }}</h1>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    {{ __('Manage field squads (1 Leader + optional Members) for Complain, New Connection, and Transfer teams.') }}
                </p>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                {{-- Date Navigation --}}
                <div class="inline-flex items-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-1 shadow-sm">
                    <a href="{{ route('technician-teams.index', ['date' => $prevDate]) }}"
                       title="{{ __('Previous Day') }}"
                       class="p-2 rounded-lg text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>

                    <form method="GET" action="{{ route('technician-teams.index') }}" class="flex items-center px-1">
                        <input type="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()"
                               class="text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 bg-transparent border-0 focus:ring-0 cursor-pointer py-1 px-2">
                    </form>

                    <a href="{{ route('technician-teams.index', ['date' => $nextDate]) }}"
                       title="{{ __('Next Day') }}"
                       class="p-2 rounded-lg text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>

                    @if(!$isToday)
                    <a href="{{ route('technician-teams.index', ['date' => now()->format('Y-m-d')]) }}"
                       class="ml-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors">
                        {{ __('Today') }}
                    </a>
                    @endif
                </div>

                @if($user->isAdmin() || $user->isSupervisorLevel() || $user->isNoc())
                <button type="button" @click="openCreate('complain')"
                        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors shadow-sm cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('Create Team') }}
                </button>
                @endif
            </div>
        </div>

        {{-- Validation Errors Banner --}}
        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-sm font-medium">
                <div class="font-bold flex items-center gap-2 mb-1">
                    <span>⚠️</span>
                    <span>{{ __('Could not save team. Please review the errors below:') }}</span>
                </div>
                <ul class="list-disc list-inside text-xs space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm font-medium flex items-center justify-between">
                <span>{{ session('success') }}</span>
                <span class="text-xs opacity-75">🔔 Notifications dispatched</span>
            </div>
        @endif

        {{-- Quick Stats Row --}}
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5 mb-2.5">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-2.5 shadow-sm">
                <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">{{ __('Total Teams') }}</p>
                <p class="text-xl font-bold text-slate-800 dark:text-white mt-0.5">{{ $totalTeams }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-2.5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-medium text-amber-600 dark:text-amber-400 uppercase tracking-wider">🛠️ {{ __('Complain') }}</p>
                </div>
                <p class="text-xl font-bold text-slate-800 dark:text-white mt-0.5">{{ $complainCount }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-2.5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-medium text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">🔌 {{ __('New Conn.') }}</p>
                </div>
                <p class="text-xl font-bold text-slate-800 dark:text-white mt-0.5">{{ $newConnCount }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-2.5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wider">🔄 {{ __('Line Transfer') }}</p>
                </div>
                <p class="text-xl font-bold text-slate-800 dark:text-white mt-0.5">{{ $lineTransCount }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-2.5 shadow-sm">
                <p class="text-[11px] font-medium text-purple-600 dark:text-purple-400 uppercase tracking-wider">👥 {{ __('Field Staff') }}</p>
                <p class="text-xl font-bold text-slate-800 dark:text-white mt-0.5">{{ $assignedStaffCount }}</p>
            </div>
        </div>

        {{-- Categories Section --}}
        @if($totalTeams === 0)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-12 text-center shadow-sm">
            <div class="w-16 h-16 mx-auto mb-4 bg-indigo-50 dark:bg-indigo-950/50 rounded-2xl flex items-center justify-center text-3xl">
                👷‍♂️
            </div>
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">
                {{ __('No technician teams assigned for :date', ['date' => $carbonDate->format('d M, Y')]) }}
            </h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto mt-1 mb-6">
                {{ __('Form 3-person daily teams (1 Leader + 2 Members) for Complain, New Connection, and Line Transfer teams.') }}
            </p>
            @if($user->isAdmin() || $user->isSupervisorLevel() || $user->isNoc())
            <div class="flex items-center justify-center gap-3 flex-wrap">
                <button type="button" @click="openCreate('complain')"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold bg-amber-500 hover:bg-amber-600 text-white transition-colors shadow-sm cursor-pointer">
                    <span>🛠️</span> {{ __('+ Complain Team') }}
                </button>
                <button type="button" @click="openCreate('new_connection')"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition-colors shadow-sm cursor-pointer">
                    <span>🔌</span> {{ __('+ New Connection Team') }}
                </button>
                <button type="button" @click="openCreate('line_transfer')"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white transition-colors shadow-sm cursor-pointer">
                    <span>🔄</span> {{ __('+ Transfer Team') }}
                </button>
            </div>
            @endif
        </div>
        @else

        <div class="space-y-2.5">
            @foreach(array_keys($categories) as $catKey)
            @php
                $catMeta = $categories[$catKey] ?? [
                    'label' => ucfirst(str_replace('_', ' ', $catKey)),
                    'icon' => '👷',
                    'badge' => 'bg-slate-100 text-slate-800',
                    'color' => '#64748b'
                ];
                $teamsInCat = $groupedTeams->get($catKey, collect());
            @endphp
            <div class="bg-slate-50/50 dark:bg-slate-900/40 border border-slate-200/80 dark:border-slate-800/80 rounded-xl p-3 sm:p-3.5">
                {{-- Section Header --}}
                <div class="flex items-center justify-between mb-2.5 pb-2 border-b border-slate-200/60 dark:border-slate-800/60">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">{{ $catMeta['icon'] }}</span>
                        <div>
                            <h2 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                {{ __($catMeta['label']) }}
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $catMeta['badge'] }} font-semibold">
                                    {{ $teamsInCat->count() }} {{ __('Team(s)') }}
                                </span>
                            </h2>
                            <p class="text-[11px] text-slate-400">
                                @if($catKey === 'complain')
                                    {{ __('Field squad for customer issues & outage fixes') }}
                                @elseif($catKey === 'new_connection')
                                    {{ __('Deployment squad for new line setups') }}
                                @else
                                    {{ __('Squad for line shifts and transfer requests') }}
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($user->isAdmin() || $user->isSupervisorLevel() || $user->isNoc())
                    <button type="button" @click="openCreate('{{ $catKey }}')"
                            class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                        <span>+</span> {{ __('Add Team') }}
                    </button>
                    @endif
                </div>

                @if($teamsInCat->isEmpty())
                <div class="py-4 text-center text-xs text-slate-400 dark:text-slate-500">
                    {{ __('No :category teams assigned for this day.', ['category' => __($catMeta['label'])]) }}
                </div>
                @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2.5">
                    @foreach($teamsInCat as $team)
                    @php
                        $leader = $team->leader;
                        $m1 = $team->member1;
                        $m2 = $team->member2;
                        $teamTitle = $team->team_name ?: ($catMeta['label'] ?? __('Team')) . ' #' . $loop->iteration;
                    @endphp
                    <div class="bg-white dark:bg-[#131b2e] border border-slate-200 dark:border-slate-800/90 rounded-xl p-2.5 shadow-xs hover:border-indigo-400/50 dark:hover:border-indigo-500/50 transition-all flex flex-col justify-between">
                        <div>
                            {{-- Top Category Pill & Area --}}
                            <div class="flex items-center justify-between gap-1 mb-1.5">
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-black border {{ $catMeta['badge'] ?? 'bg-amber-500/10 text-amber-500 border-amber-500/30' }}">
                                    {{ __($catMeta['label']) }}
                                </span>
                                @if($team->area)
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 truncate font-medium">
                                    📍 {{ $team->area }}
                                </span>
                                @endif
                            </div>

                            <h4 class="text-xs font-black text-slate-900 dark:text-white mb-2 truncate">
                                {{ $teamTitle }}
                            </h4>

                            {{-- 3 Member Compact Boxes --}}
                            <div class="space-y-2.5">
                                {{-- Team Leader Box --}}
                                <div class="rounded-xl border border-amber-400/60 dark:border-amber-400/70 bg-amber-50/50 dark:bg-amber-950/30 px-2.5 py-1.5 flex items-center justify-between shadow-2xs">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="text-sm flex-shrink-0">👑</span>
                                        <div class="min-w-0 leading-tight">
                                            <div class="text-[10px] font-bold text-amber-600 dark:text-amber-400 leading-none mb-0.5">{{ __('Leader') }}</div>
                                            <div class="text-xs font-bold text-slate-800 dark:text-white truncate">{{ $leader?->name ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                    @if($leader?->phone)
                                    <a href="tel:{{ $leader->phone }}" class="p-1 rounded bg-white/80 dark:bg-slate-800 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-slate-700 shadow-xs flex-shrink-0 ml-1" title="{{ __('Call') }}: {{ $leader->phone }}">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    </a>
                                    @endif
                                </div>

                                {{-- Member 1 Box --}}
                                <div class="rounded-xl border border-slate-200 dark:border-slate-700/80 bg-slate-50 dark:bg-slate-800/70 px-2.5 py-1.5 flex items-center justify-between shadow-2xs">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="w-5 h-5 rounded-full bg-indigo-100 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 text-[10px] font-bold flex items-center justify-center flex-shrink-0">{{ app()->getLocale() === 'bn' ? '১' : '1' }}</span>
                                        <span class="text-xs font-medium text-slate-800 dark:text-slate-200 truncate">{{ $m1?->name ?? __('Unassigned Member') }}</span>
                                    </div>
                                    @if($m1?->phone)
                                    <a href="tel:{{ $m1->phone }}" class="p-1 rounded bg-white/80 dark:bg-slate-800 text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 flex-shrink-0 ml-1" title="{{ __('Call') }}: {{ $m1->phone }}">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    </a>
                                    @endif
                                </div>

                                {{-- Member 2 Box --}}
                                <div class="rounded-xl border border-slate-200 dark:border-slate-700/80 bg-slate-50 dark:bg-slate-800/70 px-2.5 py-1.5 flex items-center justify-between shadow-2xs">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="w-5 h-5 rounded-full bg-indigo-100 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 text-[10px] font-bold flex items-center justify-center flex-shrink-0">{{ app()->getLocale() === 'bn' ? '২' : '2' }}</span>
                                        <span class="text-xs font-medium text-slate-800 dark:text-slate-200 truncate">{{ $m2?->name ?? __('Unassigned Member') }}</span>
                                    </div>
                                    @if($m2?->phone)
                                    <a href="tel:{{ $m2->phone }}" class="p-1 rounded bg-white/80 dark:bg-slate-800 text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 flex-shrink-0 ml-1" title="{{ __('Call') }}: {{ $m2->phone }}">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    </a>
                                    @endif
                                </div>
                            </div>

                            @if($team->vehicle_no || $team->notes)
                            <div class="flex items-center justify-between text-[10px] text-slate-500 dark:text-slate-400 pt-1.5 mt-1.5 border-t border-slate-100 dark:border-slate-800/60">
                                @if($team->vehicle_no)
                                    <span>🛵 {{ $team->vehicle_no }}</span>
                                @endif
                                @if($team->notes)
                                    <span class="truncate ml-auto italic" title="{{ $team->notes }}">📝 {{ $team->notes }}</span>
                                @endif
                            </div>
                            @endif
                        </div>

                        {{-- Footer Actions --}}
                        @if($user->isAdmin() || $user->isSupervisorLevel() || $user->isNoc())
                        <div class="flex items-center justify-end gap-1 pt-1.5 mt-1.5 border-t border-slate-100 dark:border-slate-800">
                            <button type="button" @click='openEdit(@json($team))'
                                    class="text-[10px] font-semibold text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 px-1.5 py-0.5 rounded hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                                {{ __('Edit') }}
                            </button>
                            <form method="POST" action="{{ route('technician-teams.destroy', $team) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this team?') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-[10px] font-semibold text-red-500 hover:text-red-700 px-1.5 py-0.5 rounded hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors cursor-pointer">
                                    {{ __('Delete') }}
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        {{-- Create / Edit Team Modal --}}
        <div x-show="showModal" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showModal = false"
                 class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden max-h-[92vh] overflow-y-auto my-auto">
                {{-- Modal Header --}}
                <div class="px-6 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between sticky top-0 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md z-10" style="padding-top: 18px; padding-bottom: 18px;">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg border border-indigo-500/20 shrink-0">🛵</span>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight"
                                x-text="isEdit ? '{{ __('Edit Technician Team') }}' : '{{ __('Create 3-Person Technician Team') }}'"></h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium leading-tight mt-1">
                                {{ __('Assign leader & members for today\'s field squad') }}
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="showModal = false"
                            class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Modal Form --}}
                <form :action="editActionUrl" method="POST" class="p-6 space-y-4">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide mb-1.5">{{ __('Duty Date') }} <span class="text-red-600 dark:text-red-400">*</span></label>
                            <input type="date" name="duty_date" x-model="form.duty_date" required
                                   class="w-full border border-slate-200 dark:border-slate-700/80 rounded-xl px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide mb-1.5">{{ __('Category') }} <span class="text-red-600 dark:text-red-400">*</span></label>
                            <select name="category" x-model="form.category" required
                                    class="w-full border border-slate-200 dark:border-slate-700/80 rounded-xl px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-medium transition-all">
                                @foreach($categories as $catKey => $catMeta)
                                    <option value="{{ $catKey }}">{{ $catMeta['icon'] ?? '👷' }} {{ __($catMeta['label']) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide">{{ __('Team / Group Name') }}</label>
                            <span class="text-[10px] text-slate-400 font-medium">{{ __('Auto-named if blank') }}</span>
                        </div>
                        <input type="text" name="team_name" x-model="form.team_name" placeholder="{{ __('e.g. Team #1, Group #1, New Connection Alpha') }}"
                               class="w-full border border-slate-200 dark:border-slate-700/80 rounded-xl px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                        <div class="flex items-center gap-1.5 flex-wrap mt-2">
                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ __('Quick Presets (1-10)') }}:</span>
                            @for($i = 1; $i <= 10; $i++)
                                <button type="button" @click="form.team_name = 'Team #' + {{ $i }}"
                                        class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 hover:bg-indigo-100 dark:bg-slate-800 dark:hover:bg-indigo-900/40 text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors border border-slate-200/60 dark:border-slate-700/60 cursor-pointer">
                                    #{{ $i }}
                                </button>
                            @endfor
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1">
                        <span class="font-bold text-slate-500 dark:text-slate-400">{{ __('Assign Staff Members') }}</span>
                        <a href="{{ route('users.create') }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline font-bold flex items-center gap-1 text-[11px]">
                            <span>+</span> {{ __('Register New Staff / Technician') }}
                        </a>
                    </div>

                    {{-- Member 1: Team Leader --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide mb-1.5 flex items-center gap-1">
                            <span>👑</span> {{ __('Team Leader') }} <span class="text-red-600 dark:text-red-400">*</span>
                        </label>
                        <select name="leader_id" x-model="form.leader_id" required
                                class="w-full border border-slate-200 dark:border-slate-700/80 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 font-medium transition-all">
                            <option value="">— {{ __('Select Team Leader') }} —</option>
                            @foreach($eligibleStaff as $s)
                            <option value="{{ $s->id }}">{{ $s->isOnDuty() ? '🟢' : '⚪' }} {{ $s->name }} ({{ ucfirst(str_replace('_',' ',$s->role)) }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Members 2 & 3: Side-by-side in 1 line --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide mb-1.5 flex items-center gap-1">
                                <span>👷</span> {{ __('Team Member 1') }}
                            </label>
                            <select name="member_1_id" x-model="form.member_1_id"
                                    class="w-full border border-slate-200 dark:border-slate-700/80 rounded-xl px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-medium transition-all">
                                <option value="">— {{ __('Select Member 1') }} —</option>
                                @foreach($eligibleStaff as $s)
                                <option value="{{ $s->id }}">{{ $s->isOnDuty() ? '🟢' : '⚪' }} {{ $s->name }} ({{ ucfirst(str_replace('_',' ',$s->role)) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide mb-1.5 flex items-center gap-1">
                                <span>👷</span> {{ __('Team Member 2') }}
                            </label>
                            <select name="member_2_id" x-model="form.member_2_id"
                                    class="w-full border border-slate-200 dark:border-slate-700/80 rounded-xl px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-medium transition-all">
                                <option value="">— {{ __('Select Member 2') }} —</option>
                                @foreach($eligibleStaff as $s)
                                <option value="{{ $s->id }}">{{ $s->isOnDuty() ? '🟢' : '⚪' }} {{ $s->name }} ({{ ucfirst(str_replace('_',' ',$s->role)) }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide mb-1.5 flex items-center gap-1">
                            <span>📍</span> {{ __('Assigned Area') }}
                        </label>
                        <input type="text" name="area" list="modal-areas-list" x-model="form.area" placeholder="{{ __('e.g. Shadhupara') }}"
                               class="w-full border border-slate-200 dark:border-slate-700/80 rounded-xl px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                        <datalist id="modal-areas-list">
                            @foreach($areas as $a)
                            <option value="{{ $a }}"></option>
                            @endforeach
                        </datalist>
                    </div>

                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-end gap-2.5">
                        <button type="button" @click="showModal = false"
                                class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-xs font-black text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm shadow-indigo-600/30 active:scale-95 transition-all cursor-pointer">
                            <span>🔔</span>
                            <span x-text="isEdit ? '{{ __('Update & Notify') }}' : '{{ __('Save & Notify Members') }}'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
