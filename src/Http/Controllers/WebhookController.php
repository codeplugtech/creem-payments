<?php

namespace Codeplugtech\CreemPayments\Http\Controllers;

use Codeplugtech\CreemPayments\CreemPayments;
use Codeplugtech\CreemPayments\Enum\SubscriptionStatusEnum;
use Codeplugtech\CreemPayments\Events\SubscriptionActive;
use Codeplugtech\CreemPayments\Events\SubscriptionCanceled;
use Codeplugtech\CreemPayments\Events\SubscriptionExpired;
use Codeplugtech\CreemPayments\Events\SubscriptionPaid;
use Codeplugtech\CreemPayments\Events\SubscriptionPaused;
use Codeplugtech\CreemPayments\Events\SubscriptionUpdated;
use Codeplugtech\CreemPayments\Events\SubscriptionTrialing;
use Codeplugtech\CreemPayments\Http\Middleware\VerifyWebhookSignature;
use Codeplugtech\CreemPayments\Subscription;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends Controller
{
    public function __construct()
    {
        if (config('creem.webhook_secret')) {
            $this->middleware(VerifyWebhookSignature::class);
        }
    }

    public function handleWebhook(Request $request)
    {
        $payload = $request->all();
        $eventType = $payload['eventType'];

        $method = 'handle' . Str::studly(Str::replace('.', '_', $eventType));

        if (method_exists($this, $method)) {
            $this->{$method}($payload);
            return new Response('Webhook Handled');
        }

        return new Response('Webhook Ignored');
    }

    protected function handleCheckoutCompleted(array $payload)
    {
        $data = $payload['object'];
        if (!isset($data['subscription'])) {
            return;
        }
        $subscriptionData = $data['subscription'];
        $customerData = $data['customer'];
        $orderData = $data['order'];
        if ($this->findSubscription($subscriptionData['id'])) {
            $billable = $this->findCustomer($customerData['email']);
            $billable->transactions()->updateOrCreate([
                'payment_id' => $orderData['transaction'],
            ], [
                'subscription_id' => $subscriptionData['id'],
                'status' => $orderData['status'],
                'total' => $orderData['amount'],
                'tax' => $orderData['tax_amount'],
                'currency' => $orderData['currency'],
                'billed_at' => Carbon::parse($orderData['created_at']),
            ]);
        }
    }

    protected function handleSubscriptionActive(array $payload)
    {
        $subscriptionData = $payload['object'];
        $customerData = $subscriptionData['customer'];
        if (!$this->findSubscription($subscriptionData['id'])) {
            $billable = $this->findCustomer($customerData['email']);
            $billable->subscriptions()->create([
                'type' => $subscriptionData['metadata']['type'],
                'subscription_id' => $subscriptionData['id'],
                'product_id' => $subscriptionData['product']['id'],
                'status' => $subscriptionData['status'],
                'next_billing_at' => $subscriptionData['current_period_end_date'] ?? null,
            ]);
        }
        $this->updateSubscriptionStatus($payload, SubscriptionStatusEnum::ACTIVE->value, SubscriptionActive::class);
    }

    protected function handleSubscriptionPaid(array $payload)
    {
        $this->updateSubscriptionStatus($payload, SubscriptionStatusEnum::ACTIVE->value, SubscriptionPaid::class);
    }

    protected function handleSubscriptionCanceled(array $payload)
    {
        $this->updateSubscriptionStatus($payload, SubscriptionStatusEnum::CANCELED->value, SubscriptionCanceled::class);
    }

    protected function handleSubscriptionPaused(array $payload)
    {
        $this->updateSubscriptionStatus($payload, SubscriptionStatusEnum::PAUSED->value, SubscriptionPaused::class);
    }

    protected function handleSubscriptionExpired(array $payload)
    {
        // Map expired to CANCELED as EXPIRED is not in the requested enum list
        $this->updateSubscriptionStatus($payload, SubscriptionStatusEnum::CANCELED->value, SubscriptionExpired::class);
    }

    protected function handleSubscriptionTrialing(array $payload)
    {
        $this->updateSubscriptionStatus($payload, SubscriptionStatusEnum::TRIALING->value, SubscriptionTrialing::class);
    }

    protected function handleSubscriptionScheduledCancel(array $payload)
    {
        $data = $payload['object'];
        if (!$subscription = $this->findSubscription($data['id'])) {
            return;
        }

        $subscription->update([
            'ends_at' => isset($data['current_period_end_date']) ? Carbon::parse($data['current_period_end_date']) : null
        ]);

        $billable = $this->findCustomer($data['customer']['email']);
        SubscriptionUpdated::dispatch($billable, $subscription, $payload);
    }


    private function updateSubscriptionStatus(array $payload, string $status, string $eventClass)
    {
        $data = $payload['object'];
        if (!$subscription = $this->findSubscription($data['id'])) {
            return;
        }

        $subscription->update([
            'status' => $status,
            'next_billing_at' => isset($data['current_period_end_date']) ? Carbon::parse($data['current_period_end_date']) : $subscription->next_billing_at
        ]);

        $billable = $this->findCustomer($data['customer']['email']);

        if (class_exists($eventClass)) {
            $eventClass::dispatch($billable, $subscription, $payload);
        }
    }


    protected function findSubscription(string $subscriptionId): ?Subscription
    {
        return (CreemPayments::$subscriptionModel)::where('subscription_id', $subscriptionId)->first();
    }

    protected function findCustomer(string $email)
    {
        $userModel = config('creem.user_model');
        return (new $userModel)->where('email', $email)->firstOrFail();
    }
}
