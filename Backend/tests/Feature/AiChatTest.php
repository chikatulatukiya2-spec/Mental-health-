<?php

namespace Tests\Feature;

use App\Models\AiMessage;
use App\Models\SupportResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
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

    public function test_clear_history_requires_authentication(): void
    {
        $this->deleteJson('/api/ai/history')->assertStatus(401);
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

    /**
     * Every one of these is a plausible real-world disclosure that the old
     * substring list missed. "I am suicidal" alone makes the detector worth
     * rebuilding.
     */
    #[DataProvider('crisisPhrases')]
    public function test_chat_flags_emergency_language(string $message): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/ai/chat', ['message' => $message])
            ->assertStatus(200)
            ->assertJsonPath('data.emergency', true);
    }

    public static function crisisPhrases(): array
    {
        return [
            'plain disclosure' => ['I want to end my life'],
            'the most common phrasing' => ['I am suicidal'],
            'variant phrasing' => ['I feel suicidal'],
            'hyphenated self-harm' => ['I keep having self-harm thoughts'],
            'plain self harm' => ['I think about self harm sometimes'],
            'progressive verb' => ['I keep killing myself over small mistakes'], // ambiguous, still safe to flag
            'literal self harm' => ['I am hurting myself again'],
            'harm myself' => ['I want to harm myself'],
            'cutting' => ['I have been cutting myself'],
            'want to die' => ['Sometimes I just want to die'],
            'end it all' => ['Everything is falling apart, I want to end it all'],
            'no reason to live' => ['I have no reason to live anymore'],
            'nothing to live for' => ['There is nothing to live for'],
            'dont want to live' => ["I don't want to live anymore"],
            'better off dead' => ['My family would be better off dead... better off without me'],
            'own life' => ['I might take my own life'],
            'slang kms' => ['idk what to do kms'],
            'leet obfuscation' => ['i think about su1c1de a lot'],
            'give up on life' => ['I want to give up on life'],
        ];
    }

    public function test_crisis_reply_includes_real_emergency_resources(): void
    {
        SupportResource::create([
            'title' => 'Lifeline Zambia (933)',
            'description' => 'Free 24/7 counselling.',
            'phone' => '933',
            'type' => 'helpline',
            'is_emergency' => true,
            'active' => true,
        ]);
        SupportResource::create([
            'title' => 'Ordinary resource',
            'type' => 'professional',
            'active' => true,
        ]);

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/ai/chat', ['message' => 'I am suicidal'])
            ->assertStatus(200)
            ->assertJsonPath('data.emergency', true)
            ->assertJsonPath('data.resources.0.title', 'Lifeline Zambia (933)')
            ->assertJsonPath('data.resources.0.phone', '933')
            ->assertJsonCount(1, 'data.resources');
    }

    public function test_ordinary_messages_are_not_flagged_as_crisis(): void
    {
        $safe = [
            'I downloaded an app today',          // "download" must not match "down"
            'I am so down because the download failed',
            'the talk about suicide prevention at school was informative',
        ];

        foreach ($safe as $index => $message) {
            // The first two must never flag. The third is an accepted
            // over-trigger: erring toward safety is the intended trade-off.
            $expected = $index === 2;

            $this->actingAs(User::factory()->create(), 'sanctum')
                ->postJson('/api/ai/chat', ['message' => $message])
                ->assertStatus(200)
                ->assertJsonPath('data.emergency', $expected);
        }
    }

    public function test_multi_topic_messages_detect_every_topic(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/ai/chat', ['message' => "I've been so anxious about everything and I can't sleep at all"])
            ->assertStatus(200)
            ->assertJsonPath('data.emergency', false)
            ->assertJsonPath('data.topics.0', 'anxiety')
            ->assertJsonPath('data.topics.1', 'sleep');
    }

    public function test_short_follow_ups_keep_the_previous_topic(): void
    {
        $user = User::factory()->create();

        $first = $this->chat($user, 'I have been really anxious about my exams');
        $this->assertSame('anxiety', $this->lastChatResponse['topics'][0] ?? null);

        $bridge = $this->chat($user, 'yes exactly');

        $this->assertTrue($this->lastChatResponse['from_context']);
        $this->assertSame('anxiety', $this->lastChatResponse['topics'][0]);
        $this->assertFalse($this->lastChatResponse['emergency']);
        // A bridge asks rather than replaying the full topic reply verbatim.
        $this->assertNotSame($first, $bridge);
        $this->assertStringContainsString('anxiety', $bridge);
    }

    public function test_same_topic_twice_gets_a_different_reply(): void
    {
        $user = User::factory()->create();

        $first = $this->chat($user, 'I have been feeling really anxious lately');
        $second = $this->chat($user, 'The anxiety is back again today');

        $this->assertNotSame($first, $second);
    }

    public function test_repeated_topic_replies_never_repeat_verbatim(): void
    {
        $user = User::factory()->create();

        $replies = [];
        foreach (range(1, 6) as $day) {
            $replies[] = $this->chat($user, "Day {$day} and the anxiety is still here with me");
        }

        $this->assertCount(6, array_unique($replies), 'Assistant repeated a reply verbatim');
    }

    public function test_reply_reflects_the_users_own_words(): void
    {
        $user = User::factory()->create();

        $reply = $this->chat($user, 'I have been so stressed because of my exams');

        $this->assertStringContainsString('exams', $reply);
    }

    public function test_fallback_replies_vary_across_a_conversation(): void
    {
        $user = User::factory()->create();

        $first = $this->chat($user, 'the weather is strange today');
        $second = $this->chat($user, 'I had a strange day');

        $this->assertNotSame($first, $second);
    }

    public function test_greetings_get_a_friendly_greeting_back(): void
    {
        $user = User::factory()->create();

        $first = $this->chat($user, 'hello');
        $second = $this->chat($user, 'good morning');

        $this->assertSame([], $this->lastChatResponse['topics']);
        $this->assertFalse($this->lastChatResponse['from_context']);
        $this->assertStringNotContainsString('general wellbeing information', $first);
        $this->assertNotSame($first, $second);
    }

    public function test_thanks_is_acknowledged_instead_of_reopening_the_topic(): void
    {
        $user = User::factory()->create();

        $this->chat($user, 'I am so down today');
        $reply = $this->chat($user, 'thanks, that helps a little');

        $this->assertFalse($this->lastChatResponse['from_context']);
        $this->assertTrue(
            str_contains($reply, 'welcome') || str_contains($reply, 'anytime'),
            "Expected a gratitude acknowledgement, got: {$reply}",
        );
    }

    public function test_personalized_reply_is_not_reused_without_its_opener(): void
    {
        $user = User::factory()->create();

        // First reply carries an opener ("Thank you for telling me more about
        // pressure..."), which used to hide the core variant from the
        // no-repeat check and cause an exact repeat on the next message.
        $first = $this->chat($user, 'I cannot sleep at all because of the pressure');
        $second = $this->chat($user, 'the pressure');

        $this->assertNotSame($first, $second);
    }

    private ?array $lastChatResponse = null;

    private function chat(User $user, string $message): string
    {
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/ai/chat', ['message' => $message])
            ->assertStatus(200);

        $this->lastChatResponse = $response->json('data');

        return $response->json('data.reply');
    }

    public function test_unrelated_short_message_falls_back_to_general_reply(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/ai/chat', ['message' => 'hello'])
            ->assertStatus(200)
            ->assertJsonPath('data.from_context', false)
            ->assertJsonPath('data.topics', []);
    }

    public function test_chat_is_rate_limited(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 20; $i++) {
            $this->actingAs($user, 'sanctum')
                ->postJson('/api/ai/chat', ['message' => "message {$i}"])
                ->assertStatus(200);
        }

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/ai/chat', ['message' => 'one too many'])
            ->assertStatus(429);
    }

    public function test_history_is_bounded_to_the_most_recent_messages(): void
    {
        $user = User::factory()->create();
        $messages = collect(range(1, 110))->map(fn ($i) => [
            'user_id' => $user->id, 'sender' => 'user', 'message' => "m{$i}",
        ])->all();
        AiMessage::insert($messages);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/ai/history')
            ->assertStatus(200)
            ->assertJsonCount(100, 'data');

        // Oldest-first ordering preserved, and the newest messages are the
        // ones kept: m11 (oldest kept) ... m110 (newest).
        $this->assertSame('m11', $response->json('data.0.message'));
        $this->assertSame('m110', $response->json('data.99.message'));
    }

    public function test_clear_history_deletes_only_the_users_messages(): void
    {
        $user = User::factory()->create();
        AiMessage::create(['user_id' => $user->id, 'sender' => 'user', 'message' => 'mine']);
        AiMessage::create(['user_id' => $user->id, 'sender' => 'ai', 'message' => 'reply']);
        AiMessage::create(['user_id' => User::factory()->create()->id, 'sender' => 'user', 'message' => 'theirs']);

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/ai/history')
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseCount('ai_messages', 1);
        $this->assertDatabaseHas('ai_messages', ['message' => 'theirs']);
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
