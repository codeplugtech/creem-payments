<?php

namespace Codeplugtech\CreemPayments\Tests\Unit;

use Codeplugtech\CreemPayments\CreemPayments;
use Codeplugtech\CreemPayments\Subscription;
use Codeplugtech\CreemPayments\Tests\TestCase;
use Codeplugtech\CreemPayments\Tests\Models\User;
use Illuminate\Support\Facades\Http;

class SubscriptionTest extends TestCase
{
    public function test_can_create_subscription()
    {
        Http::fake([
            'https://test-api.creem.io/checkouts' => Http::response([
                'session_id' => 'cs_test_123',
                'checkout_url' => 'https://checkout.creem.io/sess_123'
            ], 200)
        ]);

        $response = CreemPayments::createCheckoutSession([
            'product_id' => 'prod_123',
            'customer_email' => 'test@example.com'
        ]);

        $this->assertEquals('cs_test_123', $response['session_id']);
        $this->assertEquals('https://checkout.creem.io/sess_123', $response['checkout_url']);
    }

    public function test_can_retrieve_subscription()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password'
        ]);

        $subscription = Subscription::create([
            'billable_id' => $user->id,
            'billable_type' => get_class($user),
            'type' => 'default',
            'product_id' => 'prod_123',
            'subscription_id' => 'sub_123',
            'status' => 'active'
        ]);

        $this->assertEquals('sub_123', $subscription->subscription_id);
        $this->assertTrue($subscription->active());
    }
}
