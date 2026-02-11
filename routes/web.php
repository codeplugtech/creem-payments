<?php

use Illuminate\Support\Facades\Route;
use Codeplugtech\CreemPayments\Http\Controllers\WebhookController;

Route::post('webhook', [WebhookController::class, 'handleWebhook'])
    ->name('webhook');

