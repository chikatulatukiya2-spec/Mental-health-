<?php

namespace Database\Seeders;

use App\Models\SelfCareResource;
use App\Models\SupportResource;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(['email' => 'demo@mindease.local'], ['name' => 'Tukiya', 'password' => Hash::make('Password123!')]);
        $support = [
            ['title' => 'Talk to a Trusted Person', 'description' => 'Family, friends or a mentor', 'type' => 'trusted_person'],
            ['title' => 'Mental Health Helpline', 'description' => 'Replace this demo contact with a verified local service before deployment.', 'phone' => '+260 950 123 456', 'type' => 'helpline'],
            ['title' => 'Lifeline Zambia (933)', 'description' => 'Free, confidential 24/7 counselling for adults. Toll-free from any network (Airtel, MTN, Zamtel).', 'phone' => '933', 'type' => 'helpline', 'is_emergency' => true],
            ['title' => 'Childline Zambia (116)', 'description' => 'Free 24/7 support for children and young people. Toll-free from any network.', 'phone' => '116', 'type' => 'helpline', 'is_emergency' => true],
            ['title' => 'Professional Support', 'description' => 'Find a counsellor or therapist', 'type' => 'professional'],
            ['title' => 'Emergency Help', 'description' => 'If you feel unsafe or at risk, seek immediate local emergency support.', 'type' => 'emergency', 'is_emergency' => true],
        ];
        foreach ($support as $item) {
            SupportResource::firstOrCreate(['title' => $item['title']], $item);
        }
        $care = [
            ['title' => 'Breathing Exercises', 'description' => 'Calm your mind', 'type' => 'breathing', 'content' => 'Try slow, comfortable breathing for one minute. Stop if you feel dizzy or uncomfortable.'],
            ['title' => 'Mindfulness', 'description' => 'Be present', 'type' => 'mindfulness', 'content' => 'Notice five things you can see, four you can touch, three you can hear, two you can smell and one you can taste.'],
            ['title' => 'Relaxation Music', 'description' => 'Feel more relaxed', 'type' => 'relaxation', 'content' => 'Use calming music as part of a quiet rest period.'],
            ['title' => 'Positive Affirmations', 'description' => 'Boost your mood', 'type' => 'affirmations', 'content' => 'Choose a realistic, kind statement such as: I can take one small step at a time.'],
            ['title' => 'Healthy Habits', 'description' => 'Build a better you', 'type' => 'habits', 'content' => 'Regular movement, balanced meals, hydration and social connection can support wellbeing.'],
            ['title' => 'Sleep Better', 'description' => 'Rest and recharge', 'type' => 'sleep', 'content' => 'Keep a consistent sleep schedule and create a relaxing wind-down routine.'],
        ];
        foreach ($care as $item) {
            SelfCareResource::firstOrCreate(['title' => $item['title']], $item);
        }
    }
}
