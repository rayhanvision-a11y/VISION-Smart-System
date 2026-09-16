<?php

namespace App\Http\Controllers;

use App\Models\SlaPolicy;
use Illuminate\Http\Request;

class SlaPolicyController extends Controller
{
    public function index()
    {
        if (!auth()->user()->isAdmin()) abort(403);

        $priorities = ['low', 'medium', 'high', 'critical'];
        $policies = SlaPolicy::whereIn('priority', $priorities)->get()->keyBy('priority');

        return view('sla-policies.index', compact('priorities', 'policies'));
    }

    public function update(Request $request)
    {
        if (!auth()->user()->isAdmin()) abort(403);

        $validated = $request->validate([
            'priority'           => 'required|array',
            'priority.*.response_hours'   => 'required|integer|min:1|max:720',
            'priority.*.resolution_hours' => 'required|integer|min:1|max:2160',
            'priority.*.escalate_on_breach' => 'nullable|boolean',
        ]);

        foreach ($validated['priority'] as $priority => $data) {
            SlaPolicy::updateOrCreate(
                ['priority' => $priority],
                [
                    'response_hours'      => $data['response_hours'],
                    'resolution_hours'    => $data['resolution_hours'],
                    'escalate_on_breach'  => isset($data['escalate_on_breach']),
                ]
            );
        }

        return back()->with('success', 'SLA policies updated.');
    }
}
