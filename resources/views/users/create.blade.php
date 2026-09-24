<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ __('Create User') }}</h1>
    </div>
    <div class="max-w-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6">
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
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Password') }}</label>
                <input type="password" name="password" required minlength="8" class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
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
</x-app-layout>
