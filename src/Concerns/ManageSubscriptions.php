<?php

namespace Codeplugtech\CreemPayments\Concerns;

use Codeplugtech\CreemPayments\CreemPayments;
use Codeplugtech\CreemPayments\Enum\SubscriptionStatusEnum;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait ManageSubscriptions
{
    /**
     * Get all of the subscriptions for the model.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(CreemPayments::$subscriptionModel, $this->getForeignKey())->orderBy('created_at', 'desc');
    }

    public function subscription()
    {
        return $this->subscriptions?->where('status',SubscriptionStatusEnum::ACTIVE->value)->first();
    }

    /**
     * Determine if the model has a given subscription.
     */
    public function subscribed(string $name = 'default', ?string $plan = null): bool
    {
        $subscription = $this->subscription($name);

        if (!$subscription || !$subscription->valid()) {
            return false;
        }

        return $plan ? $subscription->hasPlan($plan) : true;
    }
}
