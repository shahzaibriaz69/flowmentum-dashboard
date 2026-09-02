<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('stores inbound and outbound webhook fields', function () {
    $basePayload = [
        'locationId' => 'location-test',
        'contactId' => 'contact-test',
        'conversationId' => 'conversation-test',
        'attachments' => [],
        'body' => 'Test message',
        'contentType' => 'text/plain',
        'dateAdded' => '2026-09-02T10:00:00.000Z',
        'status' => 'delivered',
        'conversationProviderId' => 'provider-test',
        'chatWidgetId' => 'widget-test',
        'from' => '+15550000001',
        'to' => '+15550000002',
        'messageTypeId' => 2,
        'messageTypeString' => 'TYPE_SMS',
    ];

    $this->postJson('/api/ghl-webhooks/inbound-message', $basePayload + [
        'type' => 'InboundMessage',
        'direction' => 'inbound',
        'messageId' => 'inbound-message-test',
    ])->assertOk();

    $this->postJson('/api/ghl-webhooks/outbound-message', $basePayload + [
        'type' => 'OutboundMessage',
        'direction' => 'outbound',
        'messageId' => 'outbound-message-test',
    ])->assertOk();

    expect(DB::table('conversation_messages')->where('conversation_platform_id', 'conversation-test')->count())->toBe(2);
    expect(DB::table('conversation_messages')->where('platform_message_id', 'outbound-message-test')->value('direction'))->toBe('outbound');
    expect(DB::table('conversation_messages')->where('platform_message_id', 'inbound-message-test')->value('from_address'))->toBe('+15550000001');
});
