<?php

namespace App\Events;

use App\Models\Chat\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Message $message) {}

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('conversation.' . $this->message->conversation_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id'          => $this->message->id,
            'user_id'     => $this->message->user_id,
            'user_name'   => $this->message->user->name,
            'body'        => $this->message->body,
            'created_at'  => $this->message->created_at->toIso8601String(),
            'attachments' => $this->message->attachments->map(fn($a) => [
                'id'       => $a->id,
                'filename' => $a->filename,
                'size'     => $a->formatted_size,
            ])->toArray(),
        ];
    }
}
