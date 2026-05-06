<?php

namespace App\Concerns;

use Illuminate\Validation\Rule;

trait ChatValidationRules
{
    /**
     * Validation rules for creating a conversation.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function createConversationRules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Validation rules for sending a message.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function sendMessageRules(): array
    {
        return [
            'content' => ['required', 'string', 'min:1'],
            'model' => ['required', 'string', 'max:255'],
            'temperature' => ['nullable', 'numeric', 'min:0', 'max:2'],
            'max_tokens' => ['nullable', 'integer', 'min:1'],
            'stream' => ['nullable', Rule::in([true, false, 1, 0, '1', '0'])],
        ];
    }
}
