# DemoBackend Contact API

Небольшое Laravel-приложение с формой обратной связи. Проект принимает обращение пользователя, валидирует данные, выполняет AI-анализ текста через OpenAI-совместимый API и отправляет два письма: владельцу сайта и пользователю.

## 1. Запуск проекта

### Требования

- PHP 8.4+
- Composer
- доступ к SMTP-серверу
- API-ключ AI-провайдера
- SQLite и необходимые PHP-расширения для Laravel

### Установка

```powershell
git clone https://github.com/DSEduProjects/DemoBackend-Contact
cd DemoBackend

composer install

Copy-Item .env.example .env

php artisan key:generate
php artisan migrate
php artisan optimize:clear
```

Запуск локального сервера:

```powershell
php artisan serve
```

После запуска:

- Blade-форма: `http://127.0.0.1:8000/`
- API: `http://127.0.0.1:8000/api`
- Health check: `http://127.0.0.1:8000/api/health`

### Переменные окружения

В `.env` необходимо настроить приложение, SMTP и OpenAI-совместимый API:

```env
APP_NAME=DemoBackend
APP_ENV=local
APP_DEBUG=false
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=sqlite

CACHE_STORE=database

MAIL_MAILER=smtp
MAIL_SCHEME=tls
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="DemoBackend"
MAIL_OWNER_ADDRESS=

AI_API_URL=https://api.example.com/v1/chat/completions
AI_API_KEY=
AI_MODEL=
```

После изменения `.env`:

```powershell
php artisan optimize:clear
```

Приложение не привязано к конкретному AI-провайдеру. Подойдёт любой сервис, который поддерживает OpenAI-совместимый endpoint формата:

```text
POST /v1/chat/completions
```

Например, можно использовать OpenAI, OpenRouter, Together AI, Groq или локальный совместимый сервер. Конкретный URL и модель задаются через `.env`.

Файл `.env` не должен попадать в репозиторий.

## 2. Стек технологий

### Backend

- PHP 8.4+
- Laravel 13
- Blade
- Laravel HTTP Client
- Laravel Mail
- Laravel Validation
- Laravel Rate Limiting
- файловое логирование Laravel
- SQLite для служебных данных Laravel и cache-хранилища

### AI

- OpenAI-совместимый API
- языковая модель задаётся через `AI_MODEL`
- структурированный JSON-ответ
- один AI-запрос на одно обращение

## 3. Архитектура

Проект разделён по зонам ответственности:

```text
app/
├── Exceptions/
│   └── MailSendingException.php
├── Http/
│   ├── Controllers/
│   │   └── ContactController.php
│   ├── Middleware/
│   │   └── LogHttpRequest.php
│   └── Requests/
│       └── ContactRequest.php
├── Mail/
│   ├── ContactMailOwner.php
│   └── ContactMailUser.php
└── Services/
    ├── AIService.php
    ├── ContactService.php
    └── MailService.php

resources/views/
├── main.blade.php
└── mail/

routes/
├── api.php
└── web.php
```

### Ответственность классов

- `ContactController` принимает запрос и возвращает HTTP-ответ.
- `ContactRequest` содержит правила валидации.
- `ContactService` координирует AI-анализ и отправку писем.
- `AIService` работает с OpenAI-совместимый API и формирует fallback.
- `MailService` отправляет письмо владельцу и подтверждение пользователю.
- `ContactMailOwner` и `ContactMailUser` формируют письма.
- `LogHttpRequest` записывает техническую информацию об API-запросах.
- `bootstrap/app.php` централизованно обрабатывает исключения и JSON-ошибки.

### Использованные подходы

- разделение ответственности;
- сервисный слой;
- dependency injection;
- Form Request для валидации;
- middleware для сквозного логирования;
- конфигурация через `.env`;
- graceful degradation для AI-интеграции.

### Почему выбран Laravel

Laravel предоставляет готовые механизмы для маршрутизации, валидации, отправки почты, HTTP-запросов, логирования, rate limiting и обработки исключений. Это уменьшает количество вспомогательного кода и позволяет сосредоточиться на бизнес-логике.

Используется абстракция OpenAI-совместимого API. Провайдера, базовый URL и модель можно менять через `.env` без изменения бизнес-логики приложения.

## 4. Реализация API

### `POST /api/contact`

Принимает форму обратной связи, выполняет AI-анализ и отправляет два письма.

Пример запроса:

```json
{
  "name": "Roman",
  "phone": "89646637222",
  "email": "roman@example.com",
  "comment": "Хотим предложить вам работу backend-разработчиком. Подскажите, когда вы сможете выйти?"
}
```

### Валидация

| Поле | Правила |
|---|---|
| `name` | обязательное, строка, от 2 до 100 символов |
| `phone` | обязательное, строка, не более 30 символов |
| `email` | обязательное, корректный email, не более 255 символов |
| `comment` | обязательное, строка, от 5 до 2000 символов |

Успешный ответ:

```http
201 Created
```

```json
{
  "success": true,
  "message": "Ваше обращение принято"
}
```

Ошибка валидации:

```http
422 Unprocessable Entity
```

```json
{
  "success": false,
  "message": "Ошибка валидации.",
  "errors": {
    "email": [
      "Email указан неверно."
    ]
  }
}
```

Возможные HTTP-статусы:

| Статус | Значение |
|---:|---|
| `201` | обращение успешно обработано |
| `422` | ошибка валидации |
| `429` | превышен rate limit |
| `500` | внутренняя ошибка |
| `503` | ошибка почтового сервиса |

### `GET /api/health`

Проверяет доступность Laravel-приложения.

Ответ:

```json
{
  "status": "ok"
}
```

### `GET /api/metrics`

В текущей версии endpoint статистики не реализован.

Документация API находится в файле:

```text
openapi.yaml
```

## 5. AI-интеграция

AI используется для предварительной обработки входящих обращений через любой API, совместимый с форматом OpenAI Chat Completions.

Модель определяет:

- категорию обращения;
- тональность;
- приоритет;
- краткое содержание;
- является ли сообщение спамом.

Ожидаемый формат:

```json
{
  "category": "job_offer",
  "sentiment": "positive",
  "priority": "medium",
  "summary": "Пользователь предлагает работу backend-разработчиком",
  "is_spam": false
}
```

Приложение добавляет поле:

```json
{
  "source": "ai"
}
```

Поддерживаемые категории:

```text
project_request
job_offer
partnership
question
complaint
spam
other
```

Поддерживаемая тональность:

```text
positive
neutral
negative
```

Поддерживаемый приоритет:

```text
low
medium
high
```

В выбранный AI-провайдер отправляются имя и комментарий пользователя. Телефон и email в AI-запрос не передаются.

### Используемый промпт

Системный промпт хранится в `config/ai.php`. Он не зависит от конкретного провайдера и требует от модели вернуть только JSON без дополнительного текста.

Пример логики промпта:

```text
Проанализируй обращение пользователя.

Определи:
- category;
- sentiment;
- priority;
- summary;
- is_spam.

Верни только корректный JSON без Markdown и дополнительных пояснений.
```

## 6. Fallback

AI является внешним сервисом и не должен быть критической точкой отказа.

Fallback применяется при:

- сетевой ошибке;
- таймауте;
- HTTP-ошибке AI-провайдера;
- отсутствии `choices.0.message.content`;
- некорректном JSON.

Fallback-результат:

```json
{
  "category": "other",
  "sentiment": "neutral",
  "priority": "low",
  "summary": "AI-анализ временно недоступен",
  "is_spam": false,
  "source": "fallback"
}
```

При сбое AI обращение продолжает обрабатываться, а письма отправляются с резервным результатом.

## 7. Что сделано с помощью AI

AI использовался как помощник при разработке, но решения проверялись и дорабатывались вручную.

С помощью AI:

- обсуждалась структура Laravel-проекта;
- подготавливались варианты сервисного слоя;
- разбирались ошибки SMTP, SSL и подключения к AI API;
- составлялись примеры OpenAPI;
- подготавливались тестовые JSON-запросы;
- улучшалась обработка fallback;
- создавался черновик README;
- подготавливался адаптивный CSS для Blade-формы.

Примеры запросов к AI:

```text
Как вынести AI-интеграцию в отдельный Laravel-сервис?
```

```text
Как обработать недоступность OpenAI-совместимого API так, чтобы форма продолжала работать?
```

```text
Как реализовать rate limiting для POST /api/contact?
```

```text
Как описать POST /api/contact и GET /api/health в OpenAPI?
```

```text
Как передать результат AI-анализа в Laravel Mailable и Blade-шаблон письма?
```

Вручную пришлось исправлять:

- SSL CA-сертификаты PHP;
- SMTP-подключение;
- адрес OpenAI-совместимый API;
- передачу строки вместо массива в `message.content`;
- обработку JSON-ответа модели;
- event listener формы с `onsubmit` на `submit`;
- чтение `result.message` вместо `response.message`;
- подключение CSS через `public/css/app.css`;
- маршруты Blade;
- согласование кодов HTTP-ошибок;
- fallback и логирование исключений.

## 8. Хранение данных

### Логи

HTTP-логи сохраняются стандартным механизмом Laravel:

```text
storage/logs/laravel.log
```

Middleware записывает:

- HTTP-метод;
- путь;
- IP-адрес;
- статус ответа;
- длительность обработки.

Тело запроса, email, телефон и комментарий в общий HTTP-лог не записываются.

Ошибки AI записываются с уровнем `warning`.

### Rate limiting

Для `POST /api/contact` используется встроенный middleware Laravel:

```php
throttle:3,25
```

Это означает не более трёх запросов за 25 минут для одного ключа rate limiter. Для гостевого запроса ключ обычно формируется на основе IP-адреса.

При стандартном:

```env
CACHE_STORE=database
```

счётчики хранятся в таблице `cache` SQLite.

Это временные служебные данные, а не аналитическая статистика.

### Статистика

В текущей версии отдельная статистика обращений не хранится.

Отсутствуют:

- `GET /api/metrics`;
- `MetricsService`;
- `MetricsController`;
- JSON-файл метрик;
- сохранение агрегатов по категориям, тональности и приоритетам.

Сами обращения также не сохраняются в базе данных.

## 9. Логика обработки обращения

```text
POST /api/contact
        ↓
ContactRequest
        ↓
ContactController
        ↓
ContactService
        ↓
AIService
        ↓
AI-анализ или fallback
        ↓
MailService
        ↓
Письмо владельцу и письмо пользователю
        ↓
201 Created
```

## 10. Проверка проекта

Список маршрутов:

```powershell
php artisan route:list
```

Запуск тестов:

```powershell
php artisan test
```

В текущей версии присутствуют только базовые шаблонные тесты Laravel. Контактный API, AI, почта, fallback и rate limiting отдельными автоматическими тестами пока не покрыты.

## 11. Безопасность

- `.env` не хранится в Git;
- SMTP-пароли и OpenAI-совместимый API key задаются через окружение;
- тело обращения не записывается в общий HTTP-лог;
- телефон и email не передаются в AI;
- обращения не сохраняются в базе данных;
- публичный endpoint защищён валидацией и rate limiting.
