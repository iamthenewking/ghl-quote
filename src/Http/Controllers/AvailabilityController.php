<?php

declare(strict_types=1);

namespace Iamthenewking\GhlQuote\Http\Controllers;

use Iamthenewking\GhlQuote\GoHighLevelClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvailabilityController
{
    private const MAX_DAYS = 60;

    public function __construct(protected GoHighLevelClient $ghl)
    {
    }

    /**
     * Returns each calendar for the location along with its available slots for
     * the coming window. Used by the quote form's appointment picker.
     */
    public function index(Request $request): JsonResponse
    {
        $timezone = (string) ($request->query('timezone') ?: config('gohighlevel.timezone'));
        $daysParam = (int) $request->query('days', 0);
        $days = $daysParam > 0
            ? min($daysParam, self::MAX_DAYS)
            : (int) config('gohighlevel.availability_days', 14);

        $calendars = $this->ghl->getCalendars();
        $startMs = (int) round(microtime(true) * 1000);
        $endMs = $startMs + $days * 24 * 60 * 60 * 1000;

        $out = [];
        foreach ($calendars as $cal) {
            $id = (string) ($cal['id'] ?? '');
            if ($id === '') {
                continue;
            }
            $out[] = [
                'id'   => $id,
                'name' => $cal['name'] ?? 'Calendar',
                'days' => $this->ghl->getFreeSlots($id, $startMs, $endMs, $timezone),
            ];
        }

        return response()->json(['timezone' => $timezone, 'calendars' => $out]);
    }
}
