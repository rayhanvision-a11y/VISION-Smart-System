<x-app-layout>
    <div class="max-w-xl mx-auto">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ __('Create User') }}</h1>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Profile Photo (optional)') }}</label>
                <input type="file" name="avatar" accept="image/*"
                       class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-950/60 file:text-indigo-700 dark:file:text-indigo-300 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50">
                @error('avatar')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Name') }}</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Email') }}</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Password (optional)') }}</label>
                <input type="password" name="password" minlength="8" class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500" placeholder="{{ __('Leave blank — reset link will be emailed') }}">
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">{{ __('If left blank, a password reset link will be emailed to the user.') }}</p>
                @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Role') }}</label>
                <select name="role" required class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                    <option value="reseller" {{ old('role') === 'reseller' ? 'selected' : '' }}>{{ __('Reseller') }}</option>
                    <option value="technician" {{ old('role') === 'technician' ? 'selected' : '' }}>{{ __('Technician') }}</option>
                    <option value="call_center" {{ old('role') === 'call_center' ? 'selected' : '' }}>{{ __('Call Center') }}</option>
                    <option value="supervisor" {{ old('role') === 'supervisor' ? 'selected' : '' }}>{{ __('Supervisor') }}</option>
                    <option value="senior_supervisor" {{ old('role') === 'senior_supervisor' ? 'selected' : '' }}>{{ __('Senior Supervisor') }}</option>
                    <option value="noc"      {{ old('role') === 'noc'      ? 'selected' : '' }}>{{ __('NOC') }}</option>
                    <option value="admin"    {{ old('role') === 'admin'    ? 'selected' : '' }}>{{ __('Admin') }}</option>
                    @if(auth()->user()->isSuperAdmin())
                    <option value="super_admin" {{ old('role') === 'super_admin' ? 'selected' : '' }}>{{ __('Super Admin') }}</option>
                    @endif
                </select>
                @error('role')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Designation') }}</label>
                <input type="text" name="designation" value="{{ old('designation') }}" list="designation-list"
                       placeholder="{{ __('e.g. Senior Technician, Field Engineer') }}"
                       class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                <datalist id="designation-list">
                    @foreach(($designations ?? []) as $d)
                        <option value="{{ $d }}">
                    @endforeach
                </datalist>
                @error('designation')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Office ID') }}</label>
                <input type="text" name="office_id" value="{{ old('office_id') }}"
                       placeholder="{{ __('e.g. VTL-EMP-0012') }}"
                       class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                @error('office_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('POP Office') }}</label>
                <select name="pop_office_id" class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                    <option value="">{{ __('None') }}</option>
                    @foreach(($offices ?? []) as $o)
                        <option value="{{ $o->id }}" {{ (string) old('pop_office_id') === (string) $o->id ? 'selected' : '' }}>🏢 {{ $o->name }}</option>
                    @endforeach
                </select>
                @error('pop_office_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Team Tag') }}</label>
                <select name="team" class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                    <option value="">{{ __('None / No Team Tag') }}</option>
                    @php $teams = \Schema::hasTable('teams') ? \App\Models\Team::where('is_active', true)->orderBy('name')->pluck('name') : collect(array_keys(\App\Models\User::TEAMS)); @endphp
                    @foreach($teams as $tName)
                    <option value="{{ $tName }}" {{ old('team') === $tName ? 'selected' : '' }}>🏷️ {{ $tName }}</option>
                    @endforeach
                </select>
                @error('team')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-5">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Phone (optional)') }}</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500" placeholder="+8801XXXXXXXXX">
            </div>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors shadow-sm">{{ __('Create User') }}</button>
            <a href="{{ route('users.index') }}" class="ml-3 text-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">{{ __('Cancel') }}</a>
        </form>
        </div>
    </div>
</x-app-layout>
