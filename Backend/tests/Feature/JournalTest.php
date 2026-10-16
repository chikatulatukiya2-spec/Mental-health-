<?php

namespace Tests\Feature;

use App\Models\JournalEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/journals')->assertStatus(401);
    }

    public function test_store_requires_title_and_content(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/journals', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'content']);
    }

    public function test_store_persists_an_entry_and_defaults_entry_date(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/journals', ['title' => 'A good day', 'content' => 'I felt calm.'])
            ->assertStatus(201)
            ->assertJsonPath('data.title', 'A good day');

        $this->assertDatabaseHas('journal_entries', [
            'user_id' => $user->id,
            'title' => 'A good day',
        ]);

        $this->assertSame(
            now()->toDateString(),
            JournalEntry::where('user_id', $user->id)->firstOrFail()->entry_date->toDateString()
        );
    }

    public function test_index_is_scoped_to_the_user(): void
    {
        $user = User::factory()->create();
        JournalEntry::create([
            'user_id' => $user->id,
            'title' => 'Mine',
            'content' => 'content',
            'entry_date' => now(),
        ]);
        JournalEntry::create([
            'user_id' => User::factory()->create()->id,
            'title' => 'Theirs',
            'content' => 'content',
            'entry_date' => now(),
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/journals')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data.data');
    }

    public function test_update_is_scoped_to_the_owner(): void
    {
        $entry = JournalEntry::create([
            'user_id' => User::factory()->create()->id,
            'title' => 'Theirs',
            'content' => 'content',
            'entry_date' => now(),
        ]);

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->putJson("/api/journals/{$entry->id}", ['title' => 'Hacked'])
            ->assertStatus(404);

        $this->assertDatabaseHas('journal_entries', ['id' => $entry->id, 'title' => 'Theirs']);
    }

    public function test_destroy_is_scoped_to_the_owner(): void
    {
        $entry = JournalEntry::create([
            'user_id' => User::factory()->create()->id,
            'title' => 'Theirs',
            'content' => 'content',
            'entry_date' => now(),
        ]);

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->deleteJson("/api/journals/{$entry->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('journal_entries', ['id' => $entry->id]);
    }

    public function test_owner_can_update_their_entry(): void
    {
        $user = User::factory()->create();
        $entry = JournalEntry::create([
            'user_id' => $user->id,
            'title' => 'Original',
            'content' => 'content',
            'entry_date' => now(),
        ]);

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/journals/{$entry->id}", ['title' => 'Updated'])
            ->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated');

        $this->assertDatabaseHas('journal_entries', ['id' => $entry->id, 'title' => 'Updated']);
    }
}
