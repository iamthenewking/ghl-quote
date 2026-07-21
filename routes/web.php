<?php

declare(strict_types=1);

use Iamthenewking\GhlQuote\Http\Controllers\AvailabilityController;
use Iamthenewking\GhlQuote\Http\Controllers\QuoteController;
use Illuminate\Support\Facades\Route;

// Prefix and middleware are applied by the service provider.
Route::get('quote', [QuoteController::class, 'show'])->name('ghl.quote.show');
Route::post('quote', [QuoteController::class, 'store'])->name('ghl.quote.store');
Route::get('availability', [AvailabilityController::class, 'index'])->name('ghl.availability');
