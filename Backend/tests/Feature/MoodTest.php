<?php

namespace Tests\Feature;

use App\Models\MoodEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MoodTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/moods')->assertStatus(401);
    }

    public function test_store_requires_authentication(): void
    {
        $this->postJson('/api/moods', ['mood' => 'Happy'])->assertStatus(401);
    }

    public function test_store_rejects_an_unknown_mood(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/moods', ['mood' => 'Ecstatic'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['mood']);
    }

    public function test_store_persists_a_mood_and_defaults_recorded_at(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/moods', ['mood' => 'Calm', 'emoji' => '😌', 'note' => 'quiet day'])
            ->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.mood', 'Calm');

        $this->assertDatabaseHas('mood_entries', [
            'user_id' => $user->id,
            'mood' => 'Calm',
            'note' => 'quiet day',
            'recorded_at' => now()->toDateString(),
        ]);
    }

    public function test_index_only_returns_the_users_own_moods(): void
    {
        $user = User::factory()->create();
        MoodEntry::create(['user_id' => $user->id, 'mood' => 'Happy', 'recorded_at' => now()]);
        MoodEntry::create(['user_id' => User::factory()->create()->id, 'mood' => 'Sad', 'recorded_at' => now()]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/moods')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.mood', 'Happy');
    }

    public function test_show_is_scoped_to_the_owner(): void
    {
        $entry = MoodEntry::create([
            'user_id' => User::factory()->create()->id,
            'mood' => 'Sad',
            'recorded_at' => now(),
        ]);

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->getJson("/api/moods/{$entry->id}")
            ->assertStatus(404);
    }

    public function test_destroy_is_scoped_to_the_owner(): void
    {
        $entry = MoodEntry::create([
            'user_id' => User::factory()->create()->id,
            'mood' => 'Sad',
            'recorded_at' => now(),
        ]);

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->deleteJson("/api/moods/{$entry->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('mood_entries', ['id' => $entry->id]);
    }

    public function test_owner_can_delete_their_mood(): void
    {
        $user = User::factory()->create();
        $entry = MoodEntry::create(['user_id' => $user->id, 'mood' => 'Okay', 'recorded_at' => now()]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/moods/{$entry->id}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('mood_entries', ['id' => $entry->id]);
    }
}
