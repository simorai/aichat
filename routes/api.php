<?php

use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\ConversationMessageController;
use App\Http\Controllers\Api\ModelController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])->group(function () {
    Route::get('conversations', [ConversationController::class, 'index'])->name('api.conversations.index');
    Route::post('conversations', [ConversationController::class, 'store'])->name('api.conversations.store');
    Route::get('conversations/{conversation}', [ConversationController::class, 'show'])->name('api.conversations.show');

    Route::post('conversations/{conversation}/messages', [ConversationMessageController::class, 'store'])
        ->name('api.conversations.messages.store');

    Route::get('models', [ModelController::class, 'index'])->name('api.models.index');
});
