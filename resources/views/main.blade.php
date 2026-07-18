<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>DemoBackend Contact API</title>
        <link rel="stylesheet" href="./css/app.css">
    </head>
    <body>
        <header>
            <span>DemoBackend API</span>
        </header>
        <main>
            <h1>Обратная связь</h1>
            <div class="error hidden"></div>
            <div class="msg hidden"></div>
            <form id="contact">
                <div class="contact-form">
                    <div class="input-container">
                        <label for="name">Имя</label>
                        <input type="text" name="name" minlength="2" maxlength="100" required>
                    </div>
                    <div class="input-container">
                        <label for="phone">Телефон</label>
                        <input type="text" name="phone" maxlength="30" required>
                    </div>
                    <div class="input-container">
                        <label>Почта</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="input-container">
                        <label for="comment">Комментарий</label>
                        <textarea name="comment" minlength="10" maxlength="2000" required></textarea>
                    </div>
                    <div class="input-container">
                        <button type="submit" id="contact-submit">Отправить</button>
                    </div>
                </div>
            </form>
        </main>
    </body>

    <script>
        const apiUrl = "{{config('app.url')}}";
        const form = document.getElementById('contact');
        const msgBox = document.querySelector('.msg');
        const errorBox = document.querySelector('.error');
        const submitBtn = document.getElementById('contact-submit');
        let hideTimeout = null;

        function setMessage(divContainer, message) {
            divContainer.textContent = message;
            divContainer.classList.toggle('hidden');

            hideTimeout = setTimeout(() => {
                divContainer.classList.toggle('hidden');
                hideTimeout = null;
            }, 5000);
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            submitBtn.disabled = true;

            if (hideTimeout) {
                errorBox.classList.add('hidden');
                msgBox.classList.add('hidden');

                clearTimeout(hideTimeout);
                hideTimeout = null;
            }

            const formData = new FormData(form);

            const request = await fetch(`${apiUrl}/api/contact`, {
                method: "POST",
                body: formData
            });

            const result = await request.json();

            submitBtn.disabled = false;

            if (!result.success) {
                setMessage(errorBox, result.message);
                return;
            }

            setMessage(msgBox, result.message);
        })
    </script>
</html>