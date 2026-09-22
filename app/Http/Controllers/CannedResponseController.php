<?php

namespace App\Http\Controllers;

use App\Models\CannedResponse;
use Illuminate\Http\Request;

class CannedResponseController extends Controller
{
    private function guard(): void
    {
        if (! auth()->user()->isAdmin() && ! auth()->user()->isNoc()) {
            abort(403);
        }
    }

    public function index()
    {
        $this->guard();
        $responses = CannedResponse::with('creator')->orderBy('title')->get();

        return view('canned-responses.index', compact('responses'));
    }

    public function store(Request $request)
    {
        $this->guard();
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'body' => 'required|string|max:5000',
        ]);

        CannedResponse::create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Canned response created.');
    }

    public function update(Request $request, CannedResponse $cannedResponse)
    {
        $this->guard();
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'body' => 'required|string|max:5000',
        ]);

        $cannedResponse->update($validated);

        return back()->with('success', 'Canned response updated.');
    }

    public function destroy(CannedResponse $cannedResponse)
    {
        $this->guard();
        $cannedResponse->delete();

        return back()->with('success', 'Canned response deleted.');
    }

    // Lightweight JSON list for the compose-box picker
    public function list()
    {
        $this->guard();

        return response()->json(
            CannedResponse::orderBy('title')->get(['id', 'title', 'body'])
        );
    }
}
