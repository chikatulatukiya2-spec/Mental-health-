<?php

namespace App\Services\Ai;

/**
 * Detects crisis language in a chat message.
 *
 * Matching is done on normalised text (lowercased, hyphens collapsed to
 * spaces, common leetspeak characters folded back) with word-boundary
 * patterns, so forms like "I am suicidal", "self-harm", "killing myself",
 * "kms" and "su1c1de" are all caught while words such as "download" do not
 * produce false positives.
 *
 * This is intentionally over-eager: when in doubt, a message is treated as a
 * crisis. Missing a genuine disclosure is far worse than an occasional
 * unnecessary safety message.
 */
class CrisisDetector
{
    /**
     * Crisis categories and the word-boundary patterns that trigger them.
     * Patterns run against normalised text (see normalize()).
     *
     * @var array<string, list<string>>
     */
    private array $patterns = [
        'suicide_ideation' => [
            '/\bsuicid\w*\b/',                        // suicide, suicidal, suicidally
            '/\bkill(?:ing)?\s+(?:myself|yourself|himself|herself|themselves|ourselves)\b/',
            '/\bend(?:ing)?\s+(?:myself|yourself)\b/',
            '/\bend(?:ing)?\s+(?:my|your|his|her|our)\s+life\b/',
            '/\bend\s+it\s+all\b/',
            '/\b(?:want|wanting|wanna)\s+to\s+(?:die|be\s+dead)\b/',
            '/\b(?:wish|hoping)\s+(?:i|we)\s+(?:were|was)\s+dead\b/',
            '/\b(?:no|not|nothing)\s+(?:reason|reasons|point|will)\s+to\s+live\b/',
            '/\bnothing\s+to\s+live\s+for\b/',
            '/\bd(?:o)?n[\'’]?t\s+want\s+to\s+live\b/',
            '/\bbetter\s+off\s+(?:dead|without\s+me)\b/',
            '/\btake\s+(?:my|your|his|her|our)\s+own\s+life\b/',
            '/\bgive\s+up\s+on\s+(?:life|living)\b/',
            '/\bwant\s+to\s+disappear\b/',
            '/\bkms\b/',                             // slang: "kill myself"
        ],
        'self_harm' => [
            '/\bself\s+harm\w*\b/',                  // "self harm", "self-harm" (hyphen normalised)
            '/\bself\s+destruct\w*\b/',
            '/\bhurt(?:ing)?\s+(?:myself|yourself|himself|herself|themselves)\b/',
            '/\bharm(?:ing)?\s+(?:myself|yourself|himself|herself|themselves)\b/',
            '/\b(?:cut|cutting)\s+(?:my|your)self\b/',
            '/\binjur(?:e|ing)\s+(?:my|your)self\b/',
            '/\bmutilat\w*\b/',
        ],
    ];

    /**
     * Fold common obfuscation so patterns still match: hyphens become
     * spaces ("self-harm" → "self harm") and leetspeak digits become
     * letters ("su1c1de" → "suicide").
     */
    private function normalize(string $text): string
    {
        $text = mb_strtolower($text);
        $text = strtr($text, [
            '-' => ' ', '–' => ' ', '_' => ' ',
            '0' => 'o', '1' => 'i', '3' => 'e',
            '4' => 'a', '5' => 's', '7' => 't',
            '@' => 'a', '$' => 's',
        ]);

        return trim(preg_replace('/\s+/', ' ', $text));
    }

    /**
     * @return list<string> Crisis categories detected in the message (empty = no crisis language).
     */
    public function detect(string $message): array
    {
        $text = $this->normalize($message);
        $matches = [];

        foreach ($this->patterns as $category => $regexes) {
            foreach ($regexes as $regex) {
                if (preg_match($regex, $text)) {
                    $matches[] = $category;
                    break;
                }
            }
        }

        return $matches;
    }

    public function isCrisis(string $message): bool
    {
        return $this->detect($message) !== [];
    }
}
