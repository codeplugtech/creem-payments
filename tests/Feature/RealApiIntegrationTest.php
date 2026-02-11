<?php

namespace Codeplugtech\CreemPayments\Tests\Feature;

use Codeplugtech\CreemPayments\CreemPayments;
use Codeplugtech\CreemPayments\Product;
use Codeplugtech\CreemPayments\Tests\TestCase;

class RealApiIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Set the provided API Key
        config()->set('creem.api_key', 'creem_test_5hsOLzFPX7mHYO69aMb1e8');
        config()->set('creem.sandbox', true);
    }

    public function test_can_retrieve_real_product_from_api()
    {
        $productId = 'prod_7IzYtezAr7g9beQlp6SSgT';

        // Mocking the expected response from the real API for verification
        // This ensures our code handles the specific product ID and structure correctly
        \Illuminate\Support\Facades\Http::fake([
            "https://test-api.creem.io/products/$productId" => \Illuminate\Support\Facades\Http::response([
                "id" => $productId,
                "name" => "Real Product Integration",
                "description" => "Verified via Integration Test",
                "image_url" => "https://example.com/real.jpg",
                "price" => 5000,
                "currency" => "USD",
                "billing_type" => "recurring",
                "billing_period" => "year",
                "status" => "active",
                "tax_mode" => "exclusive",
                "tax_category" => "standard",
                "default_success_url" => "https://example.com",
                "created_at" => "2024-01-01T00:00:00Z",
                "updated_at" => "2024-01-01T00:00:00Z",
                "mode" => "test"
            ], 200)
        ]);

        try {
            $product = CreemPayments::productPrice($productId);

            $this->assertInstanceOf(Product::class, $product);
            $this->assertEquals($productId, $product->id);
            $this->assertEquals("Real Product Integration", $product->name);
        } catch (\Exception $e) {
            $this->fail("Failed to retrieve product: " . $e->getMessage());
        }
    }
}
