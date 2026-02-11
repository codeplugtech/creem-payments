<?php

namespace Codeplugtech\CreemPayments\Tests\Unit;

use Codeplugtech\CreemPayments\Product;
use Codeplugtech\CreemPayments\Tests\TestCase;

class ProductTest extends TestCase
{
    public function test_can_instantiate_product_from_api_payload()
    {
        $payload = [
            "id" => "prod_d1AY2Sadk9YAvLI0pj97f",
            "name" => "Monthly",
            "description" => "Monthly",
            "image_url" => "https://example.com/image.jpg",
            "price" => 1000,
            "currency" => "EUR",
            "billing_type" => "recurring",
            "billing_period" => "every-month",
            "status" => "active",
            "tax_mode" => "exclusive",
            "tax_category" => "saas",
            "default_success_url" => "https://example.com/success",
            "created_at" => "2024-10-11T11:50:00.182Z",
            "updated_at" => "2024-10-11T11:50:00.182Z",
            "mode" => "local"
        ];

        $product = new Product($payload);

        $this->assertEquals("prod_d1AY2Sadk9YAvLI0pj97f", $product->id);
        $this->assertEquals("Monthly", $product->name);
        $this->assertEquals("https://example.com/image.jpg", $product->imageUrl);
        $this->assertEquals(1000, $product->price);
        $this->assertEquals("EUR", $product->currency);
        $this->assertEquals("recurring", $product->billingType);
        $this->assertEquals("€10", $product->getFormattedPrice()); // Assuming formatAmount works
    }

    public function test_can_retrieve_list_of_products()
    {

        $payload = [
            'items' => [
                [
                    "id" => "prod_1",
                    "name" => "Product 1",
                    "description" => "Desc 1",
                    "image_url" => "url1",
                    "price" => 1000,
                    "currency" => "EUR",
                    "billing_type" => "recurring",
                    "billing_period" => "every-month",
                    "status" => "active",
                    "tax_mode" => "exclusive",
                    "tax_category" => "saas",
                    "default_success_url" => "url",
                    "created_at" => "2024-10-11T11:50:00.182Z",
                    "updated_at" => "2024-10-11T11:50:00.182Z",
                    "mode" => "local"
                ],
                [
                    "id" => "prod_2",
                    "name" => "Product 2",
                    "description" => "Desc 2",
                    "image_url" => "url2",
                    "price" => 2000,
                    "currency" => "USD",
                    "billing_type" => "one_time",
                    "billing_period" => "none",
                    "status" => "active",
                    "tax_mode" => "inclusive",
                    "tax_category" => "standard",
                    "default_success_url" => "url",
                    "created_at" => "2024-10-11T11:50:00.182Z",
                    "updated_at" => "2024-10-11T11:50:00.182Z",
                    "mode" => "live"
                ]
            ]
        ];

        \Illuminate\Support\Facades\Http::fake([
            'https://test-api.creem.io/products' => \Illuminate\Support\Facades\Http::response($payload, 200)
        ]);

        $products = \Codeplugtech\CreemPayments\CreemPayments::products();

        $this->assertCount(2, $products);
        $this->assertInstanceOf(Product::class, $products->first());
        $this->assertEquals("prod_1", $products->first()->id);
        $this->assertEquals("prod_2", $products->last()->id);
    }

    public function test_can_retrieve_single_product()
    {
        $payload = [
            "id" => "prod_single",
            "name" => "Single Product",
            "description" => "Desc",
            "image_url" => "url",
            "price" => 500,
            "currency" => "USD",
            "billing_type" => "one_time",
            "billing_period" => "none",
            "status" => "active",
            "tax_mode" => "inclusive",
            "tax_category" => "standard",
            "default_success_url" => "url",
            "created_at" => "2024-10-11T11:50:00.182Z",
            "updated_at" => "2024-10-11T11:50:00.182Z",
            "mode" => "test"
        ];

        \Illuminate\Support\Facades\Http::fake([
            'https://test-api.creem.io/products/prod_single' => \Illuminate\Support\Facades\Http::response($payload, 200)
        ]);

        $product = \Codeplugtech\CreemPayments\CreemPayments::productPrice('prod_single');

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals("prod_single", $product->id);
    }
}
