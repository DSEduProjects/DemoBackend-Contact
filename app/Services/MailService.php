<?php

namespace App\Services;

use App\Exceptions\MailSendingException;
use App\Mail\ContactMailOwner;
use App\Mail\ContactMailUser;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Throwable;

class MailService {
    public function send(array $contact, array $analysis): void {
        try {
            Mail::to(Config::get("mail.owner_address"))
                ->send(new ContactMailOwner($contact, $analysis));
            Mail::to($contact['email'])
                ->send(new ContactMailUser($contact));
        } catch (Throwable $exception) {
            report($exception);

            throw new MailSendingException(
                'Не удалось отправить письмо',
                previous: $exception
            );
        }
    }
}