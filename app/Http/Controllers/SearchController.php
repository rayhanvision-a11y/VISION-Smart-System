<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q', '');
        $user = auth()->user();

        $query = Ticket::with(['creator', 'assignee', 'labels']);

        if ($user->isReseller()) {
            $query->where('created_by', $user->id);
        }

        if ($q) {
            $query->where(function($sq) use ($q) {
                $sq->where('title', 'like', "%{$q}%")
                   ->orWhere('description', 'like', "%{$q}%")
                   ->orWhere('id', is_numeric($q) ? $q : 0)
                   ->orWhereHas('creator', fn($uq) => $uq->where('name', 'like', "%{$q}%"));
            });
        }

        $results = $query->latest()->take(50)->get();

        return view('search', compact('results', 'q'));
    }
}
