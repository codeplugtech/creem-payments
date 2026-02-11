<?php

namespace Codeplugtech\CreemPayments;

class Product
{
    public string $id;
    public string $name;
    public string $description;
    public ?string $imageUrl;
    public int $price;
    public string $currency;
    public string $billingType;
    public string $billingPeriod;
    public string $status;
    public string $taxMode;
    public string $taxCategory;
    public ?string $defaultSuccessUrl;
    public \DateTime $createdAt;
    public \DateTime $updatedAt;
    public string $mode;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->description = $data['description'];
        $this->imageUrl = $data['product_url'];
        $this->price = $data['price'];
        $this->currency = $data['currency'];
        $this->billingType = $data['billing_type'];
        $this->billingPeriod = $data['billing_period'];
        $this->status = $data['status'];
        $this->taxMode = $data['tax_mode'];
        $this->taxCategory = $data['tax_category'];
        $this->defaultSuccessUrl = $data['default_success_url'];
        $this->createdAt = new \DateTime($data['created_at']);
        $this->updatedAt = new \DateTime($data['updated_at']);
        $this->mode = $data['mode'];
    }

    public function getFormattedPrice(): string
    {
        return CreemPayments::formatAmount($this->price, $this->currency);
    }
}
