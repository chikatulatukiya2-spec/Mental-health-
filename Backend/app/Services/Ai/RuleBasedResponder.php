<?php

namespace App\Services\Ai;

use App\Models\SupportResource;
use App\Models\User;
use App\Services\Ai\Contracts\ChatResponder;

/**
 * Default rule-based responder: deterministic, offline, and fully testable.
 *
 * Reply priority: crisis > detected topics (multi-topic, primary first) >
 * topic carried over from earlier in the conversation > generic fallback.
 *
 * Anti-repetition rules — the reason earlier versions felt stiff:
 *  - A reply variant already present in the conversation is never reused;
 *    the next unused variant is picked instead.
 *  - Once a topic's variants are exhausted, "deepening" follow-ups take
 *    over — they acknowledge the topic came up before and ask a question
 *    instead of restating advice.
 *  - Short follow-ups ("yes exactly") get a brief bridge that names the
 *    topic rather than replaying the full reply.
 *  - When the user's message contains a phrase like "about my exams", the
 *    reply reflects their own words back.
 *
 * Crisis replies dynamically embed the app's active emergency support
 * resources, so updating a phone number in the database (or seeder) is
 * enough — no code change required.
 */
class RuleBasedResponder implements ChatResponder
{
    private const FALLBACKS = [
        "Thank you for sharing that with me. I'm here to listen and provide general wellbeing information. Would you like to tell me more about what's been on your mind?",
        "I'm here, and I'm listening. If you'd like, tell me a bit more about what's going on — even a single word about your day is a good start.",
        "Thank you for trusting me with that. Would it help to talk about what's behind it, or would you prefer a small practical suggestion for right now?",
        "That's okay — we don't need the perfect words. What's one thing from today that stuck with you?",
    ];

    private const CRISIS_REPLY = "I'm really sorry you're dealing with this, and I'm glad you reached out. What you're feeling right now is temporary, even if it doesn't feel that way. I can't provide emergency care, but please move to a safe place, stay with someone you trust, and contact one of the services below right now.";

    /**
     * Used when a topic's reply variants and deepening follow-ups have all
     * already appeared in the conversation.
     */
    private const EXHAUSTED = [
        "We've covered a few angles on this already. Is there a particular part that still feels heaviest right now?",
        'I want to keep being useful here rather than repeat myself. What would help most right now — just being listened to, or a practical suggestion?',
    ];

    private const GREETING_REPLIES = [
        "Hello — I'm glad you're here. How are you feeling right now?",
        "Hi there. Thanks for checking in — what's on your mind today?",
        "Hey. I'm here whenever you're ready to talk — how has your day been?",
    ];

    private const GRATITUDE_REPLIES = [
        "You're welcome — I'm glad it helped. How are you feeling right now?",
        "I'm here anytime. Would you like to keep going, or is there something else on your mind?",
    ];

    private const BRIDGES = [
        'Earlier you mentioned %s. Do you want to go deeper on that, or would a practical tip help right now?',
        "It sounds like we're still talking about %s. What feels hardest about it at this moment?",
    ];

    private const OPENER_TEMPLATES = [
        'Thank you for telling me more about %s. ',
        "Okay — let's talk about %s. ",
    ];

    /** Words stripped when reflecting the user's own phrase back to them. */
    private const STOPWORDS = [
        'i', 'me', 'my', 'myself', 'we', 'our', 'us', 'you', 'your', 'it', 'its',
        'the', 'a', 'an', 'so', 'very', 'really', 'just', 'all', 'everything',
        'anything', 'something', 'what', 'how', 'why', 'that', 'this', 'am',
        'is', 'are', 'was', 'were', 'been', 'be', 'to', 'of', 'at', 'in', 'on',
        'and', 'or', 'not', 'no', 'cant', "can't", 'dont', "don't", 'do',
        'does', 'did', 'feel', 'feeling', 'feels', 'here', 'there', 'now',
        'today', 'lately', 'again', 'still', 'with',
    ];

    /** How many past assistant replies are consulted for the no-repeat rule. */
    private const RECENT_REPLIES_WINDOW = 8;

    private const CONTEXT_WINDOW = 10;

    private const SHORT_MESSAGE_WORDS = 5;

    private const MAX_PHRASE_WORDS = 4;

    public function __construct(
        private readonly CrisisDetector $crisis,
        private readonly TopicDetector $topics,
    ) {}

    public function respond(string $message, User $user): ChatResponse
    {
        $recent = $this->recentReplies($user);

        $crisisCategories = $this->crisis->detect($message);

        if ($crisisCategories !== []) {
            return new ChatResponse(
                reply: self::CRISIS_REPLY,
                emergency: true,
                topics: $crisisCategories,
                resources: $this->emergencyResources(),
            );
        }

        $detected = $this->topics->detect($message);

        if ($detected === []) {
            if ($greeting = $this->greetingReply($message, $recent)) {
                return new ChatResponse(reply: $greeting, emergency: false);
            }

            if ($thanks = $this->gratitudeReply($message, $recent)) {
                return new ChatResponse(reply: $thanks, emergency: false);
            }
        }

        $fromContext = false;

        // Very short replies ("yes", "me too") often refer back to what was
        // just said — if the message itself has no topic, look at what the
        // user said a moment ago before falling back to a generic reply.
        if ($detected === [] && $this->isShort($message)) {
            $detected = $this->recentTopics($user);
            $fromContext = $detected !== [];
        }

        if ($detected === []) {
            return new ChatResponse(
                reply: $this->pickUnused(self::FALLBACKS, $recent) ?? self::FALLBACKS[0],
                emergency: false,
            );
        }

        if ($fromContext) {
            return new ChatResponse(
                reply: $this->bridge($detected[0], $recent),
                emergency: false,
                topics: $detected,
                fromContext: true,
            );
        }

        return new ChatResponse(
            reply: $this->composeReply($message, $detected, $recent),
            emergency: false,
            topics: $detected,
        );
    }

    /**
     * The first detected topic (priority order) gets a reply variant that has
     * not already been used in this conversation; up to two more contribute a
     * one-liner each. A short opener may reflect the user's own words.
     */
    private function composeReply(string $message, array $detected, array &$recent): string
    {
        $slug = $detected[0];

        $reply = $this->pickUnused($this->topics->repliesFor($slug), $recent)
            ?? $this->pickUnused($this->topics->deepeningFor($slug), $recent)
            ?? self::EXHAUSTED[0];

        $used = $this->normalize($reply);
        $recent[] = $used;

        $phrase = $this->personalizationPhrase($message);
        if ($phrase !== null) {
            $opener = sprintf($this->pickUnused(self::OPENER_TEMPLATES, []) ?? self::OPENER_TEMPLATES[0], $phrase);
            $reply = $opener.$reply;
        }

        foreach (array_slice($detected, 1, 2) as $secondary) {
            $followUp = $this->pickUnused($this->topics->followUpsFor($secondary), $recent);
            if ($followUp !== null) {
                $reply .= ' '.$followUp;
                $recent[] = $this->normalize($followUp);
            }
        }

        return $reply;
    }

    /**
     * Short, non-repeating reply used when a topic carries over from earlier
     * in the conversation — asks rather than restating advice.
     */
    private function bridge(string $slug, array $recent): string
    {
        $template = $this->pickUnused(self::BRIDGES, $recent) ?? self::BRIDGES[0];

        return sprintf($template, $this->topics->labelFor($slug));
    }

    /**
     * First template that has not already appeared in the conversation.
     *
     * Candidates are matched with substring containment because stored
     * replies may carry a personalized opener or a secondary-topic
     * follow-up around the core text.
     *
     * @param  list<string>  $candidates
     * @param  list<string>  $recent  Normalized texts already used.
     */
    private function pickUnused(array $candidates, array $recent): ?string
    {
        foreach ($candidates as $candidate) {
            $needle = $this->normalize($candidate);

            foreach ($recent as $prior) {
                if ($prior === $needle || str_contains($prior, $needle)) {
                    continue 2;
                }
            }

            return $candidate;
        }

        return null;
    }

    private function normalize(string $text): string
    {
        return mb_strtolower(trim($text));
    }

    /**
     * Pull the phrase the user attached to a preposition — "anxious about my
     * exams" → "exams" — so replies can reflect their own words.
     */
    private function personalizationPhrase(string $message): ?string
    {
        $text = trim(preg_replace('/\s+/', ' ', mb_strtolower($message)));

        if (! preg_match('/\b(?:because\s+of|due\s+to|about|over|since|from|with)\b\s*(.+)$/u', $text, $m)) {
            return null;
        }

        $phrase = trim(preg_replace('/[^a-z0-9\s\'-]/i', '', $m[1]));
        $words = array_filter(
            array_slice(explode(' ', $phrase), 0, self::MAX_PHRASE_WORDS),
            fn (string $word) => $word !== '' && ! in_array($word, self::STOPWORDS, true),
        );

        $phrase = trim(implode(' ', $words));

        return $phrase === '' ? null : $phrase;
    }

    /**
     * Carry the conversation's active topic forward: the topics of the most
     * recent user message that actually contains one. Using only the last
     * substantive message (rather than every recent message combined) keeps
     * a short "thanks" or "yes" attached to what was just being discussed.
     *
     * @return list<string>
     */
    private function recentTopics(User $user): array
    {
        $messages = $user->chatMessages()
            ->where('sender', 'user')
            ->latest('id')
            ->limit(self::CONTEXT_WINDOW)
            ->pluck('message');

        foreach ($messages as $message) {
            $topics = $this->topics->detect($message);

            if ($topics !== []) {
                return $topics;
            }
        }

        return [];
    }

    /**
     * The most recent assistant replies, normalized, for the no-repeat rule.
     *
     * @return list<string>
     */
    private function recentReplies(User $user): array
    {
        return $user->chatMessages()
            ->where('sender', 'ai')
            ->latest('id')
            ->limit(self::RECENT_REPLIES_WINDOW)
            ->pluck('message')
            ->map(fn (string $message) => $this->normalize($message))
            ->all();
    }

    private function isShort(string $message): bool
    {
        return str_word_count($message) <= self::SHORT_MESSAGE_WORDS;
    }

    /**
     * Simple greetings ("hi", "good morning") should get a friendly hello
     * back — not a bridge about a topic from earlier in the conversation.
     */
    private function greetingReply(string $message, array $recent): ?string
    {
        if (str_word_count($message) > 3) {
            return null;
        }

        if (! preg_match('/^\s*(?:hi+|hello+|hey+|howdy|yo|good\s+(?:morning|afternoon|evening))\b/iu', $message)) {
            return null;
        }

        return $this->pickUnused(self::GREETING_REPLIES, $recent) ?? self::GREETING_REPLIES[0];
    }

    /**
     * Acknowledge thanks rather than dragging the conversation back to a
     * topic that was already closed.
     */
    private function gratitudeReply(string $message, array $recent): ?string
    {
        if (str_word_count($message) > 5) {
            return null;
        }

        if (! preg_match('/^\s*(?:thanks?|thank\s+you|thankyou|ty|cheers)\b/iu', $message)) {
            return null;
        }

        return $this->pickUnused(self::GRATITUDE_REPLIES, $recent) ?? self::GRATITUDE_REPLIES[0];
    }

    /**
     * Active emergency resources from the database, ready to render as
     * phone links. Falls back to nothing if none are seeded — the reply
     * itself still stands.
     *
     * @return list<array{title: string, description: ?string, phone: ?string, url: ?string}>
     */
    private function emergencyResources(): array
    {
        return SupportResource::query()
            ->where('is_emergency', true)
            ->where('active', true)
            ->orderByDesc('phone')
            ->limit(4)
            ->get()
            ->map(fn (SupportResource $resource) => [
                'title' => $resource->title,
                'description' => $resource->description,
                'phone' => $resource->phone,
                'url' => $resource->url,
            ])
            ->all();
    }
}
