@extends('layouts.app')
@section('title', 'Chat')
@section('page-title', 'Chat')

@section('content')
<div class="flex h-[calc(100vh-10rem)] gap-0 overflow-hidden rounded-lg shadow ring-1 ring-black ring-opacity-5">

    {{-- Sidebar: participants --}}
    <div class="hidden lg:flex lg:w-56 lg:flex-col bg-gray-50 border-r border-gray-200">
        <div class="px-4 py-3 border-b border-gray-200">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Participants</p>
        </div>
        <ul class="flex-1 overflow-y-auto divide-y divide-gray-100">
            @foreach($participants as $participant)
            <li class="px-4 py-3 flex items-center gap-3">
                <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 text-sm font-semibold shrink-0">
                    {{ substr($participant->name, 0, 1) }}
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $participant->name }}</p>
                    <p class="text-xs text-gray-500">{{ $participant->employee?->employee_code ?? '' }}</p>
                </div>
            </li>
            @endforeach
        </ul>
        <div class="px-4 py-3 border-t border-gray-200">
            <a href="{{ route('chat.index') }}" class="text-xs text-indigo-600 hover:text-indigo-900">← All chats</a>
        </div>
    </div>

    {{-- Chat area --}}
    <div class="flex flex-1 flex-col bg-white" id="chat-container" x-data="chatApp({{ $conversation->id }})">

        {{-- Message feed --}}
        <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4" id="message-feed">
            @foreach($messages as $msg)
            @php $isOwn = $msg->user_id === auth()->id(); @endphp
            <div class="flex {{ $isOwn ? 'justify-end' : 'justify-start' }} gap-3">
                @if(! $isOwn)
                <div class="h-8 w-8 shrink-0 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 text-sm font-semibold">
                    {{ substr($msg->user->name, 0, 1) }}
                </div>
                @endif
                <div class="max-w-sm">
                    @if(! $isOwn)
                    <p class="text-xs text-gray-500 mb-1">{{ $msg->user->name }}</p>
                    @endif
                    @if($msg->body)
                    <div class="rounded-2xl px-4 py-2 text-sm {{ $isOwn ? 'bg-indigo-600 text-white rounded-br-sm' : 'bg-gray-100 text-gray-900 rounded-bl-sm' }}">
                        {{ $msg->body }}
                    </div>
                    @endif
                    @foreach($msg->attachments as $att)
                    <div class="mt-1 flex items-center gap-2 text-xs text-gray-600 bg-gray-50 rounded-md px-3 py-2 border border-gray-200">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/>
                        </svg>
                        <a href="{{ route('chat.attachment.download', $att) }}" class="hover:text-indigo-600 truncate max-w-[140px]">{{ $att->filename }}</a>
                        <span class="text-gray-400">{{ $att->formatted_size }}</span>
                    </div>
                    @endforeach
                    <p class="text-xs text-gray-400 mt-1 {{ $isOwn ? 'text-right' : '' }}">
                        {{ $msg->created_at->format('g:i A') }}
                    </p>
                </div>
            </div>
            @endforeach

            {{-- Real-time messages appended here --}}
            <template x-for="msg in newMessages" :key="msg.id">
                <div :class="msg.user_id === {{ auth()->id() }} ? 'justify-end' : 'justify-start'" class="flex gap-3">
                    <div class="max-w-sm">
                        <div :class="msg.user_id === {{ auth()->id() }} ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-900'"
                             class="rounded-2xl px-4 py-2 text-sm" x-text="msg.body"></div>
                        <p class="text-xs text-gray-400 mt-1" x-text="msg.user_name"></p>
                    </div>
                </div>
            </template>
        </div>

        {{-- Message input --}}
        <div class="border-t border-gray-200 px-4 py-3">
            <form method="POST" action="{{ route('chat.send', $conversation) }}" enctype="multipart/form-data"
                  class="flex items-end gap-3">
                @csrf
                <div class="flex-1">
                    <textarea name="body" rows="1" placeholder="Type a message…"
                              class="block w-full rounded-2xl border-0 py-2 px-4 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm resize-none"
                              onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();this.form.submit();}"></textarea>
                </div>
                <div>
                    <label class="cursor-pointer rounded-full p-2 text-gray-400 hover:text-indigo-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/>
                        </svg>
                        <input type="file" name="attachments[]" multiple class="hidden">
                    </label>
                </div>
                <button type="submit"
                        class="shrink-0 rounded-full bg-indigo-600 p-2 text-white hover:bg-indigo-500">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M3.105 2.289a.75.75 0 00-.826.95l1.414 4.925A1.5 1.5 0 005.135 9.25h6.115a.75.75 0 010 1.5H5.135a1.5 1.5 0 00-1.442 1.086l-1.414 4.926a.75.75 0 00.826.95 28.896 28.896 0 0015.293-7.154.75.75 0 000-1.115A28.897 28.897 0 003.105 2.289z"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function chatApp(conversationId) {
    return {
        newMessages: [],
        init() {
            // Scroll to bottom on load
            const feed = document.getElementById('message-feed');
            feed.scrollTop = feed.scrollHeight;

            // Subscribe via Reverb / Echo
            if (window.Echo) {
                window.Echo.join(`conversation.${conversationId}`)
                    .listen('MessageSent', (e) => {
                        this.newMessages.push(e.message);
                        this.$nextTick(() => { feed.scrollTop = feed.scrollHeight; });
                    });
            }
        }
    };
}
</script>
@endpush
@endsection
