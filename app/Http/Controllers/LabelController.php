<?php

namespace App\Http\Controllers;

use App\Models\Label;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    private function adminOnly()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }
    }

    public function index()
    {
        $this->adminOnly();
        $labels = Label::withCount('tickets')->orderBy('name')->get();
        return view('labels.index', compact('labels'));
    }

    public function store(Request $request)
    {
        $this->adminOnly();
        $request->validate([
            'name'  => 'required|string|max:50|unique:labels,name',
            'color' => 'required|string|max:7',
        ]);
        Label::create($request->only('name', 'color'));
        return redirect()->route('labels.index')->with('success', 'Label created.');
    }

    public function destroy(string $id)
    {
        $this->adminOnly();
        Label::findOrFail($id)->delete();
        return redirect()->route('labels.index')->with('success', 'Label deleted.');
    }
}
