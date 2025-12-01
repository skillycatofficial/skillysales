<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Chat;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    \Illuminate\Support\Facades\Log::info('Channel auth attempt', ['user_id' => $user->id, 'chat_id' => $chatId]);
    return true;
});
