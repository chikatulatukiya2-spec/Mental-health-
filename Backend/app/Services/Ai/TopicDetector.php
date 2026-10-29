<?php

namespace App\Services\Ai;

/**
 * Detects the wellbeing topics present in a chat message.
 *
 * Every matching topic is returned (not just the first), and each topic
 * carries several reply variants, "deepening" follow-ups used once the
 * variants are exhausted, and short one-liners for secondary topics. The
 * responder picks variants that have not already appeared in the current
 * conversation, so the assistant never repeats itself verbatim.
 */
class TopicDetector
{
    /**
     * Topics in priority order (highest first).
     *
     * - patterns:  word-boundary regexes that trigger the topic
     * - label:     human-readable name used in conversation bridges
     * - replies:   main reply variants (rotated, never repeated in-conversation)
     * - deepening: replies used after the main variants run out; they
     *              acknowledge the topic has come up before and ask a
     *              question to move the conversation forward
     * - follow_ups: one-liners appended when this topic is secondary to another
     *
     * @var array<string, array{
     *     patterns: list<string>,
     *     label: string,
     *     replies: list<string>,
     *     deepening: list<string>,
     *     follow_ups: list<string>
     * }>
     */
    private array $topics = [
        'anxiety' => [
            'patterns' => [
                '/\banxiet\w*\b/', '/\banxious\b/', '/\bpanic(?:king|ked)?\b/',
                '/\bnervous\b/', '/\bon\s+edge\b/', '/\bracing\s+thoughts\b/',
                '/\bworry(?:ing)?\b/', '/\bworried\b/',
            ],
            'label' => 'anxiety',
            'replies' => [
                "That sounds difficult, and what you're feeling is valid. Try slowing your breathing for a minute: breathe in gently, pause briefly, and breathe out slowly. If anxiety keeps interfering with daily life, consider speaking with a qualified professional.",
                "Anxiety can make your body feel like it's in danger even when you're safe. One thing that can help right now: name five things you can see, four you can touch, three you can hear — it pulls your mind back to the present. If anxiety keeps interrupting your days, a professional can teach you more tools like this.",
                "I hear you. Fighting anxiety often makes it louder — try acknowledging it instead: 'I feel anxious right now, and that's okay.' Then let the wave pass rather than riding it. If this feeling is frequent or intense, please consider talking to a qualified professional.",
            ],
            'deepening' => [
                "We've talked about anxiety before, and it clearly keeps coming back. What does a typical anxious moment look like for you — is it a situation, a time of day, or something else?",
                'Since we last spoke about this, has the anxiety changed at all — lighter, heavier, or the same? Even small shifts are worth noticing.',
            ],
            'follow_ups' => [
                'I also want to acknowledge the anxiety you mentioned — grounding techniques like naming five things you can see can help in the moment.',
                'I also noticed the anxiety in your message — a slow exhale, twice as long as the inhale, can calm your body within a minute.',
            ],
        ],
        'stress' => [
            'patterns' => [
                '/\bstress(?:ed|es|ing)?\b/', '/\boverwhelm(?:ed|ing)?\b/',
                '/\bburn(?:t|ed)\s*out\b/', '/\bburnout\b/', '/\btoo\s+much\b/',
                '/\bpressure\b/', '/\bcan\s*?[\'’]?t\s+cope\b/',
            ],
            'label' => 'stress',
            'replies' => [
                "It sounds like you're carrying a lot right now. Try picking one small, manageable thing you can do next rather than everything at once — and it's okay to ask someone to share the load. If the pressure keeps building, talking to a professional can help you find a way through it.",
                "Carrying constant pressure is exhausting — you don't have to earn your rest. Try this: write down the three things on your mind, circle the one that actually needs you today, and let the rest wait until tomorrow. If the load stays this heavy, sharing it with someone — a friend, family, or counsellor — can make it lighter.",
                "That sounds like a lot to hold alone. When everything feels urgent, it helps to ask: what actually needs to happen today, and what can wait? Stepping back for a break isn't failing — it's how you keep going. If stress is affecting your sleep or health, talking to a professional is a strong step, not a weak one.",
            ],
            'deepening' => [
                'The pressure you described came up before too. Is there one part of it that feels most in your control right now?',
                "We've touched on your stress load already — has anything shifted since, even slightly?",
            ],
            'follow_ups' => [
                "I also noticed you're under real pressure — even five quiet minutes away from it all can help your mind reset.",
                "I also want to acknowledge the weight you're carrying — please don't treat rest as something you have to earn.",
            ],
        ],
        'low_mood' => [
            'patterns' => [
                '/\bsad(?:ness)?\b/', '/\bdepress(?:ed|ion|ing)?\b/', '/\bdown\b/',
                '/\blow\s+(?:mood|spirit)\b/', '/\bhopeless\b/', '/\bnumb\b/',
                '/\bempty\b/', '/\bunhappy\b/', '/\bmiserable\b/', '/\bglum\b/',
            ],
            'label' => 'low mood',
            'replies' => [
                "Thank you for sharing that. You don't have to handle everything alone. Consider talking to someone you trust and taking one small, manageable step today — even a short walk or a glass of water counts. If low mood persists or worsens, a qualified professional can help.",
                "Low days can make everything feel further away — including the people who care about you. Be extra gentle with yourself today: water, food, a little daylight, and one kind word to yourself. If the heaviness stays for weeks or grows, please reach out to a professional — that's what they're there for.",
                'Thank you for trusting me with that. When mood is low, small things feel huge and huge things feel impossible — so keep today small. One tiny act of care counts. And if this feeling has been around a while, talking to a doctor or counsellor can genuinely help — you deserve support.',
            ],
            'deepening' => [
                'This came up for us before, and that matters. What do you think sits underneath the low feeling — sadness, tiredness, or something else?',
                'Since we last talked about this, has the low mood moved at all? Even a small change is worth noting.',
            ],
            'follow_ups' => [
                'I also heard the low mood in your message — be gentle with yourself today, and consider reaching out to someone you trust.',
                'I also noticed how heavy things feel right now — one small kind thing for yourself today is enough.',
            ],
        ],
        'sleep' => [
            'patterns' => [
                '/\binsomnia\b/', '/\bcan\s*?[\'’]?t\s+sleep\b/', '/\b(?:not|unable)\s+to\s+sleep\b/',
                '/\bsleep(?:ing|less)?\b/', '/\btired\b/', '/\bexhaust(?:ed|ing)\b/',
                '/\bnightmare(?:s)?\b/',
            ],
            'label' => 'sleep',
            'replies' => [
                "Rest matters, and it's hard to face the day without it. A consistent sleep and wake time, reducing screen time and stimulating activity before bed, and a calm bedtime routine can help. If sleep problems continue, consider discussing them with a healthcare professional.",
                "Not sleeping drains everything else too. Try keeping your wake-up time steady even after a bad night, dimming screens an hour before bed, and moving worry thoughts to paper so your mind doesn't have to hold them. If sleeplessness lasts more than a few weeks, a healthcare professional can help properly.",
                "Tiredness makes every problem feel bigger than it is — please don't judge your days by one bad night. A simple routine helps: same wake-up time, no late caffeine, and a calm wind-down. If nights stay sleepless, consider speaking with a healthcare professional — chronic sleep loss is treatable.",
            ],
            'deepening' => [
                "Sleep has come up between us before — that usually means it's affecting your days. Is it falling asleep, staying asleep, or waking up tired?",
                'Since we last spoke about rest, have your nights changed at all?',
            ],
            'follow_ups' => [
                'I also noticed you mentioned sleep — a short wind-down routine before bed may help restore your rest.',
                "I also want to acknowledge the sleep you're missing — even one consistent wake-up time this week can start shifting things.",
            ],
        ],
        'loneliness' => [
            'patterns' => [
                '/\blonely\b/', '/\bloneliness\b/', '/\bisolat(?:ed|ion)\b/',
                '/\bnobody\s+(?:cares|loves|is\s+there)\b/', '/\bno\s+friends\b/',
                '/\bleft\s+out\b/', '/\bso\s+alone\b/',
            ],
            'label' => 'loneliness',
            'replies' => [
                'Feeling lonely can be really painful, and it takes courage to say it out loud. Even a small connection counts — a message to someone you know, a group or class in your community, or one of the support services in this app. You matter, and you deserve support.',
                "Loneliness is a heavy weight, and it can quietly tell you things that aren't true — like that you're a burden. You're not. Connection can start small: a message to one person, a shared space like a class or group, or one of the support lines in this app. Would any of those feel possible today?",
                "I'm glad you told me. Feeling alone in a crowd is one of the most painful human experiences — and it lies about your worth. One small step toward another person counts, even a simple hello. The support services in this app are real people, ready to listen whenever you need them.",
            ],
            'deepening' => [
                'Feeling lonely came up for us before — I want to keep being here for it. What does the loneliest part of your day look like?',
                'Since we last spoke about this, has there been any moment, however small, of real connection?',
            ],
            'follow_ups' => [
                'I also want to acknowledge the loneliness you shared — even one small step toward connection can lighten the weight.',
                'I also heard the loneliness in what you said — you took a real step by naming it here.',
            ],
        ],
        'grief' => [
            'patterns' => [
                '/\bgrief\b/', '/\bgrieving\b/', '/\bbereaved\b/', '/\bbereavement\b/',
                '/\blost\s+(?:my|a|the)\s+(?:mum|mom|dad|mother|father|parent|wife|husband|partner|friend|child|sister|brother)\b/',
                '/\bpassed\s+away\b/', '/\bdied\b/', '/\bdeath\s+of\b/',
            ],
            'label' => 'grief',
            'replies' => [
                "I'm sorry for what you're going through. Grief has no right or wrong timeline — be patient and kind with yourself. Leaning on people who knew the person you lost, or a counsellor, can help you carry it. There is no shame in asking for support.",
                "Grief is love with nowhere to go — it comes in waves, and the waves don't keep a schedule. There's no right way to do this; some days will be heavier than others, and that's normal. If the grief feels impossible to carry alone, a counsellor can sit with you in it without asking you to hurry.",
                "Thank you for telling me. Grief can reshape ordinary days — a song, a room, an unanswered phone — and that's a sign of real love, not weakness. Please lean on the people who knew them, and on us here too. If the pain stays unbearable, reaching out to a professional is a valid next step.",
            ],
            'deepening' => [
                "Grief came up between us before, and I don't want it to feel like something you have to move past. How are you carrying it today?",
                'Since we last spoke about your loss, has anything felt a little lighter — or a little heavier?',
            ],
            'follow_ups' => [
                "I also want to acknowledge the grief you mentioned — please be gentle with yourself; there's no timeline you have to follow.",
                "I also heard the grief in your message — it's okay to let the tears come when they need to.",
            ],
        ],
        'anger' => [
            'patterns' => [
                '/\bangry\b/', '/\banger\b/', '/\bfurious\b/', '/\brage\b/',
                '/\bmad\b/', '/\birritat(?:ed|ing)\b/', '/\bannoy(?:ed|ing)\b/',
            ],
            'label' => 'anger',
            'replies' => [
                "It's okay to feel angry — emotions like anger often carry an important message underneath. Try stepping away for a few slow breaths before reacting, and later, name what triggered it. If anger feels hard to control or is affecting your relationships, a professional can help.",
                "Anger is rarely just anger — it's often hurt, fear, or unfairness wearing a louder coat. Before you act on it, give yourself ninety seconds: breathe, unclench your hands, and ask what the anger is protecting. Then choose your next move with a clearer head.",
                "Thank you for being honest about this. Feeling angry doesn't make you a bad person — what you do with it matters. Try physical release first: a walk, cold water on your hands, anything that breaks the heat. If anger keeps boiling over at the people around you, a professional can help you find the source.",
            ],
            'deepening' => [
                "Anger has come up for us before — that tells me it's worth understanding rather than judging. What tends to set it off?",
                'Since we last spoke about this, has the anger come up again — and what happened just before it did?',
            ],
            'follow_ups' => [
                "I also noticed the anger in what you said — it's worth pausing to ask what underneath it needs your attention.",
                'I also want to acknowledge how charged this feels — ninety seconds of space before reacting can change everything.',
            ],
        ],
        'relationships' => [
            'patterns' => [
                '/\bboyfriend\b/', '/\bgirlfriend\b/', '/\bpartner\b/', '/\bhusband\b/',
                '/\bwife\b/', '/\bbreak\s*up\b/', '/\bbreakup\b/', '/\bdivorce\b/',
                '/\bargument\b/', '/\bfight(?:ing)?\s+with\b/', '/\brelationship\b/',
            ],
            'label' => 'your relationship',
            'replies' => [
                "Relationships can bring us a lot of joy and a lot of pain, sometimes at the same time. Try to focus on what you can express honestly and what you can't control in the other person. If things feel stuck or unsafe, talking with a counsellor can help you find clarity.",
                "Close relationships can feel like the best and hardest parts of life at once. When things are tense, it helps to name what you actually need rather than what you wish they'd notice — 'I need...' lands better than 'you always...'. And if you ever feel unsafe, that's a line no relationship should cross — support is available.",
                "Thank you for sharing this. It's easy to lose yourself in someone else's moods — check in with your own for a moment: what do you need right now, separate from them? Healthy love makes room for both people's needs. A counsellor can help you untangle things if they feel knotted.",
            ],
            'deepening' => [
                'This came up between us before — how have things been with them since we last spoke?',
                'Since our last conversation about this, has anything shifted in the situation — even a small thing?',
            ],
            'follow_ups' => [
                'I also want to acknowledge what you shared about your relationship — your feelings about it are valid.',
                'I also heard how much this relationship is taking from you right now — your needs matter in it too.',
            ],
        ],
        'work_school' => [
            'patterns' => [
                '/\bexams?\b/', '/\btests?\b/', '/\bgrades?\b/', '/\bdeadlines?\b/',
                '/\bmy\s+boss\b/', '/\bworkload\b/', '/\buniversity\b/', '/\bcollege\b/',
                '/\bfailed\b/', '/\bfailing\b/', '/\bschool\b/',
            ],
            'label' => 'school or work',
            'replies' => [
                "Work and study pressure can feel all-consuming. Break the next task into the smallest possible step, give yourself a real break, and remember that one result doesn't define you. If the pressure is affecting your health or sleep, please consider talking to someone about it.",
                'Deadlines have a way of shrinking everything else in life. Try this: pick the single next task, set a twenty-five minute timer, and stop when it rings — progress beats panic. And one result or one job is never a verdict on who you are.',
                "That pressure is real, and you're not weak for feeling it. Structure helps: write the workload down so it lives outside your head, then rank it by what actually matters. Protect sleep like it's a deadline too. If it's overwhelming your days, talking to a counsellor at school or work can help lighten it.",
            ],
            'deepening' => [
                "School or work pressure came up for us before — what's the heaviest part of it right now?",
                'Since we last spoke about this, has the workload settled at all, or has it grown?',
            ],
            'follow_ups' => [
                'I also heard how much pressure school or work is putting on you — protecting a little time to rest will help you cope better.',
                "I also want to acknowledge the workload you're carrying — please schedule a real break, not just a pause between tasks.",
            ],
        ],
    ];

    /**
     * @return list<string> Slugs of every topic detected, in priority order.
     */
    public function detect(string $message): array
    {
        $text = mb_strtolower($message);
        $detected = [];

        foreach ($this->topics as $slug => $topic) {
            foreach ($topic['patterns'] as $pattern) {
                if (preg_match($pattern, $text)) {
                    $detected[] = $slug;
                    break;
                }
            }
        }

        return $detected;
    }

    /** @return list<string> */
    public function repliesFor(string $slug): array
    {
        return $this->topics[$slug]['replies'];
    }

    /** @return list<string> */
    public function deepeningFor(string $slug): array
    {
        return $this->topics[$slug]['deepening'];
    }

    /** @return list<string> */
    public function followUpsFor(string $slug): array
    {
        return $this->topics[$slug]['follow_ups'];
    }

    public function labelFor(string $slug): string
    {
        return $this->topics[$slug]['label'];
    }
}
