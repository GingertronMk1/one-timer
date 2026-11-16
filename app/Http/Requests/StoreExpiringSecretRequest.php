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
            'content' => ['required', 'string'],
            'expires_in' => ['required', 'integer', 'min:1'],
            'expires_unit' => ['required', Rule::in(array_map(fn (UnitEnum $val) => $val->value, TimeUnit::cases()))],
            'remaining_views' => ['required', 'integer', 'min:0'],
            'recipients' => ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! is_string($value)) {
                    return;
                }

                foreach (preg_split('/\R/', $value) as $address) {
                    $address = trim($address);
                    if ($address !== '' && Validator::make(['email' => $address], ['email' => 'email'])->fails()) {
                        $fail('Each recipient must be a valid email address, one per line.');

                        return;
                    }
                }
            }],
        ];
    }
}
