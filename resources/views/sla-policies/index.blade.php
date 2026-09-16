<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ __('SLA Policies') }}</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Response & resolution targets per priority, plus breach escalation.') }}</p>
    </div>

    <div class="max-w-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('sla-policies.update') }}">
            @csrf
            <table class="w-full text-sm mb-5">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="text-left py-2 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">{{ __('Priority') }}</th>
                        <th class="text-left py-2 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">{{ __('Response (hrs)') }}</th>
                        <th class="text-left py-2 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">{{ __('Resolution (hrs)') }}</th>
                        <th class="text-left py-2 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">{{ __('Escalate on breach') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($priorities as $p)
                    @php $policy = $policies->get($p); @endphp
                    <tr>
                        <td class="py-3 font-medium text-slate-700 dark:text-slate-200 capitalize">{{ __(ucfirst($p)) }}</td>
                        <td class="py-3">
                            <input type="number" name="priority[{{ $p }}][response_hours]" value="{{ $policy->response_hours ?? match($p){'critical'=>1,'high'=>2,'medium'=>8,'low'=>24} }}" min="1" max="720" required class="w-24 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-sm bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                        </td>
                        <td class="py-3">
                            <input type="number" name="priority[{{ $p }}][resolution_hours]" value="{{ $policy->resolution_hours ?? match($p){'critical'=>2,'high'=>4,'medium'=>24,'low'=>72} }}" min="1" max="2160" required class="w-24 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-sm bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                        </td>
                        <td class="py-3">
                            <input type="checkbox" name="priority[{{ $p }}][escalate_on_breach]" value="1" {{ ($policy->escalate_on_breach ?? true) ? 'checked' : '' }} class="rounded border-slate-300 dark:border-slate-700 dark:bg-slate-800 text-indigo-600 focus:ring-indigo-500">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="text-xs text-slate-400 dark:text-slate-500 mb-5 leading-relaxed">{{ __('A ticket\'s due date is set from the Resolution target when created. A background check runs every 15 minutes and notifies admins + the assignee when a ticket breaches its due date, if escalation is enabled for that priority.') }}</p>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors shadow-sm">{{ __('Save Policies') }}</button>
        </form>
    </div>
</x-app-layout>
