<?php

declare(strict_types=1);

namespace Iamthenewking\GhlQuote\Http\Controllers;

use Iamthenewking\GhlQuote\Services\QuoteService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuoteController
{
    public function __construct(protected QuoteService $quotes)
    {
    }

    /** Renders the bundled quote form. Publish the view to customize it. */
    public function show(): View
    {
        return view('gohighlevel::quote', [
            'notificationEmail' => config('gohighlevel.notification_email'),
        ]);
    }

    /** Accepts a JSON (or form) submission and creates the lead in GoHighLevel. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->all();

        if (empty($data['fullName']) || trim((string) $data['fullName']) === '') {
            return response()->json(['error' => 'full name is required'], 400);
        }
        if (empty($data['email']) && empty($data['phone'])) {
            return response()->json(['error' => 'an email or phone is required'], 400);
        }

        $result = $this->quotes->process($data);

        return response()->json($result, $result['ok'] ? 200 : 502);
    }
}
