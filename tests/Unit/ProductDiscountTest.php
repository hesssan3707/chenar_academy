<?php

namespace Tests\Unit;

use App\Models\Product;
use Tests\TestCase;

class ProductDiscountTest extends TestCase
{
    public function test_final_price_falls_back_to_base_price(): void
    {
        $product = new Product([
            'base_price' => 120000,
            'sale_price' => null,
            'discount_type' => null,
            'discount_value' => null,
        ]);

        $this->assertSame(120000, $product->finalPrice());
        $this->assertFalse($product->hasDiscount());
        $this->assertNull($product->discountLabel());
    }

    public function test_final_price_uses_sale_price_when_no_discount_is_set(): void
    {
        $product = new Product([
            'base_price' => 120000,
            'sale_price' => 100000,
            'discount_type' => null,
            'discount_value' => null,
        ]);

        $this->assertSame(100000, $product->finalPrice());
        $this->assertTrue($product->hasDiscount());
        $this->assertSame('17% OFF', $product->discountLabel());
    }

    public function test_discount_price_takes_priority_over_sale_price(): void
    {
        $product = new Product([
            'base_price' => 200000,
            'sale_price' => 150000,
            'discount_type' => 'percent',
            'discount_value' => 25,
        ]);

        $this->assertSame(150000, $product->sale_price);
        $this->assertSame(150000, $product->finalPrice());
        $this->assertTrue($product->hasDiscount());
        $this->assertSame('25% OFF', $product->discountLabel());
    }

    public function test_amount_discount_generates_amount_label(): void
    {
        $product = new Product([
            'base_price' => 200000,
            'sale_price' => null,
            'discount_type' => 'amount',
            'discount_value' => 50000,
        ]);

        $this->assertSame(150000, $product->finalPrice());
        $this->assertTrue($product->hasDiscount());
        $this->assertSame('50,000 OFF', $product->discountLabel());
    }
}
