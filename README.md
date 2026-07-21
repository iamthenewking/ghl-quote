# iamthenewking/ghl-quote

A Laravel package that captures quote-form leads into **GoHighLevel (LeadConnector)**, pulls **live calendar availability**, and **books appointments** — the same feature set as the Next.js and plain-PHP versions in this repo, packaged as an installable Composer dependency.

The package never sends email itself; it tags the contact so a GoHighLevel **Workflow** fires (e.g. notify the sales inbox).

---

## Requirements

- PHP 8.1+
- Laravel 10, 11, or 12

---

## Installation

Once the package is published on [Packagist](https://packagist.org), install it like any other Composer dependency:

```bash
composer require iamthenewking/ghl-quote
```

> The service provider `Iamthenewking\GhlQuote\GoHighLevelServiceProvider` and the
> `GoHighLevel` facade are registered automatically via Laravel package
> auto-discovery — nothing to add to `bootstrap/providers.php` or
> `config/app.php`.

<details>
<summary>Installing before it's on Packagist (local path or VCS)</summary>

**From a local folder** — add a `path` repository to your app's root `composer.json`:

```jsonc
{
    "repositories": [
        { "type": "path", "url": "packages/ghl-quote" }
    ],
    "require": { "iamthenewking/ghl-quote": "*" }
}
```

**Straight from GitHub** before submitting to Packagist:

```jsonc
{
    "repositories": [
        { "type": "vcs", "url": "https://github.com/iamthenewking/ghl-quote" }
    ],
    "require": { "iamthenewking/ghl-quote": "dev-main" }
}
```

Then `composer update iamthenewking/ghl-quote`.
</details>

### Publish the config (and optionally the view)

```bash
php artisan vendor:publish --tag=gohighlevel-config
php artisan vendor:publish --tag=gohighlevel-views   # optional — to customize the form
```

`--tag=gohighlevel-config` writes `config/gohighlevel.php`.
`--tag=gohighlevel-views` writes `resources/views/vendor/gohighlevel/quote.blade.php`.

### Add your credentials to `.env`

```env
GHL_PRIVATE_INTEGRATION_TOKEN=pit-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
GHL_LOCATION_ID=your_location_id

# optional
GHL_TIMEZONE=America/New_York
GHL_QUOTE_TAG="Website Quote Form"
GHL_AVAILABILITY_DAYS=14
NOTIFICATION_EMAIL=support@domain.com
GHL_ROUTE_PREFIX=ghl
GHL_REGISTER_ROUTES=true
```

### Visit the form

Start your app (`php artisan serve`) and open:

```
http://localhost:8000/ghl/quote
```

You should see the quote form with the **Preferred appointment** picker
loading live availability from your GoHighLevel calendars.

---

## Routes

When `GHL_REGISTER_ROUTES=true` (default), these are registered under the
configured prefix (default `ghl`) using the `web` middleware group:

| Method | URI                 | Route name         | Purpose                          |
|--------|---------------------|--------------------|----------------------------------|
| GET    | `/ghl/quote`        | `ghl.quote.show`   | Renders the bundled quote form   |
| POST   | `/ghl/quote`        | `ghl.quote.store`  | Creates the lead + books slot    |
| GET    | `/ghl/availability` | `ghl.availability` | JSON: each calendar's free slots  |

Check they are registered:

```bash
php artisan route:list --name=ghl
```

### Using your own routes instead

Set `GHL_REGISTER_ROUTES=false`, then wire the controllers yourself:

```php
use Iamthenewking\GhlQuote\Http\Controllers\QuoteController;
use Iamthenewking\GhlQuote\Http\Controllers\AvailabilityController;

Route::middleware('web')->prefix('get-a-quote')->group(function () {
    Route::get('/', [QuoteController::class, 'show'])->name('ghl.quote.show');
    Route::post('/', [QuoteController::class, 'store'])->name('ghl.quote.store');
    Route::get('/availability', [AvailabilityController::class, 'index'])->name('ghl.availability');
});
```

> The bundled Blade view references the route **names** (`ghl.quote.store`,
> `ghl.availability`), so keep those names if you reuse the view.

---

## Programmatic use

You don't have to use the bundled form — call the client or service directly.

### The client (facade or DI)

```php
use Iamthenewking\GhlQuote\Facades\GoHighLevel;

$calendars = GoHighLevel::getCalendars();

$startMs = (int) round(microtime(true) * 1000);
$endMs   = $startMs + 14 * 86400 * 1000;
$slots   = GoHighLevel::getFreeSlots($calendarId, $startMs, $endMs, 'America/New_York');

$appt = GoHighLevel::bookAppointment(
    $calendarId,
    $contactId,
    '2026-07-23T08:00:00-04:00',
    'Jane Doe — Website quote appointment'
);
```

Or inject the client where you need it:

```php
use Iamthenewking\GhlQuote\GoHighLevelClient;

public function __construct(private GoHighLevelClient $ghl) {}
```

### The full quote pipeline

`QuoteService::process()` upserts the contact, re-applies the tag (so your
Workflow re-fires), attaches a details note, optionally books the chosen
appointment, and reads the contact back to confirm it persisted:

```php
use Iamthenewking\GhlQuote\Services\QuoteService;

public function store(Request $request, QuoteService $quotes)
{
    $result = $quotes->process($request->all());
    return response()->json($result, $result['ok'] ? 200 : 502);
}
```

**Expected input keys:** `fullName` (required), `email` and/or `phone`
(one required), plus optional `propertyAddress`, `propertyType`, `homeowner`,
`windowCount`, `doors`, `reason`, `scope`, `timeline`, `decisionMakers`,
`referralSource`, and — to book — `appointmentCalendarId` + `appointmentSlot`
(ISO-8601 with offset) and optionally `appointmentCalendarName`.

**Result shape:** `ok`, `contactId`, `isNewContact`, `notificationTriggered`,
`noteAdded`, `verifiedInGhl`, `contactName`, `tags`, `dateAdded`, `crmUrl`,
`appointmentRequested`, `appointmentBooked`, `appointmentId`,
`appointmentTime`, and (`error` / `appointmentError`) on failure.

---

## Configuration reference (`config/gohighlevel.php`)

| Key                  | Env                              | Default                                | Notes |
|----------------------|----------------------------------|----------------------------------------|-------|
| `token`              | `GHL_PRIVATE_INTEGRATION_TOKEN`  | —                                      | Private Integration Token (`pit-…`) |
| `location_id`        | `GHL_LOCATION_ID`                | —                                      | Location the token belongs to |
| `api_base`           | `GHL_API_BASE`                   | `https://services.leadconnectorhq.com` | |
| `timezone`           | `GHL_TIMEZONE`                   | `America/New_York`                     | Availability timezone |
| `quote_tag`          | `GHL_QUOTE_TAG`                  | `Website Quote Form`                   | Re-applied each submit to re-fire the Workflow |
| `availability_days`  | `GHL_AVAILABILITY_DAYS`          | `14`                                   | Days of availability to offer (capped at 60) |
| `notification_email` | `NOTIFICATION_EMAIL`             | `support@domain.com`              | Reference only — shown in the form |
| `register_routes`    | `GHL_REGISTER_ROUTES`            | `true`                                 | Auto-register package routes |
| `route_prefix`       | `GHL_ROUTE_PREFIX`               | `ghl`                                  | URL prefix for the routes |
| `route_middleware`   | —                                | `['web']`                              | Middleware group for the routes |

---

## Customizing the form

After `vendor:publish --tag=gohighlevel-views`, edit
`resources/views/vendor/gohighlevel/quote.blade.php`. Laravel loads your
published copy instead of the packaged one. Keep the route names and the
`appointmentCalendarId` / `appointmentSlot` field names if you keep the
appointment picker.

---

## Publishing to Packagist

The package is Packagist-ready (valid `composer.json` with `name`, `description`,
`license`, `authors`, autoload, and Laravel auto-discovery under `extra.laravel`).
To publish:

1. **Pick a vendor/name you own.** The current name is `iamthenewking/ghl-quote`. The
   `vendor` (`iamthenewking`) must match your Packagist account or a claimed vendor.
   If you use a different one, update `name`, `homepage`, and `support` URLs in
   `composer.json`, and this README, to match.
2. **Push to a public Git repo** (e.g. GitHub) whose URL matches `homepage`/`support`.
3. **Tag a release** using SemVer — Packagist derives versions from Git tags:
   ```bash
   git tag v1.0.0
   git push origin v1.0.0
   ```
4. **Submit on Packagist** — sign in at <https://packagist.org>, click
   *Submit*, and paste the repo URL.
5. **Enable auto-updates** — add the Packagist webhook (or install the
   Packagist GitHub app) so new tags publish automatically.

After that, `composer require iamthenewking/ghl-quote` works for everyone. Validate
locally first:

```bash
composer validate --strict
```

> Do **not** commit a real `.env` or credentials to the published repo. This
> package reads config from the host app's environment; only `.env.example`
> (with placeholders) is included.

---

## Notes & caveats

- `bookAppointment()` omits `endTime` on purpose so GoHighLevel derives the
  appointment length from the calendar's own slot duration.
- Booking **and** note attachment are best-effort: a failure there is reported
  in the result but never fails the lead capture.
- The `POST /ghl/quote` route is in the `web` group, so it is **CSRF-protected**.
  The bundled view sends the `X-CSRF-TOKEN` header automatically. If you POST
  from your own frontend, include a valid CSRF token or move the route to a
  group excluded from CSRF.

---

## Troubleshooting

| Symptom | Likely cause / fix |
|---------|--------------------|
| `419 Page Expired` on submit | Missing CSRF token — use the bundled view or send `X-CSRF-TOKEN`. |
| Availability list is empty | Token/location wrong, or the calendars have no open slots in the window. |
| Route not found | `GHL_REGISTER_ROUTES=false`, or run `php artisan route:clear` / `config:clear`. |
| `Class not found` after install | Run `composer dump-autoload`. |
