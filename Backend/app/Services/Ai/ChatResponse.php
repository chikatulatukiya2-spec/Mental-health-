<?php

namespace App\Services\Ai;

/**
 * Immutable result of generating a chat reply.
 */
class ChatResponse
{
    public const DEFAULT_DISCLAIMER = 'MindEase AI provides general support and information, not a diagnosis or emergency service.';

    /**
     * @param  string  $reply  The assistant reply text.
     * @param  bool  $emergency  Whether crisis language was detected.
     * @param  string[]  $topics  Slugs of every detected topic (e.g. 'anxiety', 'sleep').
     * @param  array<int, array{title:string, description:?string, phone:?string, url:?string}>  $resources
     *                                                                                                       Emergency contact resources, populated when $emergency is true.
     * @param  bool  $fromContext  Whether topics were carried over from earlier in the conversation.
     */
    public function __construct(
        public readonly string $reply,
        public readonly bool $emergency,
        public readonly array $topics = [],
        public readonly array $resources = [],
        public readonly bool $fromContext = false,
        public readonly string $disclaimer = self::DEFAULT_DISCLAIMER,
    ) {}

    /**
     * Shape returned to the frontend as `data`. `reply` and `emergency` are
     * kept under their original keys for backward compatibility.
     */
    public function toArray(): array
    {
        return [
            'reply' => $this->reply,
            'emergency' => $this->emergency,
            'topics' => $this->topics,
            'resources' => $this->resources,
            'from_context' => $this->fromContext,
        ];
    }
}
