<?php

namespace Devkit2026\JwtAuth\Http\Requests;

use Devkit2026\JwtAuth\DTO\LoginDto;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
        ];
    }

    public function toDto(): LoginDto
    {
        return new LoginDto(
            email: $this->input('email'),
            password: $this->input('password')
        );
    }
}
