<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MessageAttachment extends Model
{
    protected $fillable = ['message_id', 'filename', 'stored_as', 'mime_type', 'size_bytes'];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function getFormattedSizeAttribute(): string
    {
        $kb = $this->size_bytes / 1024;
        return $kb < 1024 ? round($kb, 1).'KB' : round($kb / 1024, 1).'MB';
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('chat.attachment.download', $this->id);
    }
}
