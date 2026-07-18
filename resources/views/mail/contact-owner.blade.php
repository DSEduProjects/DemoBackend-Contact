<h1>Новое обращение с сайта</h1>

<p><strong>Имя:</strong> {{ $contact['name'] }}</p>
<p><strong>Телефон:</strong> {{$contact['phone']}}</p>
<p><strong>Почта:</strong> {{$contact['email']}}</p>
<p><strong>Комментарий:</strong></p>
<p>{{$contact['comment']}}</p>

<hr>

<h2>AI-анализ</h2>
<p>
    <strong>Категория:</strong> 
    {{ config('ai.analyze_values_text.category.' . ($analysis['category'] ?? 'other'), 'Другое') }}
</p>
<p>
    <strong>Тональность:</strong> 
    {{ config('ai.analyze_values_text.sentiment.' . ($analysis['sentiment'] ?? 'neutral'), 'Нейтрально') }}
</p>
<p>
    <strong>Приоритет:</strong> 
    {{ config('ai.analyze_values_text.priority.' . ($analysis['priority'] ?? 'low'), 'Низкий') }}
</p>
<p>
    <strong>Краткое содержание:</strong> 
    {{ $analysis['summary'] }}
</p>

<p><strong>{{ ($analysis['is_spam'] ?? false) ? 'Данное письмо является СПАМОМ' : '' }}</strong></p>
