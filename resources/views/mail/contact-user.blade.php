<h1>Здравствуйте, {{ $contact['name'] }}!</h1>

<p>Ваше обращение принято.</p>

<p>Мы получили ваш комментарий:</p>

<blockquote>
    {{ $contact['comment'] }}
</blockquote>

<p>Ответ будет отправлен на адрес {{ $contact['email'] }}.</p>