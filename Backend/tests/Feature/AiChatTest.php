<?php

namespace Tests\Feature;

use App\Models\AiMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_history_requires_authentication(): void
    {
        $this->getJson('/api/ai/history')->assertStatus(401);
    }

    public function test_chat_requires_authentication(): void
    {
        $this->postJson('/api/ai/chat', ['message' => 'hello'])->assertStatus(401);
    }

    public function test_chat_requires_a_message(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/ai/chat', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }

    public function test_chat_stores_the_user_and_assistant_messages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/ai/chat', ['message' => 'I feel a bit tired today'])
            ->assertStatus(200)
            ->assertJsonPath('data.emergency', false)
            ->assertJsonStructure(['data' => ['reply', 'emergency'], 'disclaimer']);

        $this->assertDatabaseCount('ai_messages', 2);
        $this->assertDatabaseHas('ai_messages', ['user_id' => $user->id, 'sender' => 'user']);
        $this->assertDatabaseHas('ai_messages', ['user_id' => $user->id, 'sender' => 'ai']);
    }

    public function test_chat_flags_emergency_language(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/ai/chat', ['message' => 'I want to end my life'])
            ->assertStatus(200)
            ->assertJsonPath('data.emergency', true);
    }

    public function test_history_is_scoped_to_the_user(): void
    {
        $user = User::factory()->create();
        AiMessage::create(['user_id' => $user->id, 'sender' => 'user', 'message' => 'mine']);
        AiMessage::create(['user_id' => User::factory()->create()->id, 'sender' => 'user', 'message' => 'theirs']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/ai/history')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.message', 'mine');
    }
}
