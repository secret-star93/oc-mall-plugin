<?php

namespace OFFLINE\Mall\Tests\Classes\Totals;

use Auth;
use OFFLINE\Mall\Classes\Totals\TotalsCalculator;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\CustomFieldOption;
use OFFLINE\Mall\Models\CustomFieldValue;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\ShippingMethod;
use OFFLINE\Mall\Models\ShippingMethodRate;
use OFFLINE\Mall\Models\Tax;
use OFFLINE\Mall\Models\Variant;
use PluginTestCase;

class TotalsCalculatorTest extends PluginTestCase
{
    public function test_it_works_for_a_single_product()
    {
        $quantity = 5;
        $price    = 20000;

        $cart = $this->getCart();
        $cart->addProduct($this->getProduct($price), $quantity);

        $calc = new TotalsCalculator($cart);
        $this->assertEquals($quantity * $price * 100, $calc->totalPostTaxes());
    }

    public function test_it_works_for_multiple_products()
    {
        $quantity = 5;
        $price    = 20000;

        $cart = $this->getCart();

        $cart->addProduct($this->getProduct($price), $quantity);
        $cart->addProduct($this->getProduct($price / 2), $quantity * 2);

        $calc = new TotalsCalculator($cart);
        $this->assertEquals(
            (($quantity * $price) + ($quantity * 2 * $price / 2)) * 100,
            $calc->totalPostTaxes()
        );
    }

    public function test_it_calculates_taxes_included()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 20);

        $product                     = $this->getProduct(100);
        $product->price_includes_tax = true;
        $product->taxes()->attach([$tax1->id, $tax2->id]);
        $product->save();

        $cart = $this->getCart();
        $cart->addProduct($product, 2);

        $calc = new TotalsCalculator($cart);
        $this->assertEquals(20000, $calc->totalPostTaxes());
        $this->assertEquals(4615, round($calc->totalTaxes(), 2));
        $this->assertCount(2, $calc->taxes());
        $this->assertEquals(1538, $calc->taxes()[0]->total());
        $this->assertEquals(3076, $calc->taxes()[1]->total());
    }

    public function test_it_calculates_taxes_excluded()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 20);

        $product                     = $this->getProduct(80);
        $product->price_includes_tax = false;
        $product->taxes()->attach([$tax1->id, $tax2->id]);
        $product->save();

        $cart = $this->getCart();
        $cart->addProduct($product, 2);

        $calc = new TotalsCalculator($cart);
        $this->assertEquals(20800, $calc->totalPostTaxes());
        $this->assertEquals(4800, round($calc->totalTaxes(), 2));
        $this->assertCount(2, $calc->taxes());
        $this->assertEquals(1600, $calc->taxes()[0]->total());
        $this->assertEquals(3200, $calc->taxes()[1]->total());
    }

    public function test_it_calculates_shipping_cost()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 20);

        $product                     = $this->getProduct(100);
        $product->price_includes_tax = true;
        $product->taxes()->attach([$tax1->id, $tax2->id]);
        $product->save();

        $cart = $this->getCart();
        $cart->addProduct($product, 2);

        $shippingMethod        = ShippingMethod::first();
        $shippingMethod->price = 100;
        $shippingMethod->save();

        $shippingMethod->taxes()->attach($tax1);

        $cart->setShippingMethod($shippingMethod);

        $calc = new TotalsCalculator($cart);
        $this->assertEquals(30000, $calc->totalPostTaxes());
        $this->assertEquals(5615, $calc->totalTaxes());
        $this->assertCount(2, $calc->taxes());
        $this->assertEquals(2538, $calc->taxes()[0]->total());
        $this->assertEquals(3076, $calc->taxes()[1]->total());
    }

    public function test_it_calculates_shipping_cost_with_special_rates()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 20);

        $product                     = $this->getProduct(100);
        $product->price_includes_tax = true;
        $product->weight             = 1000;
        $product->taxes()->attach([$tax1->id, $tax2->id]);
        $product->save();

        $cart = $this->getCart();
        $cart->addProduct($product, 2);

        $shippingMethod        = ShippingMethod::first();
        $shippingMethod->price = 100;
        $shippingMethod->save();

        $rate                     = new ShippingMethodRate();
        $rate->from_weight        = 2000;
        $rate->price              = 200;
        $rate->shipping_method_id = $shippingMethod->id;
        $rate->save();

        $shippingMethod->taxes()->attach($tax1);

        $cart->setShippingMethod($shippingMethod);

        $calc = new TotalsCalculator($cart);
        $this->assertEquals(40000, $calc->totalPostTaxes());
        $this->assertEquals(6615, $calc->totalTaxes());
        $this->assertCount(2, $calc->taxes());
        $this->assertEquals(3538, $calc->taxes()[0]->total());
        $this->assertEquals(3076, $calc->taxes()[1]->total());
    }

    public function test_it_calculates_variant_cost()
    {
        $product            = Product::first();
        $product->stackable = true;
        $product->price = 200;
        $product->save();

        $sizeA        = new CustomFieldOption();
        $sizeA->name  = 'Size A';
        $sizeA->price = 100;
        $sizeA->sort_order = 1;
        $sizeB        = new CustomFieldOption();
        $sizeB->name  = 'Size B';
        $sizeB->price = 200;
        $sizeB->sort_order = 1;

        $field             = new CustomField();
        $field->name       = 'Size';
        $field->type       = 'dropdown';
        $field->product_id = $product->id;
        $field->save();

        $field->options()->save($sizeA);
        $field->options()->save($sizeB);

        $variant             = new Variant();
        $variant->product_id = $product->id;
        $variant->stock      = 1;
        $variant->save();

        $variant->custom_field_options()->attach($sizeA);
        $variant->custom_field_options()->attach($sizeB);

        $customFieldValueA                         = new CustomFieldValue();
        $customFieldValueA->custom_field_id        = $field->id;
        $customFieldValueA->custom_field_option_id = $sizeA->id;
        $customFieldValueB                         = new CustomFieldValue();
        $customFieldValueB->custom_field_id        = $field->id;
        $customFieldValueB->custom_field_option_id = $sizeB->id;

        $cart = $this->getCart();
        $cart->addProduct($product, 2, $customFieldValueA);
        $cart->addProduct($product, 1, $customFieldValueB);

        $calc = new TotalsCalculator($cart);
        $this->assertEquals(1000 * 100, $calc->totalPostTaxes());
    }

    protected function getProduct($price)
    {
        $product        = Product::first()->replicate();
        $product->price = $price;
        $product->save();

        return $product;
    }

    protected function getCart(): Cart
    {
        $cart = new Cart();
        $cart->save();

        return $cart;
    }

    protected function getTax($name, int $percentage): Tax
    {
        $tax1             = new Tax();
        $tax1->name       = $name;
        $tax1->percentage = $percentage;
        $tax1->save();

        return $tax1;
    }
}
