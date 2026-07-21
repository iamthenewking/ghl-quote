<?php

declare(strict_types=1);

namespace Iamthenewking\GhlQuote\Facades;

use Iamthenewking\GhlQuote\GoHighLevelClient;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array getCalendars()
 * @method static array getFreeSlots(string $calendarId, int $startMs, int $endMs, string $timezone)
 * @method static array upsertContact(array $input)
 * @method static void addTags(string $contactId, array $tags)
 * @method static void removeTags(string $contactId, array $tags)
 * @method static void addNote(string $contactId, string $body)
 * @method static ?array getContact(string $contactId)
 * @method static array bookAppointment(string $calendarId, string $contactId, string $startTime, ?string $title = null)
 *
 * @see \Iamthenewking\GhlQuote\GoHighLevelClient
 */
class GoHighLevel extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return GoHighLevelClient::class;
    }
}
