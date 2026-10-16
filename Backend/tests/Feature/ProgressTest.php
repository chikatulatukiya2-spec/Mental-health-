<?php

namespace Tests\Feature;

use App\Models\Goal;
use App\Models\MoodEntry;
use App\Models\Screening;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/progress')->assertStatus(401);
    }

    public function test_wellbeing_score_is_null_without_recent_moods(): void
    {
        $response = $this->actingAs(User::factory()->create(), 'sanctum')
            ->getJson('/api/progress')
            ->assertStatus(200)
            ->assertJsonPath('data.period', 'Last 7 days');

        $this->assertNull($response->json('data.wellbeing_score'));
    }

    public function test_wellbeing_score_is_computed_from_recent_moods(): void
    {
        $user = User::factory()->create();
        MoodEntry::create(['user_id' => $user->id, 'mood' => 'Happy', 'recorded_at' => now()]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/progress')
            ->assertStatus(200);

        // Happy maps to 5/5 => 100.
        $this->assertEquals(100, $response->json('data.wellbeing_score'));
        $this->assertEquals([5], $response->json('data.mood_series'));
    }

    public function test_moods_older_than_the_window_are_excluded(): void
    {
        $user = User::factory()->create();
        MoodEntry::create(['user_id' => $user->id, 'mood' => 'Happy', 'recorded_at' => now()->subDays(10)]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/progress');

        $this->assertNull($response->json('data.wellbeing_score'));
    }

    public function test_goal_counts_are_reported(): void
    {
        $user = User::factory()->create();
        Goal::create(['user_id' => $user->id, 'title' => 'A', 'completed' => true]);
        Goal::create(['user_id' => $user->id, 'title' => 'B', 'completed' => false]);
        Goal::create(['user_id' => User::factory()->create()->id, 'title' => 'Other']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/progress')
            ->assertStatus(200)
            ->assertJsonPath('data.goals_total', 2)
            ->assertJsonPath('data.goals_completed', 1);
    }

    public function test_latest_screening_is_included(): void
    {
        $user = User::factory()->create();
        Screening::create([
            'user_id' => $user->id,
            'type' => 'PHQ-9',
            'answers' => array_fill(0, 9, 0),
            'score' => 0,
            'result_label' => 'Minimal symptoms',
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/progress')
            ->assertStatus(200);

        $this->assertEquals(0, $response->json('data.latest_screening.score'));
    }
}
