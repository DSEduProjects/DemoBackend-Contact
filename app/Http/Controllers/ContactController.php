<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\ContactService;
use App\Http\Requests\ContactRequest;

class ContactController extends Controller
{
    public function __construct(
        private readonly ContactService $contactService
    ) {}

    public function store(ContactRequest $contactRequest) {
        $result = $this->contactService->handle(
            $contactRequest->validated()
        );
        
        return response()->json($result, 201);
    }
}
