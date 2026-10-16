<?php

namespace Tests\Feature;

use App\Models\SelfCareResource;
use App\Models\SupportResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicResourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_information_index_is_public(): void
    {
        $this->getJson('/api/information')
            ->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_information_can_be_filtered_by_topic(): void
    {
        $this->getJson('/api/information?topic=Anxiety')
            ->assertStatus(200)
            ->assertJsonPath('data.topic', 'Anxiety');
    }

    public function test_information_returns_all_topics_for_unknown_topic(): void
    {
        $this->getJson('/api/information?topic=Unknown')
            ->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_support_index_lists_active_resources(): void
    {
        SupportResource::create(['title' => 'Helpline', 'active' => true]);
        SupportResource::create(['title' => 'Archived', 'active' => false]);

        $this->getJson('/api/support')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Helpline');
    }

    public function test_self_care_index_lists_active_resources(): void
    {
        SelfCareResource::create(['title' => 'Breathing', 'type' => 'breathing', 'active' => true]);
        SelfCareResource::create(['title' => 'Hidden', 'type' => 'breathing', 'active' => false]);

        $this->getJson('/api/self-care')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_self_care_can_be_filtered_by_type(): void
    {
        SelfCareResource::create(['title' => 'Breathing', 'type' => 'breathing', 'active' => true]);
        SelfCareResource::create(['title' => 'Sleep', 'type' => 'sleep', 'active' => true]);

        $this->getJson('/api/self-care?type=sleep')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Sleep');
    }

    public function test_self_care_show_returns_active_resource(): void
    {
        $resource = SelfCareResource::create(['title' => 'Breathing', 'type' => 'breathing', 'active' => true]);

        $this->getJson("/api/self-care/{$resource->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.title', 'Breathing');
    }

    public function test_self_care_show_hides_inactive_resource(): void
    {
        $resource = SelfCareResource::create(['title' => 'Hidden', 'type' => 'breathing', 'active' => false]);

        $this->getJson("/api/self-care/{$resource->id}")
            ->assertStatus(404);
    }
}
