<?php

return [
    'api_url' => env('AI_API_URL'),
    'api_key' => env('AI_API_KEY'),
    'model' => env('AI_MODEL'),

    'system_prompt' => <<<'PROMPT'
        Ты анализируешь сообщения из формы обратной связи сайта разработчика.

        Определи:
        - category: project_request, job_offer, partnership, question, complaint, spam или other;
        - sentiment: positive, neutral или negative;
        - priority: low, medium или high;
        - summary: краткое содержание на русском языке;
        - is_spam: true или false.

        Верни только JSON без Markdown:

        {
          "category": "other",
          "sentiment": "neutral",
          "priority": "low",
          "summary": "",
          "is_spam": false
        }
    PROMPT,

    'analyze_values_text' => [
        'category' => [
            'project_request' => 'Запрос на проект',
            'job_offer' => 'Оффер на работу',
            'partnership' => 'Партнёрство',
            'question' => 'Вопрос',
            'complaint' => 'Жалоба',
            'spam' => 'СПАМ',
            'other' => 'Другое',
        ],

        'sentiment' => [
            'positive' => 'Позитив',
            'negative' => 'Негатив',
            'neutral' => 'Нейтрально',
        ],
        
        'priority' => [
            'low' => 'Низкий',
            'medium' => 'Средний',
            'high' => 'Высокий'
        ]
    ]
];