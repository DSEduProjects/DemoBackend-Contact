<?php
namespace App\Services;

use App\Services\MailService;
use App\Services\AIService;

class ContactService {
    public function __construct(
        private readonly MailService $mailService,
        private readonly AIService $aiService
    ) {}

    public function handle(array $data): array {
        $analysis = $this->aiService->analyzeRequest($data);
        $this->mailService->send($data, $analysis);

        return [
            "success" => true,
            "message" => "Ваше обращение принято"
        ];
    }
}