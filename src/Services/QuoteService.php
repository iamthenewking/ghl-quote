<?php

declare(strict_types=1);

namespace Iamthenewking\GhlQuote\Services;

use Iamthenewking\GhlQuote\GoHighLevelClient;
use DateTime;
use RuntimeException;
use Throwable;

/**
 * Processes a quote submission: upsert the contact, re-apply the notification
 * tag, attach a details note, optionally book the chosen appointment, and read
 * the contact back to confirm it persisted. Booking and note attachment are
 * best-effort and never fail the overall lead capture.
 */
class QuoteService
{
    public function __construct(protected GoHighLevelClient $ghl)
    {
    }

    /**
     * @param  array<string,mixed>  $q
     * @return array<string,mixed>
     */
    public function process(array $q): array
    {
        $result = [
            'ok'                    => false,
            'noteAdded'             => false,
            'isNewContact'          => false,
            'notificationTriggered' => false,
            'verifiedInGhl'         => false,
            'appointmentRequested'  => ! empty($q['appointmentCalendarId']) && ! empty($q['appointmentSlot']),
            'appointmentBooked'     => false,
        ];

        try {
            [$first, $last] = $this->splitName((string) ($q['fullName'] ?? ''));

            $up = $this->ghl->upsertContact([
                'firstName' => $first,
                'lastName'  => $last,
                'email'     => $q['email'] ?? null,
                'phone'     => $q['phone'] ?? null,
                'address1'  => $q['propertyAddress'] ?? null,
                'source'    => 'Website quote form (Laravel)',
            ]);

            $contactId = $up['id'];
            if ($contactId === null) {
                throw new RuntimeException('GHL upsert did not return a contact id');
            }

            $result['contactId']    = $contactId;
            $result['isNewContact'] = $up['isNew'];
            $result['ok']           = true;

            $loc = (string) config('gohighlevel.location_id');
            $result['crmUrl'] = "https://app.gohighlevel.com/v2/location/{$loc}/contacts/detail/{$contactId}";

            // Remove then add the tag so the "tag added" trigger re-fires every submission.
            $tag = (string) config('gohighlevel.quote_tag');
            try {
                $this->ghl->removeTags($contactId, [$tag]);
            } catch (Throwable $e) {
                // ignore — tag may not be present yet
            }
            try {
                $this->ghl->addTags($contactId, [$tag]);
                $result['notificationTriggered'] = true;
            } catch (Throwable $e) {
                $result['notificationTriggered'] = false;
            }

            // Attach details as a note; failure shouldn't fail the whole submit.
            try {
                $this->ghl->addNote($contactId, $this->buildNote($q));
                $result['noteAdded'] = true;
            } catch (Throwable $e) {
                $result['noteAdded'] = false;
            }

            // Book the requested appointment, if one was chosen. Best-effort.
            if (! empty($q['appointmentCalendarId']) && ! empty($q['appointmentSlot'])) {
                try {
                    $booked = $this->ghl->bookAppointment(
                        (string) $q['appointmentCalendarId'],
                        $contactId,
                        (string) $q['appointmentSlot'],
                        trim((string) ($q['fullName'] ?? '')) . ' — Website quote appointment'
                    );
                    $result['appointmentBooked'] = true;
                    $result['appointmentId']     = $booked['id'];
                    $result['appointmentTime']   = $booked['startTime'] ?? $q['appointmentSlot'];
                } catch (Throwable $e) {
                    $result['appointmentError'] = $e->getMessage();
                }
            }

            // Read the contact back to confirm persistence.
            $contact = $this->ghl->getContact($contactId);
            if ($contact !== null) {
                $result['verifiedInGhl'] = true;
                $name = $contact['contactName']
                    ?? trim(($contact['firstName'] ?? '') . ' ' . ($contact['lastName'] ?? ''));
                $result['contactName'] = $name !== '' ? $name : null;
                $result['tags']        = $contact['tags'] ?? [];
                $result['dateAdded']   = $contact['dateAdded'] ?? null;
            }
        } catch (Throwable $e) {
            $result['error'] = $e->getMessage();
        }

        return $result;
    }

    /** @return array{0:string,1:?string} [firstName, lastName] */
    protected function splitName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName)) ?: [];
        $first = array_shift($parts) ?? trim($fullName);
        $last = implode(' ', $parts);

        return [$first, $last !== '' ? $last : null];
    }

    /** Formats an ISO slot for the note, e.g. "Wed, Jul 22, 2026, 8:00 AM EDT". */
    protected function formatSlot(string $iso): string
    {
        try {
            return (new DateTime($iso))->format('D, M j, Y, g:i A T');
        } catch (Throwable $e) {
            return $iso;
        }
    }

    /** @param array<string,mixed> $q */
    protected function buildNote(array $q): string
    {
        $lines = [];
        $push = static function (string $label, $val) use (&$lines): void {
            if ($val !== null && trim((string) $val) !== '') {
                $lines[] = "{$label}: {$val}";
            }
        };

        $push('Property address', $q['propertyAddress'] ?? null);
        $push('Property type', $q['propertyType'] ?? null);
        $push('Homeowner', $q['homeowner'] ?? null);
        $push('Windows to replace', $q['windowCount'] ?? null);
        $push('Doors / sliding doors', $q['doors'] ?? null);
        $push('Reason for replacing', $q['reason'] ?? null);
        $push('Scope', $q['scope'] ?? null);
        $push('Desired timeline', $q['timeline'] ?? null);
        $push('All decision-makers available', $q['decisionMakers'] ?? null);
        $push('Heard about us via', $q['referralSource'] ?? null);
        $push('Requested appointment with', $q['appointmentCalendarName'] ?? null);
        $slot = $q['appointmentSlot'] ?? null;
        $push('Requested appointment time', ($slot !== null && $slot !== '') ? $this->formatSlot((string) $slot) : null);

        return "New website quote request\n\n" . implode("\n", $lines);
    }
}
