<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ __('Edit User:') }} {{ $editUser->name }}</h1>
    </div>
    <div class="max-w-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('users.update', $editUser) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            
            {{-- Profile Photo --}}
            <div class="mb-5 p-4 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl flex items-center gap-4">
                <img src="{{ $editUser->avatarUrl() }}" alt="{{ $editUser->name }}"
                     onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($editUser->name) }}&background=6366f1&color=ffffff&bold=true';"
                     class="w-16 h-16 rounded-full object-cover border-2 border-indigo-200 dark:border-indigo-800 shadow-2xs flex-shrink-0">
                <div class="flex-1 min-w-0">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Profile Photo') }}</label>
                    <input type="file" name="avatar" accept="image/*"
                           class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-950/60 file:text-indigo-700 dark:file:text-indigo-300 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50">
                    @error('avatar')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Name') }}</label>
                <input type="text" name="name" value="{{ old('name', $editUser->name) }}" required class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Email') }}</label>
                <input type="email" name="email" value="{{ old('email', $editUser->email) }}" required class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('New Password (leave blank to keep)') }}</label>
                <input type="password" name="password" minlength="8" class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Role') }}</label>
                <select name="role" required class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                    <option value="reseller" {{ old('role', $editUser->role) === 'reseller' ? 'selected' : '' }}>{{ __('Reseller') }}</option>
                    <option value="call_center" {{ old('role', $editUser->role) === 'call_center' ? 'selected' : '' }}>{{ __('Call Center') }}</option>
                    <option value="supervisor" {{ old('role', $editUser->role) === 'supervisor' ? 'selected' : '' }}>{{ __('Supervisor') }}</option>
                    <option value="senior_supervisor" {{ old('role', $editUser->role) === 'senior_supervisor' ? 'selected' : '' }}>{{ __('Senior Supervisor') }}</option>
                    <option value="noc"      {{ old('role', $editUser->role) === 'noc'      ? 'selected' : '' }}>{{ __('NOC') }}</option>
                    @if(auth()->user()->isSuperAdmin())
                    <option value="admin"       {{ old('role', $editUser->role) === 'admin'       ? 'selected' : '' }}>{{ __('Admin') }}</option>
                    <option value="super_admin" {{ old('role', $editUser->role) === 'super_admin' ? 'selected' : '' }}>{{ __('Super Admin') }}</option>
                    @endif
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Team Tag') }}</label>
                <select name="team" class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                    <option value="">{{ __('None / No Team Tag') }}</option>
                    @foreach(\App\Models\User::TEAMS as $tKey => $tName)
                    <option value="{{ $tKey }}" {{ old('team', $editUser->team) === $tKey ? 'selected' : '' }}>🏷️ {{ $tName }}</option>
                    @endforeach
                </select>
                @error('team')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Phone') }}</label>
                <input type="text" name="phone" value="{{ old('phone', $editUser->phone) }}" class="w-full border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500" placeholder="+8801XXXXXXXXX">
            </div>
            <div class="mb-5 flex items-center gap-3">
                <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('Active') }}</label>
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $editUser->is_active) ? 'checked' : '' }} class="rounded border-slate-300 dark:border-slate-700 dark:bg-slate-800 text-indigo-600 focus:ring-indigo-500">
            </div>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors shadow-sm">{{ __('Save Changes') }}</button>
            <a href="{{ route('users.index') }}" class="ml-3 text-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">{{ __('Cancel') }}</a>
        </form>
    </div>
</x-app-layout>
