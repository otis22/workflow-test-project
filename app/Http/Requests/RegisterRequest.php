<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Application\User\RegisterUser;
use Illuminate\Foundation\Http\FormRequest;

final class RegisterRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:'.RegisterUser::MIN_PASSWORD_LENGTH, 'confirmed'],
        ];
    }
}
