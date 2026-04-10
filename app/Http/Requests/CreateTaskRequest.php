<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Task\Priority;
use App\Domain\Task\Status;
use Illuminate\Foundation\Http\FormRequest;

final class CreateTaskRequest extends FormRequest
{
    #[\Override]
    protected function prepareForValidation(): void
    {
        $title = $this->input('title');
        if (is_string($title)) {
            $this->merge(['title' => trim($title)]);
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $statuses = implode(',', array_map(fn (Status $status): string => $status->value, Status::cases()));
        $priorities = implode(',', array_map(fn (Priority $priority): string => $priority->value, Priority::cases()));

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'string', 'in:'.$statuses],
            'priority' => ['required', 'string', 'in:'.$priorities],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
