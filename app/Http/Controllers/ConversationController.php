<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Conversation;

class ConversationController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->all();
        $message = is_array($payload['message'] ?? null) ? $payload['message'] : [];
        $contact = is_array($payload['contact'] ?? null) ? $payload['contact'] : [];
        $conversation = is_array($payload['conversation'] ?? null) ? $payload['conversation'] : [];

        Log::info('GHL Conversation Webhook Incoming', [
            'event' => $payload['type'] ?? $payload['event'] ?? null,
            'keys' => array_keys($payload),
        ]);

        $eventType = strtolower((string) ($payload['type'] ?? $payload['event'] ?? ''));
        $direction = strtolower((string) ($payload['direction'] ?? $message['direction'] ?? ''));
        $direction = str_contains($eventType, 'outbound') || $direction === 'outbound'
            ? 'outbound'
            : 'inbound';

        $contactId = $payload['contactId'] ?? $message['contactId'] ?? $contact['id'] ?? null;
        $locationId = $payload['locationId'] ?? $message['locationId'] ?? $contact['locationId'] ?? null;
        $conversationId = $payload['conversationId'] ?? $message['conversationId'] ?? $conversation['id'] ?? null;
        $conversationId ??= $contactId ? "conv_{$contactId}" : null;

        $messageId = $payload['messageId'] ?? $message['id'] ?? $payload['id'] ?? null;
        $body = $payload['body'] ?? $message['body'] ?? $payload['text'] ?? $message['text'] ?? (is_scalar($payload['message'] ?? null) ? $payload['message'] : null);
        $messageType = $payload['messageType'] ?? $message['type'] ?? $payload['channel'] ?? 'text';

        if (!$conversationId && !$contactId) {
            return response()->json([
                'status'  => 'ignored',
                'message' => 'Neither Conversation ID nor Contact ID provided in payload'
            ], 400);
        }

        $conversation = Conversation::firstOrCreate(
            ['platform_conversation_id' => $conversationId],
            [
                'contact_id'   => $contactId ?? 'unknown',
                'contact_name' => $payload['fullName'] ?? $contact['name'] ?? trim(($payload['firstName'] ?? $contact['firstName'] ?? '') . ' ' . ($payload['lastName'] ?? $contact['lastName'] ?? '')),
                'last_message' => $body,
            ]
        );

        $conversation->update(['last_message' => $body]);

        $messageData = [
            'direction' => $direction,
            'message_type' => strtolower((string) $messageType),
            'body' => is_scalar($body) ? (string) $body : json_encode($body),
            'raw_payload' => $payload,
        ];

        if ($messageId) {
            $conversation->messages()->updateOrCreate(
                ['platform_message_id' => (string) $messageId],
                $messageData
            );
        } else {
            $conversation->messages()->create($messageData);
        }

        return response()->json([
            'status' => 'success',
            'conversation_id' => $conversation->id,
            'message_id' => $messageId,
        ], 200);
    }
}