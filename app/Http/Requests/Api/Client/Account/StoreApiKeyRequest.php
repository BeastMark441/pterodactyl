<?php

namespace Pterodactyl\Http\Requests\Api\Client\Account;

use IPTools\Range;
use Pterodactyl\Models\ApiKey;
use Illuminate\Validation\Validator;
use Pterodactyl\Http\Requests\Api\Client\ClientApiRequest;

class StoreApiKeyRequest extends ClientApiRequest
{
    public function rules(): array
    {
        $rules = ApiKey::getRules();

        return [
            'description' => $rules['memo'],
            'allowed_ips' => [...$rules['allowed_ips'], 'max:50'],
            'allowed_ips.*' => 'string',
        ];
    }

    /**
     * Проверьте, что каждое из введенных значений действительно является допустимым.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (!is_array($ips = $this->input('allowed_ips'))) {
                return;
            }

            foreach ($ips as $index => $ip) {
                $valid = false;
                try {
                    $valid = Range::parse($ip)->valid();
                } catch (\Exception $exception) {
                    if ($exception->getMessage() !== 'Неверный формат IP-адреса') {
                        throw $exception;
                    }
                } finally {
                    $validator->errors()->addIf(!$valid, "allowed_ips.{$index}", '"' . $ip . '" не является допустимым IP-адресом или диапазоном CIDR.');
                }
            }
        });
    }
}
