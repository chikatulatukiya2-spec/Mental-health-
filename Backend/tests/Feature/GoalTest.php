<?php

namespace Tests\Feature;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoalTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/goals')->assertStatus(401);
    }

    public function test_store_requires_a_title(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/goals', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_store_persists_a_goal_and_defaults_the_category(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/goals', ['title' => 'Walk daily'])
            ->assertStatus(201)
            ->assertJsonPath('data.category', 'Wellbeing');

        $this->assertDatabaseHas('goals', [
            'user_id' => $user->id,
            'title' => 'Walk daily',
            'category' => 'Wellbeing',
            'completed' => false,
        ]);
    }

    public function test_marking_a_goal_complete_sets_completed_at(): void
    {
        $user = User::factory()->create();
        $goal = Goal::create(['user_id' => $user->id, 'title' => 'Walk daily']);

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/goals/{$goal->id}", ['completed' => true])
            ->assertStatus(200)
            ->assertJsonPath('data.completed', true);

        $this->assertNotNull($goal->fresh()->completed_at);
    }

    public function test_update_is_scoped_to_the_owner(): void
    {
        $goal = Goal::create([
            'user_id' => User::factory()->create()->id,
            'title' => 'Theirs',
        ]);

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->putJson("/api/goals/{$goal->id}", ['title' => 'Hacked'])
            ->assertStatus(404);

        $this->assertDatabaseHas('goals', ['id' => $goal->id, 'title' => 'Theirs']);
    }

    public function test_destroy_is_scoped_to_the_owner(): void
    {
        $goal = Goal::create([
            'user_id' => User::factory()->create()->id,
            'title' => 'Theirs',
        ]);

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->deleteJson("/api/goals/{$goal->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('goals', ['id' => $goal->id]);
    }
}
