<?php

declare(strict_types=1);

namespace Iamthenewking\GhlQuote;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Minimal GoHighLevel (LeadConnector) API client built on Laravel's HTTP
 * client. Read helpers swallow errors and return empty results (so a status
 * page still renders); write helpers throw so callers can decide how to react.
 */
class GoHighLevelClient
{
    public function __construct(
        protected string $token,
        protected string $locationId,
        protected string $apiBase = 'https://services.leadconnectorhq.com',
    ) {
    }

    /** A pre-configured request for the given API version header. */
    protected function request(string $version): PendingRequest
    {
        return Http::baseUrl($this->apiBase)
            ->withToken($this->token)
            ->withHeaders(['Version' => $version])
            ->acceptJson()
            ->timeout(30);
    }

    /**
     * Lists calendars for the configured location. Returns [] on failure.
     *
     * @return array<int,array<string,mixed>>
     */
    public function getCalendars(): array
    {
        try {
            $res = $this->request('2021-07-28')
                ->get('/calendars/', ['locationId' => $this->locationId]);

            return $res->successful() ? ($res->json('calendars') ?? []) : [];
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Lists available appointment slots for a calendar between two epoch-millis
     * timestamps, normalized into a sorted array of days. Returns [] on failure.
     *
     * @return array<int,array{date:string,slots:array<int,string>}>
     */
    public function getFreeSlots(string $calendarId, int $startMs, int $endMs, string $timezone): array
    {
        try {
            $res = $this->request('2021-07-28')->get("/calendars/{$calendarId}/free-slots", [
                'startDate' => $startMs,
                'endDate'   => $endMs,
                'timezone'  => $timezone,
            ]);
            if (! $res->successful()) {
                return [];
            }

            $days = [];
            foreach (($res->json() ?? []) as $date => $value) {
                if (
                    is_string($date)
                    && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
                    && is_array($value)
                    && isset($value['slots'])
                    && is_array($value['slots'])
                    && count($value['slots']) > 0
                ) {
                    $days[] = ['date' => $date, 'slots' => array_values($value['slots'])];
                }
            }
            usort($days, static fn ($a, $b) => strcmp($a['date'], $b['date']));

            return $days;
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Creates or updates a contact (upsert by email/phone match).
     *
     * @param  array<string,mixed>  $input
     * @return array{id:?string,isNew:bool}
     */
    public function upsertContact(array $input): array
    {
        $payload = array_filter($input, static fn ($v) => $v !== null && $v !== '');
        $payload['locationId'] = $this->locationId;

        $res = $this->request('2021-07-28')->post('/contacts/upsert', $payload)->throw();

        return [
            'id'    => $res->json('contact.id'),
            'isNew' => (bool) $res->json('new', false),
        ];
    }

    /** Adds tags to a contact. Adding a tag fires GHL "tag added" triggers. */
    public function addTags(string $contactId, array $tags): void
    {
        $this->request('2021-07-28')
            ->post("/contacts/{$contactId}/tags", ['tags' => $tags])
            ->throw();
    }

    /** Removes tags from a contact. No-ops server-side if not present. */
    public function removeTags(string $contactId, array $tags): void
    {
        $this->request('2021-07-28')
            ->delete("/contacts/{$contactId}/tags", ['tags' => $tags])
            ->throw();
    }

    /** Adds a note to a contact. */
    public function addNote(string $contactId, string $body): void
    {
        $this->request('2021-07-28')
            ->post("/contacts/{$contactId}/notes", ['body' => $body])
            ->throw();
    }

    /**
     * Fetches a contact by id to verify it persisted. Returns null on failure.
     *
     * @return array<string,mixed>|null
     */
    public function getContact(string $contactId): ?array
    {
        try {
            $res = $this->request('2021-07-28')->get("/contacts/{$contactId}");

            return $res->successful() ? ($res->json('contact') ?? null) : null;
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Books an appointment for a contact. endTime is intentionally omitted so
     * GoHighLevel derives the duration from the calendar's own slot length.
     * Throws on failure.
     *
     * @return array{id:string,startTime:?string,status:?string}
     */
    public function bookAppointment(string $calendarId, string $contactId, string $startTime, ?string $title = null): array
    {
        $payload = array_filter([
            'calendarId'        => $calendarId,
            'locationId'        => $this->locationId,
            'contactId'         => $contactId,
            'startTime'         => $startTime,
            'title'             => $title,
            'appointmentStatus' => 'confirmed',
        ], static fn ($v) => $v !== null && $v !== '');
        $payload['ignoreDateRange'] = false;
        $payload['toNotify'] = true;

        $res = $this->request('2021-04-15')
            ->post('/calendars/events/appointments', $payload)
            ->throw();

        $id = $res->json('appointment.id') ?? $res->json('id');
        if ($id === null) {
            throw new RuntimeException('GHL appointment created but no id was returned');
        }

        return [
            'id'        => (string) $id,
            'startTime' => $res->json('appointment.startTime') ?? $res->json('startTime'),
            'status'    => $res->json('appointment.appointmentStatus') ?? $res->json('appointmentStatus'),
        ];
    }
}
