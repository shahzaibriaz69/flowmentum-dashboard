<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\SyncLocationDetailsService;

class ConversationController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->all();
        Log::info('GHL Conversation Webhook Incoming', [
            'event' => $payload['type'] ?? $payload['event'] ?? null,
            'keys' => array_keys($payload),
        ]);

        $storedMessage = SyncLocationDetailsService::persistConversationMessage($payload);
        if (!$storedMessage) {
            return response()->json(['status' => 'ignored', 'message' => 'Conversation ID or Contact ID missing'], 400);
        }

        return response()->json([
            'status' => 'success',
            'conversation_id' => $storedMessage->conversation_id,
            'message_id' => $storedMessage->platform_message_id,
        ], 200);
    }
}