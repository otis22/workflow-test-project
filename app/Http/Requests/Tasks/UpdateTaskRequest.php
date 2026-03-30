<?php

namespace App\Http\Requests\Tasks;

use Illuminate\Contracts\Validation\ValidationRule;

class UpdateTaskRequest extends TaskDataRequest
{
    /**
     * @return array<int, ValidationRule|string>
     */
    protected function dueDateRules(): array
    {
        return ['nullable', 'date'];
    }
}
