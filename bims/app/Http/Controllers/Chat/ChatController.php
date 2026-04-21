<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Chat\Conversation;
use App\Models\Chat\Message;
use App\Models\Chat\MessageAttachment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(): View
    {
        $conversations = Conversation::whereHas('participants', fn($q) => $q->where('user_id', auth()->id()))
            ->with(['participants.employee', 'messages' => fn($q) => $q->latest()->limit(1)])
            ->orderByDesc(
                Message::select('created_at')
                    ->whereColumn('conversation_id', 'conversations.id')
                    ->latest()
                    ->limit(1)
            )
            ->get();

        return view('chat.index', compact('conversations'));
    }

    public function show(Conversation $conversation): View
    {
        $this->authorizeConversation($conversation);

        $messages = $conversation->messages()
            ->with(['user', 'attachments'])
            ->orderBy('created_at')
            ->paginate(50);

        $participants = $conversation->participants()->with('employee')->get();

        return view('chat.show', compact('conversation', 'messages', 'participants'));
    }

    public function startConversation(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id'  => ['required', 'exists:users,id', 'not_in:' . auth()->id()],
            'subject'  => ['nullable', 'string', 'max:150'],
        ]);

        $targetId = $request->user_id;

        // Reuse existing direct conversation if one exists
        $existing = Conversation::directBetween(auth()->id(), $targetId);

        if ($existing) {
            return redirect()->route('chat.show', $existing);
        }

        $conversation = Conversation::create([
            'type'    => 'direct',
            'subject' => $request->subject,
        ]);

        $conversation->participants()->attach([auth()->id(), $targetId]);

        return redirect()->route('chat.show', $conversation);
    }

    public function sendMessage(Request $request, Conversation $conversation): RedirectResponse
    {
        $this->authorizeConversation($conversation);

        $maxMb = config('app.max_chat_attachment_mb', 10);

        $request->validate([
            'body'        => ['required_without:attachments', 'nullable', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:' . ($maxMb * 1024)],
        ]);

        $message = $conversation->messages()->create([
            'user_id' => auth()->id(),
            'body'    => $request->body,
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store("chat/attachments/{$conversation->id}", 'local');

                $message->attachments()->create([
                    'filename'    => $file->getClientOriginalName(),
                    'path'        => $path,
                    'mime_type'   => $file->getMimeType(),
                    'size_bytes'  => $file->getSize(),
                ]);
            }
        }

        // Broadcast via Reverb
        broadcast(new \App\Events\MessageSent($message->load('user', 'attachments')))->toOthers();

        return back();
    }

    public function downloadAttachment(MessageAttachment $attachment)
    {
        // Verify user is a participant in the conversation
        $conversation = $attachment->message->conversation;
        $this->authorizeConversation($conversation);

        return Storage::disk('local')->download($attachment->path, $attachment->filename);
    }

    // ── Private helpers ──────────────────────────────────────────

    private function authorizeConversation(Conversation $conversation): void
    {
        $isParticipant = $conversation->participants()
            ->where('user_id', auth()->id())
            ->exists();

        if (! $isParticipant) {
            abort(403, 'You are not a participant in this conversation.');
        }
    }
}
