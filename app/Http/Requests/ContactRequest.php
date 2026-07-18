<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "name" => ["required", "string", "min:2", "max:100"],
            "phone" => ["required", "string", "max:30"],
            "email" => ["required", "email", "max:255"],
            "comment" => ["required", "string", "min:5", "max:2000"]
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Укажите имя.',
            'phone.required' => 'Укажите телефон.',
            'email.required' => 'Укажите email.',
            'email.email' => 'Email указан неверно.',
            'comment.required' => 'Добавьте комментарий.',
        ];
    }
}
