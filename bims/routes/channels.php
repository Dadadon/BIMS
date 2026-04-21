<?php

use App\Models\Chat\Conversation;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Presence channel for a conversation room.
 * Only participants are authorised.
 */
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);
    if (! $conversation) return false;

    $isParticipant = $conversation->participants()
        ->where('user_id', $user->id)
        ->exists();

    if (! $isParticipant) return false;

    return [
        'id'   => $user->id,
        'name' => $user->name,
    ];
});
