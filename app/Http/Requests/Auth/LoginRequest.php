<?php

namespace App\Http\Requests\Auth;

use App\Models\AuditLog;
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
     * A09:2025 — Security Logging & Alerting Failures. Neither a successful nor a failed
     * login used to leave any trace at all — no way to investigate a compromised account
     * after the fact, or notice a spray/credential-stuffing pattern in progress. Both
     * outcomes are now audit-logged with enough context (email or user id, IP) to
     * investigate.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $throttle = app(LoginThrottle::class);
        $email = $this->string('email')->toString();
        $ip = $this->ip();

        if ($throttle->tooManyAttempts($email, $ip)) {
            throw ValidationException::withMessages([
                'email' => 'Too many login attempts. Please try again later.',
            ]);
        }

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            $throttle->increment($email, $ip);

            AuditLog::create([
                'user_id' => null,
                'event' => 'auth.login_failed',
                'context' => ['email' => $email],
                'ip_address' => $ip,
            ]);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $throttle->clear($email);

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'auth.login_succeeded',
            'context' => [],
            'ip_address' => $ip,
        ]);
    }
}
