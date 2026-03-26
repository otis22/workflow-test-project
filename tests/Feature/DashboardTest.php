<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class DashboardTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function dashboard_shows_my_tasks(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);
        $project->members()->attach($user->id);
        Task::factory()->create([
            'project_id' => $project->id,
            'creator_id' => $user->id,
            'assignee_id' => $user->id,
            'title' => 'My Assigned Task',
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertStatus(200)
            ->assertSee('My Assigned Task');
    }

    #[Test]
    public function dashboard_shows_upcoming_deadlines(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);
        $project->members()->attach($user->id);
        Task::factory()->create([
            'project_id' => $project->id,
            'creator_id' => $user->id,
            'title' => 'Due Soon',
            'due_date' => now()->addDays(3),
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertSee('Due Soon');
    }

    #[Test]
    public function dashboard_shows_user_projects(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id, 'name' => 'My Cool Project']);
        $project->members()->attach($user->id);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertSee('My Cool Project');
    }
}
