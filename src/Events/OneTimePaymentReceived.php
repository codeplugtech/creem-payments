<?php

namespace Codeplugtech\CreemPayments\Events;

use Codeplugtech\CreemPayments\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OneTimePaymentReceived
{
    use Dispatchable, SerializesModels;


    public function __construct(public Model $billable, public Transaction $transaction, public array $payload)
    {

    }
}
