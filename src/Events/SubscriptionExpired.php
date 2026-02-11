<?php

namespace Codeplugtech\CreemPayments\Events;

use Codeplugtech\CreemPayments\Subscription;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpired
{
    use Dispatchable, SerializesModels;


    public function __construct(public Model $billable,public Subscription $subscription,public array $payload)
    {
    }
}
