<?php

namespace Devkit2026\JwtAuth\Http\Requests;

use Devkit2026\JwtAuth\DTO\RegisterDto;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ];
    }

    public function toDto(): RegisterDto
    {
        return new RegisterDto(
            email: $this->input('email'),
            password: $this->input('password')
        );
    }
}
