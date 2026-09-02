<x-dashboard-shell>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Inbox</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Inbound and outbound messages from GoHighLevel.</p>
        </div>
        <form method="POST" action="{{ route('inbox.sync') }}">
            @csrf
            <button class="h-10 px-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold flex items-center gap-2">
                <i class="fa-solid fa-rotate"></i>Sync conversations
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-400">{{ session('error') }}</div>
    @endif

    <section class="h-[calc(100vh-180px)] min-h-[560px] overflow-hidden rounded-2xl border border-slate-200 dark:border-[#1e293b] bg-white dark:bg-[#0e1421] lg:grid lg:grid-cols-[300px_1fr]">
        <aside class="min-h-0 border-b border-slate-200 dark:border-[#1e293b] lg:border-b-0 lg:border-r">
            <div class="border-b border-slate-200 dark:border-[#1e293b] p-4">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-xs text-slate-400"></i>
                    <input placeholder="Search conversations" class="h-9 w-full rounded-lg border border-slate-200 bg-slate-50 pl-9 pr-3 text-sm outline-none focus:border-blue-500 dark:border-[#263247] dark:bg-[#111a2a] dark:text-white">
                </div>
            </div>
            <div class="h-[calc(100%-73px)] overflow-y-auto">
                @forelse($conversations as $conversation)
                    <a href="{{ route('inbox', ['conversation' => $conversation->id]) }}" class="block border-b border-slate-100 p-4 dark:border-[#182235] {{ $activeConversation?->id === $conversation->id ? 'bg-blue-50 dark:bg-[#16233b]' : 'hover:bg-slate-50 dark:hover:bg-[#121c2d]' }}">
                        <div class="flex items-start justify-between gap-3">
                            <h2 class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $conversation->contact_name ?: 'Unknown contact' }}</h2>
                            <span class="shrink-0 text-[11px] text-slate-400">{{ $conversation->last_message_at?->diffForHumans(null, true) }}</span>
                        </div>
                        <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">{{ $conversation->last_message ?: 'No messages yet' }}</p>
                    </a>
                @empty
                    <div class="p-6 text-center text-sm text-slate-500">No conversations yet. Run a sync to load them.</div>
                @endforelse
            </div>
        </aside>

        <div class="flex min-h-0 flex-col">
            @if($activeConversation)
                <header class="flex items-center justify-between border-b border-slate-200 p-4 dark:border-[#1e293b]">
                    <div>
                        <h2 class="font-semibold text-slate-900 dark:text-white">{{ $activeConversation->contact_name ?: 'Unknown contact' }}</h2>
                        <p class="text-xs text-slate-500">{{ $activeConversation->contact_phone_or_email ?: 'Conversation' }}</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-500 dark:bg-[#182235] dark:text-slate-300">{{ $activeConversation->messages->count() }} messages</span>
                </header>
                <div id="messageThread" class="min-h-0 flex-1 space-y-4 overflow-y-auto p-5 scroll-smooth">
                    @forelse($activeConversation->messages->sortBy('sent_at') as $message)
                        <div class="flex {{ $message->direction === 'outbound' ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[78%] rounded-2xl px-4 py-3 text-sm {{ $message->direction === 'outbound' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-800 dark:bg-[#1c2839] dark:text-slate-200' }}">
                                <p class="whitespace-pre-wrap">{{ $message->body }}</p>
                                <div class="mt-2 flex items-center justify-end gap-2 text-[10px] opacity-60">
                                    <span>{{ strtoupper($message->message_type) }}</span>
                                    <span>{{ $message->sent_at?->format('M j, g:i A') ?: $message->created_at?->format('M j, g:i A') }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="py-16 text-center text-sm text-slate-500">No messages in this conversation.</p>
                    @endforelse
                </div>
                <form id="sendMessageForm" method="POST" action="{{ route('inbox.send', $activeConversation) }}" class="border-t border-slate-200 p-4 dark:border-[#1e293b]">
                    @csrf
                    <div class="flex gap-3">
                        <input id="messageBody" name="body" required placeholder="Type a message..." autocomplete="off" class="h-11 min-w-0 flex-1 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-blue-500 dark:border-[#263247] dark:bg-[#111a2a] dark:text-white">
                        <button id="sendMessageButton" type="submit" title="Send message" class="h-11 w-11 shrink-0 rounded-xl bg-blue-600 text-white transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-60"><i class="fa-solid fa-paper-plane"></i></button>
                    </div>
                    <p id="sendMessageError" class="mt-2 hidden text-xs text-red-400"></p>
                </form>
            @else
                <div class="flex flex-1 items-center justify-center p-8 text-center text-sm text-slate-500">Select a conversation after syncing your GoHighLevel messages.</div>
            @endif
        </div>
    </section>
</x-dashboard-shell>

<script>
    const sendForm = document.getElementById('sendMessageForm');
    const messageThread = document.getElementById('messageThread');
    const messageBody = document.getElementById('messageBody');
    const sendButton = document.getElementById('sendMessageButton');
    const sendError = document.getElementById('sendMessageError');

    if (sendForm && messageThread) {
        const renderMessages = (messages) => {
            messageThread.innerHTML = '';
            messages.forEach((message) => {
                const row = document.createElement('div');
                row.className = `flex ${message.direction === 'outbound' ? 'justify-end' : 'justify-start'}`;
                const bubble = document.createElement('div');
                bubble.className = `max-w-[78%] rounded-2xl px-4 py-3 text-sm ${message.direction === 'outbound' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-800 dark:bg-[#1c2839] dark:text-slate-200'}`;
                const text = document.createElement('p');
                text.className = 'whitespace-pre-wrap';
                text.textContent = message.body || '';
                const meta = document.createElement('div');
                meta.className = 'mt-2 flex items-center justify-end gap-2 text-[10px] opacity-60';
                meta.innerHTML = `<span>${message.message_type}</span><span>${message.sent_at || ''}</span>`;
                bubble.append(text, meta);
                row.appendChild(bubble);
                messageThread.appendChild(row);
            });
        };

        const scrollThreadToBottom = () => {
            messageThread.scrollTo({ top: messageThread.scrollHeight, behavior: 'smooth' });
        };

        window.addEventListener('load', scrollThreadToBottom);

        const refreshMessages = async () => {
            try {
                const response = await fetch(`${window.location.pathname}${window.location.search}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!response.ok) return;
                const data = await response.json();
                const currentCount = messageThread.children.length;
                if (data.messages && data.messages.length !== currentCount) {
                    renderMessages(data.messages);
                    scrollThreadToBottom();
                }
            } catch (error) {
            }
        };

        window.setInterval(refreshMessages, 5000);

        sendForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const body = messageBody.value.trim();
            if (!body) return;

            sendButton.disabled = true;
            sendButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
            sendError.classList.add('hidden');

            try {
                const response = await fetch(sendForm.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        _token: sendForm.querySelector('input[name="_token"]').value,
                        body,
                    }),
                });

                const data = await response.json();
                if (!response.ok || data.status !== 'success') {
                    throw new Error(data.message || 'Message could not be sent.');
                }

                const bubble = document.createElement('div');
                bubble.className = 'flex justify-end';
                bubble.innerHTML = `<div class="max-w-[78%] rounded-2xl bg-blue-600 px-4 py-3 text-sm text-white"><p class="whitespace-pre-wrap"></p><div class="mt-2 flex items-center justify-end gap-2 text-[10px] opacity-60"><span>SMS</span><span>Just now</span></div></div>`;
                bubble.querySelector('p').textContent = body;
                messageThread.appendChild(bubble);
                messageBody.value = '';
                scrollThreadToBottom();
            } catch (error) {
                sendError.textContent = error.message;
                sendError.classList.remove('hidden');
            } finally {
                sendButton.disabled = false;
                sendButton.innerHTML = '<i class="fa-solid fa-paper-plane"></i>';
                messageBody.focus();
            }
        });
    }
</script>