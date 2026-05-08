<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenRouterService
{
    /**
     * Get available models from OpenRouter.
     *
     * @return array<string, mixed>
     */
    public function listModels(): array
    {
        $this->ensureApiKey();

        $response = Http::baseUrl((string) config('services.openrouter.base_url'))
            ->acceptJson()
            ->withToken($this->resolveApiKey())
            ->timeout(30)
            ->get('/models');

        if (! $response->successful()) {
            throw new RuntimeException('Failed to fetch models from OpenRouter.');
        }

        return $response->json();
    }

    /**
     * Build the full chat completion payload from validated request data and conversation history.
     *
     * Centralises model, temperature, max_tokens defaults so controllers stay thin.
     *
     * @param array<string, mixed>           $data     Validated request data (model, temperature, max_tokens).
     * @param iterable<\App\Models\Message>  $messages Ordered conversation history.
     * @return array<string, mixed>
     */
    public function buildChatPayload(array $data, iterable $messages): array
    {
        return [
            'model'      => $data['model'],
            'messages'   => $this->buildMessagesPayload($messages),
            'temperature' => (float) ($data['temperature'] ?? 0.7),
            'max_tokens'  => (int) ($data['max_tokens'] ?? 1024),
        ];
    }

    /**
     * Build the messages array required by the OpenRouter chat completions API.
     *
     * @param iterable<\App\Models\Message> $messages
     * @return array<int, array<string, string>>
     */
    public function buildMessagesPayload(iterable $messages): array
    {
        $payload = [];

        foreach ($messages as $message) {
            $payload[] = [
                'role' => $message->role,
                'content' => $message->content,
            ];
        }

        return $payload;
    }

    /**
     * Stream a chat completion from OpenRouter.
     *
     * Calls $onChunk with each incremental content delta string.
     * Returns the fully assembled assistant content when the stream ends.
     *
     * @param array<string, mixed> $payload
     * @throws RuntimeException on API errors or network failures
     */
    public function streamChatCompletion(array $payload, callable $onChunk): string
    {
        $this->ensureApiKey();

        $payload['stream'] = true;

        try {
            $response = Http::baseUrl((string) config('services.openrouter.base_url'))
                ->withToken((string) $this->resolveApiKey())
                ->withHeaders(['Accept' => 'text/event-stream'])
                ->withOptions(['stream' => true])
                ->timeout(120)
                ->connectTimeout(30)
                ->post('/chat/completions', $payload);
        } catch (\Illuminate\Http\Client\ConnectionException) {
            throw new RuntimeException('Could not connect to OpenRouter. Check your network connection.');
        }

        if (! $response->successful()) {
            $this->throwFromErrorResponse($response);
        }

        return $this->consumeSseStream($response->getBody(), $onChunk);
    }

    /**
     * Read and forward SSE chunks from the response stream.
     *
     * @param \Psr\Http\Message\StreamInterface $body
     */
    private function consumeSseStream(mixed $body, callable $onChunk): string
    {
        $assembled = '';
        $buffer = '';

        while (! $body->eof()) {
            $raw = $body->read(4096);

            if ($raw === '') {
                continue;
            }

            $buffer .= $raw;

            while (($pos = strpos($buffer, "\n")) !== false) {
                $line = rtrim(substr($buffer, 0, $pos), "\r");
                $buffer = substr($buffer, $pos + 1);

                if (! str_starts_with($line, 'data: ')) {
                    continue;
                }

                $data = substr($line, 6);

                if ($data === '[DONE]') {
                    return $assembled;
                }

                if ($data === '') {
                    continue;
                }

                $parsed = json_decode($data, true);

                if (! is_array($parsed)) {
                    continue;
                }

                $content = $parsed['choices'][0]['delta']['content'] ?? '';

                if ($content !== '') {
                    $assembled .= $content;
                    $onChunk($content);
                }
            }
        }

        return $assembled;
    }

    /**
     * Map a non-2xx OpenRouter response to a descriptive RuntimeException.
     *
     * @throws RuntimeException
     */
    private function throwFromErrorResponse(mixed $response): never
    {
        $status = $response->status();

        try {
            $body = $response->json() ?? [];
            $message = $body['error']['message'] ?? $body['message'] ?? 'OpenRouter request failed.';
        } catch (\Throwable) {
            $message = 'OpenRouter request failed.';
        }

        throw match (true) {
            $status === 401 => new RuntimeException('OpenRouter API key is invalid.'),
            $status === 402 => new RuntimeException('OpenRouter account has insufficient credits.'),
            $status === 429 => new RuntimeException('OpenRouter rate limit exceeded. Please try again later.'),
            default => new RuntimeException($message),
        };
    }

    public function ensureApiKey(): void
    {
        if ($this->resolveApiKey() === null) {
            throw new RuntimeException('OPENROUTER_API_KEY is not configured.');
        }
    }

    protected function resolveApiKey(): ?string
    {
        return config('services.openrouter.api_key');
    }
}
