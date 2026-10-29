<?php

namespace App\Services\Ai\Contracts;

use App\Models\User;
use App\Services\Ai\ChatResponse;

/**
 * Produces a reply for a single chat message.
 *
 * The default implementation is App\Services\Ai\RuleBasedResponder (pure PHP,
 * no external dependencies). To swap in an LLM later, bind a different
 * implementation of this interface in App\Providers\AppServiceProvider —
 * the controller, routes, and frontend response shape stay unchanged.
 */
interface ChatResponder
{
    public function respond(string $message, User $user): ChatResponse;
}
