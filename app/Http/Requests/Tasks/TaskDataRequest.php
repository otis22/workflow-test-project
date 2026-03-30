<?php

namespace App\Http\Requests\Tasks;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class TaskDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<int, ValidationRule|string>
     */
    abstract protected function dueDateRules(): array;

    /**
     * @return array<string, ValidationRule|array<int, mixed>|string>
     */
    public function rules(): array
    {
        /** @var Project $project */
        $project = $this->route('project');

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(Task::STATUSES)],
            'priority' => ['required', Rule::in(Task::PRIORITIES)],
            'due_date' => $this->dueDateRules(),
            'assignee_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
                Rule::in($project->members()->pluck('users.id')->all()),
            ],
        ];
    }
}
