<?php

namespace Codeplugtech\CreemPayments\Tests\Feature;

use Codeplugtech\CreemPayments\Enum\SubscriptionStatusEnum;
use Codeplugtech\CreemPayments\Events\SubscriptionActive;
use Codeplugtech\CreemPayments\Events\SubscriptionCanceled;
use Codeplugtech\CreemPayments\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;

class WebhookTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        Config::set('creem.webhook_secret', 'secret');
    }

    protected function createBillable($email = 'test@example.com')
    {
        $user = new \Codeplugtech\CreemPayments\Tests\Models\User();
        $user->email = $email;
        $user->name = 'Test User';
        $user->password = bcrypt('password');
        $user->save();
        return $user;
    }

    public function test_it_handles_subscription_active_webhook()
    {
        $user = $this->createBillable('test@example.com');
        $subscription = $user->subscriptions()->create([
            'subscription_id' => 'sub_123',
            'type' => 'default',
            'product_id' => 'prod_123',
            'status' => 'pending',
        ]);

        $payload = [
            'eventType' => 'subscription.active',
            'object' => [
                'id' => 'sub_123',
                'customer' => ['email' => 'test@example.com'],
                'status' => 'active',
                'current_period_end_date' => now()->addMonth()->toIso8601String(),
            ]
        ];

        $content = json_encode($payload);
        $signature = hash_hmac('sha256', $content, 'secret');

        $response = $this->call(
            'POST',
            '/creem/webhook',
            [],
            [],
            [],
            [
                'HTTP_creem-signature' => $signature,
                'CONTENT_TYPE' => 'application/json'
            ],
            $content
        );

        $response->assertOk();

        $this->assertEquals(SubscriptionStatusEnum::ACTIVE->value, $subscription->refresh()->status);
        Event::assertDispatched(SubscriptionActive::class);
    }

    public function test_it_handles_subscription_canceled_webhook()
    {
        $user = $this->createBillable('test@example.com');
        $subscription = $user->subscriptions()->create([
            'subscription_id' => 'sub_123',
            'type' => 'default',
            'product_id' => 'prod_123',
            'status' => 'active',
        ]);

        $payload = [
            'eventType' => 'subscription.canceled',
            'object' => [
                'id' => 'sub_123',
                'customer' => ['email' => 'test@example.com'],
                'status' => 'canceled',
            ]
        ];

        $content = json_encode($payload);
        $signature = hash_hmac('sha256', $content, 'secret');

        $response = $this->call(
            'POST',
            '/creem/webhook',
            [],
            [],
            [],
            [
                'HTTP_creem-signature' => $signature,
                'CONTENT_TYPE' => 'application/json'
            ],
            $content
        );

        $response->assertOk();

        $this->assertEquals(SubscriptionStatusEnum::CANCELED->value, $subscription->refresh()->status);
        Event::assertDispatched(SubscriptionCanceled::class);
    }
}
