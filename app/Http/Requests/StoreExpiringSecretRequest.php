<?php

namespace App\Http\Requests;

use App\TimeUnit;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use UnitEnum;

class StoreExpiringSecretRequest extends FormRequest
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
            'content' => [Rule::required(), Rule::string()],
            'expires_in' => [Rule::required(), Rule::numeric()->integer()->min(1)],
            'expires_unit' => [Rule::required(), Rule::in(array_map(fn (UnitEnum $val) => $val->value, TimeUnit::cases()))],
            'recipients' => [Rule::nullable(), Rule::string(), function (string $attribute, mixed $value, \Closure $fail): void {
                if (! is_string($value)) {
                    return;
                }

                foreach ($this->recipients() as $address) {
                    if (Validator::make(['email' => $address], ['email' => [Rule::email()]])->fails()) {
                        $fail('Each recipient must be a valid email address, one per line.');

                        return;
                    }
                }
            }],
        ];
    }

    /**
     * @return list<string>
     */
    public function recipients(): array
    {
        $recipients = $this->input('recipients');

        if (! is_string($recipients)) {
            return [];
        }

        return array_values(array_filter(
            array_map('trim', preg_split('/\R/', $recipients) ?: []),
            fn (string $recipient): bool => $recipient !== '',
        ));
    }
}
