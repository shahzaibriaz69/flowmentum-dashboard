<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Conversation;
use App\Models\GhlLocation;
use App\Services\SyncLocationDetailsService;

class InboxController extends Controller
{
    public function index(Request $request)
    {
        $location = $this->location();
        $conversations = Conversation::with(['messages' => fn ($query) => $query->latest('sent_at')->latest()])
            ->where('location_id', $location->ghl_location_id)
            ->latest('last_message_at')
            ->get();

        $selectedConversationId = (int) $request->integer('conversation');
        $activeConversation = $conversations->firstWhere('id', $selectedConversationId) ?: $conversations->first();

        return view('inbox', compact('conversations', 'location', 'activeConversation'));
    }

    public function sync()
    {
        $count = SyncLocationDetailsService::syncConversations($this->location());
        return redirect()->route('inbox')->with('success', "{$count} messages synced from GoHighLevel.");
    }

    public function send(Request $request, Conversation $conversation)
    {
        $validated = $request->validate(['body' => ['required', 'string', 'max:10000']]);
        abort_unless($conversation->location_id === $this->location()->ghl_location_id, 404);

        $response = Http::withToken($this->location()->access_token)
            ->withHeaders(['Version' => '2021-04-15', 'Accept' => 'application/json'])
            ->post('https://services.leadconnectorhq.com/conversations/messages', [
                'type' => 'SMS',
                'contactId' => $conversation->contact_id,
                'message' => $validated['body'],
            ]);

        if (!$response->successful()) {
            return back()->withInput()->with('error', 'GoHighLevel message send failed.');
        }

        SyncLocationDetailsService::persistConversationMessage([
            'type' => 'OutboundMessage',
            'locationId' => $conversation->location_id,
            'conversationId' => $conversation->platform_conversation_id,
            'contactId' => $conversation->contact_id,
            'body' => $validated['body'],
            'direction' => 'outbound',
            'messageType' => 'SMS',
            'status' => 'sent',
            'messageId' => $response->json('messageId'),
        ], $this->location(), $conversation);

        return redirect()->route('inbox');
    }

    private function location(): GhlLocation
    {
        $user = auth()->user();
        $locationIds = array_filter([
            $user->location_id ?? null,
            $user->ghl_location_id ?? null,
            request()->attributes->get('active_location_id'),
        ]);

        return GhlLocation::where('user_id', $user->id)
            ->orWhereIn('ghl_location_id', $locationIds)
            ->firstOrFail();
    }
}