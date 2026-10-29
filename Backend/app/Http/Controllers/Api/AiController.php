<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Ai\Contracts\ChatResponder;
use Illuminate\Http\Request;

class AiController extends Controller
{
    /** Upper bound on returned history so a long conversation can't bloat the response. */
    private const HISTORY_LIMIT = 100;

    public function __construct(private readonly ChatResponder $responder) {}

    public function history(Request $request)
    {
        $messages = $request->user()->chatMessages()
            ->latest('id')
            ->limit(self::HISTORY_LIMIT)
            ->get()
            ->reverse()
            ->values();

        return response()->json(['data' => $messages]);
    }

    public function chat(Request $request)
    {
        $data = $request->validate(['message' => 'required|string|max:3000']);

        $response = $this->responder->respond($data['message'], $request->user());

        $request->user()->chatMessages()->createMany([
            ['sender' => 'user', 'message' => $data['message']],
            ['sender' => 'ai', 'message' => $response->reply],
        ]);

        return response()->json([
            'success' => true,
            'data' => $response->toArray(),
            'disclaimer' => $response->disclaimer,
        ]);
    }

    public function clearHistory(Request $request)
    {
        $request->user()->chatMessages()->delete();

        return response()->json(['success' => true]);
    }
}
