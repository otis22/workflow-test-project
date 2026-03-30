<?php

namespace App\Application\Tasks;

use App\Models\User;
use Carbon\CarbonImmutable;

readonly class TaskData
{
    public function __construct(
        public string $title,
        public ?string $description,
        public string $status,
        public string $priority,
        public ?string $dueDate,
        public ?User $assignee,
    ) {}

    /**
     * @return array<string, int|string|null>
     */
    public function toPersistenceAttributes(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'due_date' => $this->dueDate === null ? null : CarbonImmutable::parse($this->dueDate)->toDateString(),
            'assignee_id' => $this->assignee?->id,
        ];
    }
}
