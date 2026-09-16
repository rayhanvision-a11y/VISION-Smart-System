<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WhatsAppController extends Controller
{
    private string $nodeBase = 'http://127.0.0.1:3000/api/whatsapp';

    private function waHeaders(): array
    {
        $user = auth()->user();
        return [
            'X-WA-User'     => (string) auth()->id(),
            'X-WA-Admin'    => ($user?->isSuperAdminOnly() || $user?->isAdmin()) ? '1' : '0',
            'X-Internal-Token' => env('WA_INTERNAL_SECRET', 'change-me-in-env'),
        ];
    }

    private function validId(string $id): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9\-]+$/', $id);
    }

    private function waGet(string $endpoint, array $query = [], int $timeout = 5)
    {
        return Http::timeout($timeout)->withHeaders($this->waHeaders())->get("{$this->nodeBase}{$endpoint}", $query);
    }

    private function waPost(string $endpoint, array $body = [], int $timeout = 10)
    {
        return Http::timeout($timeout)->withHeaders($this->waHeaders())->post("{$this->nodeBase}{$endpoint}", $body);
    }

    private function waDelete(string $endpoint, int $timeout = 5)
    {
        return Http::timeout($timeout)->withHeaders($this->waHeaders())->delete("{$this->nodeBase}{$endpoint}");
    }

    public function index()
    {
        return view('whatsapp.index');
    }

    public function status()
    {
        try {
            $response = $this->waGet('/status');
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['status' => 'disconnected', 'user' => ['name' => '', 'phone' => ''], 'hasQR' => false, 'chatCount' => 0]);
        }
    }

    public function qr()
    {
        try {
            $response = $this->waGet('/qr');
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['status' => 'disconnected', 'qrDataURL' => null]);
        }
    }

    public function chats()
    {
        try {
            $response = $this->waGet('/chats');
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['status' => 'disconnected', 'chats' => []]);
        }
    }

    public function profilePic(Request $request)
    {
        $jid = $request->query('jid', '');
        try {
            $response = $this->waGet('/profile-pic', ['jid' => $jid]);
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['url' => null]);
        }
    }

    public function profilePics(Request $request)
    {
        try {
            $response = $this->waPost('/profile-pics', $request->all(), 10);
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['pics' => []]);
        }
    }

    public function messages(Request $request)
    {
        $jid = $request->query('jid', '');
        try {
            $response = $this->waGet('/messages', ['jid' => $jid]);
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['messages' => []]);
        }
    }

    public function send(Request $request)
    {
        $request->validate([
            'targetJid'   => 'required|string|max:50',
            'text'        => 'nullable|string|max:5000',
            'imageBase64' => 'nullable|string|max:2097152',
        ]);
        try {
            $response = $this->waPost('/send', $request->only(['targetJid', 'text', 'imageBase64']), 15);
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal error.'], 500);
        }
    }

    public function broadcast(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'message'     => 'nullable|string|max:5000',
            'imageBase64' => 'nullable|string|max:2097152',
            'jids'        => 'required|array|max:500',
            'jids.*'      => 'string|max:50',
        ]);
        try {
            $response = $this->waPost('/broadcast', $request->only(['name', 'message', 'imageBase64', 'jids']), 120);
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal error.'], 500);
        }
    }

    public function addContact(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'name'  => 'nullable|string|max:255',
        ]);
        try {
            $response = $this->waPost('/add-contact', $request->only(['phone', 'name']));
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal error.'], 500);
        }
    }

    public function logout()
    {
        try {
            $response = $this->waPost('/logout');
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal error.'], 500);
        }
    }

    public function getSavedLists()
    {
        try {
            $response = $this->waGet('/saved-lists');
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['lists' => []]);
        }
    }

    public function saveList(Request $request)
    {
        try {
            $response = $this->waPost('/saved-lists', $request->all());
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal error.'], 500);
        }
    }

    public function deleteList(Request $request)
    {
        $id = $request->query('id', '');
        if (!$this->validId($id)) abort(422);
        try {
            $response = $this->waDelete("/saved-lists?id={$id}");
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal error.'], 500);
        }
    }

    public function renameList(Request $request, string $id)
    {
        if (!$this->validId($id)) abort(422);
        $request->validate(['name' => 'required|string|max:255']);
        try {
            $response = $this->waPost("/saved-lists/{$id}/rename", $request->only(['name']));
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal error.'], 500);
        }
    }

    public function toggleListPublic(string $id)
    {
        if (!$this->validId($id)) abort(422);
        try {
            $response = $this->waPost("/saved-lists/{$id}/toggle-public");
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal error.'], 500);
        }
    }

    public function getTemplates()
    {
        try {
            $response = $this->waGet('/templates');
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['templates' => []]);
        }
    }

    public function saveTemplate(Request $request)
    {
        try {
            $response = $this->waPost('/templates', $request->all());
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal error.'], 500);
        }
    }

    public function renameTemplate(Request $request, string $id)
    {
        if (!$this->validId($id)) abort(422);
        $request->validate(['name' => 'required|string|max:255', 'message' => 'nullable|string|max:5000']);
        try {
            $response = $this->waPost("/templates/{$id}/rename", $request->only(['name', 'message', 'scope']));
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal error.'], 500);
        }
    }

    public function deleteTemplate(Request $request)
    {
        $id = $request->query('id', '');
        if (!$this->validId($id)) abort(422);
        try {
            $response = $this->waDelete("/templates?id={$id}");
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal error.'], 500);
        }
    }

    public function getContacts()
    {
        try {
            $response = $this->waGet('/contacts');
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['contacts' => []]);
        }
    }

    public function search(Request $request)
    {
        $q = $request->input('q', '');
        try {
            $response = $this->waGet('/search', ['q' => $q], 8);
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['results' => []]);
        }
    }

    public function sync()
    {
        try {
            $response = $this->waPost('/sync', [], 15);
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal error.'], 500);
        }
    }

    // Schedule Broadcast
    public function getScheduledBroadcasts()
    {
        try {
            $response = $this->waGet('/scheduled-broadcasts');
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['scheduled' => []]);
        }
    }

    public function saveScheduledBroadcast(Request $request)
    {
        try {
            $response = $this->waPost('/scheduled-broadcasts', $request->all(), 15);
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal error.'], 500);
        }
    }

    public function deleteScheduledBroadcast(Request $request)
    {
        $id = $request->query('id', '');
        if (!$this->validId($id)) abort(422);
        try {
            $response = $this->waDelete("/scheduled-broadcasts?id={$id}");
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal error.'], 500);
        }
    }

    // Broadcast History
    public function getBroadcastHistory()
    {
        try {
            $response = $this->waGet('/broadcast-history');
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['history' => []]);
        }
    }

    public function deleteBroadcastHistory(Request $request)
    {
        $id = $request->query('id', '');
        if (!$this->validId($id)) abort(422);
        try {
            $response = $this->waDelete("/broadcast-history?id={$id}");
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal error.'], 500);
        }
    }

    // Sessions (super admin only)
    public function getSessions()
    {
        $user = auth()->user();
        if (!$user?->isSuperAdminOnly() && !$user?->isAdmin()) abort(403);
        try {
            $response = $this->waGet('/sessions');
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['sessions' => []]);
        }
    }

    public function logoutSession(string $userId)
    {
        $user = auth()->user();
        if (!$user?->isSuperAdminOnly() && !$user?->isAdmin()) abort(403);
        if (!preg_match('/^\d+$/', $userId)) abort(422);
        try {
            $response = $this->waPost("/sessions/{$userId}/logout");
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal error.'], 500);
        }
    }
}
