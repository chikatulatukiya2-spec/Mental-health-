<?php

namespace Tests\Feature;

use App\Models\Screening;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ScreeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_questions_endpoint_is_public_and_returns_the_phq9(): void
    {
        $this->getJson('/api/screening/questions')
            ->assertStatus(200)
            ->assertJsonPath('type', 'phq9')
            ->assertJsonCount(9, 'questions')
            ->assertJsonCount(4, 'answers');
    }

    public function test_store_requires_authentication(): void
    {
        $this->postJson('/api/screenings', ['answers' => array_fill(0, 9, 0)])
            ->assertStatus(401);
    }

    public function test_store_validates_nine_answers_in_range(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/screenings', ['answers' => [0, 1, 2]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['answers']);

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/screenings', ['answers' => array_fill(0, 9, 9)])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['answers.0']);
    }

    #[DataProvider('scoreCases')]
    public function test_store_computes_score_and_result_label(array $answers, int $score, string $label): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/screenings', ['answers' => $answers])
            ->assertStatus(201)
            ->assertJsonPath('score', $score)
            ->assertJsonPath('result_label', $label);
    }

    public static function scoreCases(): array
    {
        return [
            'minimal' => [array_fill(0, 9, 0), 0, 'Minimal symptoms'],
            'mild' => [[2, 1, 1, 1, 1, 1, 1, 0, 1], 9, 'Mild symptoms'],
            'moderate' => [[2, 2, 2, 2, 2, 1, 1, 1, 1], 14, 'Moderate symptoms'],
            'moderately severe' => [[3, 2, 2, 2, 2, 2, 2, 2, 2], 19, 'Moderately severe symptoms'],
            'severe' => [array_fill(0, 9, 3), 27, 'Severe symptoms'],
        ];
    }

    public function test_store_flags_self_harm_when_question_nine_is_positive(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/screenings', ['answers' => [0, 0, 0, 0, 0, 0, 0, 0, 1]])
            ->assertStatus(201)
            ->assertJsonPath('self_harm_flag', true);
    }

    public function test_store_does_not_flag_self_harm_when_question_nine_is_zero(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/screenings', ['answers' => array_fill(0, 9, 0)])
            ->assertStatus(201)
            ->assertJsonPath('self_harm_flag', false);
    }

    public function test_store_persists_the_screening_for_the_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/screenings', ['answers' => array_fill(0, 9, 1)])
            ->assertStatus(201);

        $this->assertDatabaseHas('screenings', [
            'user_id' => $user->id,
            'type' => 'PHQ-9',
            'score' => 9,
        ]);
    }

    public function test_index_only_returns_the_users_own_screenings(): void
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

        Screening::create([
            'user_id' => User::factory()->create()->id,
            'type' => 'PHQ-9',
            'answers' => array_fill(0, 9, 3),
            'score' => 27,
            'result_label' => 'Severe symptoms',
            'completed_at' => now(),
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/screenings')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_show_is_scoped_to_the_owner(): void
    {
        $screening = Screening::create([
            'user_id' => User::factory()->create()->id,
            'type' => 'PHQ-9',
            'answers' => array_fill(0, 9, 0),
            'score' => 0,
            'result_label' => 'Minimal symptoms',
            'completed_at' => now(),
        ]);

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->getJson("/api/screenings/{$screening->id}")
            ->assertStatus(404);
    }
}
