<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateProjectRequest extends FormRequest
{
    /**
     * Trim the name before validation so that a whitespace-only
     * submission is rejected by Laravel's `required` rule instead
     * of bypassing it and tripping the domain invariant (which
     * would bubble to a 500).
     *
     * Only string input is trimmed. Non-string input (e.g. `name[]=x`
     * array injection) is left untouched so Laravel's `string` rule
     * can reject it — casting it to a string here would coerce
     * arrays to the literal "Array" and silently bypass validation.
     */
    #[\Override]
    protected function prepareForValidation(): void
    {
        $name = $this->input('name');
        if (is_string($name)) {
            $this->merge(['name' => trim($name)]);
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
