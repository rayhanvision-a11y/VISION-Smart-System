<?php

namespace App\Http\Controllers;

use App\Models\PopOffice;
use Illuminate\Http\Request;

class PopOfficeController extends Controller
{
    private function superAdminOnly()
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403);
        }
    }

    private function adminOnly()
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }
    }

    public function index()
    {
        $this->adminOnly();
        $offices = PopOffice::withCount([
            'tickets as pending_count' => fn ($q) => $q->whereNotIn('status', ['resolved']),
            'tickets as total_count',
        ])->orderBy('name')->get();

        return view('pop-offices.index', compact('offices'));
    }

    public function store(Request $request)
    {
        $this->superAdminOnly();
        $request->validate([
            'name' => 'required|string|max:100|unique:pop_offices,name',
            'location' => 'nullable|string|max:255',
        ]);
        PopOffice::create($request->only('name', 'location'));

        return redirect()->route('pop-offices.index')->with('success', 'POP Office added.');
    }

    public function destroy(PopOffice $popOffice)
    {
        $this->superAdminOnly();
        $popOffice->delete();

        return redirect()->route('pop-offices.index')->with('success', 'POP Office deleted.');
    }

    public function tickets(PopOffice $popOffice)
    {
        $this->adminOnly();
        $tickets = $popOffice->tickets()
            ->with(['creator', 'assignee'])
            ->whereNotIn('status', ['resolved'])
            ->latest()->paginate(20);

        return view('pop-offices.tickets', compact('popOffice', 'tickets'));
    }
}
