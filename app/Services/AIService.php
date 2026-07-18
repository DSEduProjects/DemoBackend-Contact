<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Throwable;

class AIService {
    public function analyzeRequest(array $contact): array {
        try {
            $fetch = Http::withToken(Config::get("ai.api_key"))
                ->acceptJson()
                ->timeout(10)
                ->post(Config::get('ai.api_url'), [
                    'model' => Config::get("ai.model"),
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => Config::get('ai.system_prompt')
                        ],
                        [
                            'role' => 'user',
                            'content' => sprintf(
                                "Имя: %s\nКомментарий: %s",
                                $contact['name'],
                                $contact['comment']
                            )
                        ]
                    ]
                ]);

            $fetch->throw();

            $content = $fetch->json('choices.0.message.content');

            if (!is_string($content)) {
                throw new \RuntimeException(
                    'AI вернул ответ в неожиданном формате'
                );
            }

            $analysis = json_decode($content, true);

            if (!is_array($analysis)) {
                throw new \RuntimeException(
                    'AI вернул некорректный JSON'
                );
            }

            return [
                'category' => $analysis['category'] ?? 'other',
                'sentiment' => $analysis['sentiment'] ?? 'neutral',
                'priority' => $analysis['priority'] ?? 'low',
                'summary' => $analysis['summary'] ?? '',
                'is_spam' => $analysis['is_spam'] ?? false,
                'source' => 'ai',
            ];
        } catch (Throwable $exception) {
            Log::warning('AI analysis failed', [
                'message' => $exception->getMessage(),
            ]);

            return [
                'category' => 'other',
                'sentiment' => 'neutral',
                'priority' => 'low',
                'summary' => 'AI-анализ временно недоступен',
                'is_spam' => false,
                'source' => 'fallback',
            ];
        }
    }
}