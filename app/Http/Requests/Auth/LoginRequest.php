<?php

namespace App\Http\Requests\Auth;

use App\Support\LoginThrottle;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $throttle = app(LoginThrottle::class);
        $email = $this->string('email')->toString();

        if ($throttle->tooManyAttempts($email)) {
            throw ValidationException::withMessages([
                'email' => 'Too many login attempts. Please try again later.',
            ]);
        }

        if (! Auth::attempt($this->only('email', 'password'))) {
            $throttle->increment($email);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $throttle->clear($email);
    }
}
