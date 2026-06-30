<?php

namespace Pterodactyl\Rules;

use Illuminate\Contracts\Validation\Rule;

class UserEmail implements Rule
{
    /**
     * @param string $attribute
     */
    public function passes($attribute, $value): bool
    {
        if (!is_string($value)) {
            return true;
        }

        $local = explode('@', $value, 2)[0] ?? '';
        $local = trim($local, '"');

        return !str_starts_with($local, '-');
    }

    public function message(): string
    {
        return 'Поле :attribute не должно начинаться с дефиса.';
    }

    public function __toString(): string
    {
        return 'p_user_email';
    }
}