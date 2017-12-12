<?php namespace OFFLINE\Mall\Tests\Models;

use DB;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\CustomFieldOption;
use OFFLINE\Mall\Models\CustomFieldValue;
use OFFLINE\Mall\Models\Discount;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\ShippingMethod;
use OFFLINE\Mall\Models\Variant;
use PluginTestCase;

class CartTest extends PluginTestCase
{
    public function test_it_stacks_products()
    {
        $product            = Product::first();
        $product->stackable = true;
        $product->save();

        $cart = new Cart();
        $cart->addProduct($product);

        $this->assertEquals(1, $cart->products->count());
        $this->assertEquals(1, $cart->products->first()->quantity);

        $cart->addProduct($product);
        $this->assertEquals(1, $cart->products->count());
        $this->assertEquals(2, $cart->products->first()->quantity);
    }

    public function test_it_stacks_product_variants()
    {
        $product            = Product::first();
        $product->stackable = true;
        $product->save();

        $sizeA             = new CustomFieldOption();
        $sizeA->name       = 'Size A';
        $sizeA->sort_order = 1;
        $sizeB             = new CustomFieldOption();
        $sizeB->name       = 'Size B';
        $sizeB->sort_order = 2;

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

        $customFieldValue                         = new CustomFieldValue();
        $customFieldValue->custom_field_id        = $field->id;
        $customFieldValue->custom_field_option_id = $sizeA->id;
        $customFieldValue->save();

        $cart = new Cart();
        $cart->addProduct($product, 1, $customFieldValue);

        $this->assertEquals(1, $cart->products->count());
        $this->assertEquals(1, $cart->products->first()->quantity);
        $this->assertEquals($customFieldValue->id, $cart->products->first()->custom_field_values[0]->id);

        $cart->addProduct($product, 1, $customFieldValue);
        $this->assertEquals(1, $cart->products->count());
        $this->assertEquals(2, $cart->products->first()->quantity);
        $this->assertEquals($customFieldValue->id, $cart->products->first()->custom_field_values[0]->id);
    }

    public function test_it_doesnt_stack_products()
    {
        $product            = Product::first();
        $product->stackable = false;
        $product->save();

        $cart = new Cart();
        $cart->addProduct($product);

        $this->assertEquals(1, $cart->products->count());
        $this->assertEquals(1, $cart->products->first()->quantity);

        $cart->addProduct($product);
        $this->assertEquals(2, $cart->products->count());
        $this->assertEquals(1, $cart->products->first()->quantity);
    }

    public function test_it_doesnt_stack_product_variants()
    {
        $product            = Product::first();
        $product->stackable = false;
        $product->save();

        $sizeA             = new CustomFieldOption();
        $sizeA->name       = 'Size A';
        $sizeA->sort_order = 1;
        $sizeB             = new CustomFieldOption();
        $sizeB->name       = 'Size B';
        $sizeB->sort_order = 2;

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


        $customFieldValue                         = new CustomFieldValue();
        $customFieldValue->custom_field_id        = $field->id;
        $customFieldValue->custom_field_option_id = $sizeA->id;
        $customFieldValue->save();

        $cart = new Cart();
        $cart->addProduct($product, 1, $customFieldValue);

        $cart->products->first()->refresh('custom_field_values');
        $this->assertEquals(1, $cart->products->count());
        $this->assertEquals(1, $cart->products->first()->quantity);
        $this->assertEquals($customFieldValue->id, $cart->products->first()->custom_field_values[0]->id);

        $cart->addProduct($product, 1, $customFieldValue);
        $this->assertEquals(2, $cart->products->count());
        $this->assertEquals(1, $cart->products->first()->quantity);
        $this->assertEquals($customFieldValue->id, $cart->products[1]->custom_field_values[0]->id);
    }

    public function test_it_doesnt_stack_different_product_variants()
    {
        $product            = Product::first();
        $product->stackable = true;
        $product->save();

        $sizeA             = new CustomFieldOption();
        $sizeA->name       = 'Size A';
        $sizeA->sort_order = 1;
        $sizeB             = new CustomFieldOption();
        $sizeB->name       = 'Size B';
        $sizeB->sort_order = 2;

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

        $cart = new Cart();
        $cart->addProduct($product, 1, $customFieldValueA);

        $this->assertEquals(1, $cart->products->count());
        $this->assertEquals(1, $cart->products->first()->quantity);
        $this->assertEquals($customFieldValueA->id, $cart->products->first()->custom_field_values[0]->id);

        $cart->addProduct($product, 1, $customFieldValueB);
        $this->assertEquals(2, $cart->products->count());
        $this->assertEquals(1, $cart->products->first()->quantity);
        $this->assertEquals($customFieldValueA->id, $cart->products[0]->custom_field_values[0]->id);
        $this->assertEquals($customFieldValueB->id, $cart->products[1]->custom_field_values[0]->id);
    }

    public function test_it_doesnt_stack_different_product_variants_with_text_values()
    {
        $product            = Product::first();
        $product->stackable = true;
        $product->save();

        $field             = new CustomField();
        $field->name       = 'Size';
        $field->type       = 'text';
        $field->product_id = $product->id;
        $field->save();

        $variant             = new Variant();
        $variant->product_id = $product->id;
        $variant->stock      = 1;
        $variant->save();

        $customFieldValueA                  = new CustomFieldValue();
        $customFieldValueA->custom_field_id = $field->id;
        $customFieldValueA->value           = 'Test';

        $customFieldValueB                  = new CustomFieldValue();
        $customFieldValueB->custom_field_id = $field->id;
        $customFieldValueA->value           = 'Test';

        $cart = new Cart();
        $cart->addProduct($product, 1, $customFieldValueA);

        $this->assertEquals(1, $cart->products->count());
        $this->assertEquals(1, $cart->products->first()->quantity);
        $this->assertEquals($customFieldValueA->id, $cart->products->first()->custom_field_values[0]->id);

        $cart->addProduct($product, 1, $customFieldValueB);
        $this->assertEquals(2, $cart->products->count());
        $this->assertEquals(1, $cart->products->first()->quantity);
        $this->assertEquals($customFieldValueA->id, $cart->products[0]->custom_field_values[0]->id);
        $this->assertEquals($customFieldValueB->id, $cart->products[1]->custom_field_values[0]->id);
    }

    public function test_it_uses_default_quantity()
    {
        $product                   = Product::first();
        $product->quantity_default = 4;
        $product->save();

        $cart = new Cart();
        $cart->addProduct($product);

        $this->assertEquals(4, $cart->products->first()->quantity);
    }

    public function test_it_enforces_min_quantity()
    {
        $product               = Product::first();
        $product->quantity_min = 4;
        $product->save();

        $cart = new Cart();
        $cart->addProduct($product, 2);

        $this->assertEquals(4, $cart->products->first()->quantity);
    }

    public function test_it_enforces_max_quantity()
    {
        $product               = Product::first();
        $product->quantity_max = 4;
        $product->save();

        $cart = new Cart();
        $cart->addProduct($product, 12);

        $this->assertEquals(4, $cart->products->first()->quantity);
    }

    public function test_it_enforces_max_quantity_on_stacked_products()
    {
        $product               = Product::first();
        $product->stackable    = true;
        $product->quantity_max = 4;
        $product->save();

        $cart = new Cart();
        $cart->addProduct($product, 2);
        $this->assertEquals(2, $cart->products->first()->quantity);
        $cart->addProduct($product, 3);
        $this->assertEquals(4, $cart->products->first()->quantity);
    }

    public function test_it_increases_the_quantity_for_stacked_products()
    {
        $product            = Product::first();
        $product->stackable = true;
        $product->save();

        $cart = new Cart();
        $cart->addProduct($product, 2);
        $this->assertEquals(2, $cart->products->first()->quantity);
        $cart->addProduct($product, 3);
        $this->assertEquals(5, $cart->products->first()->quantity);
    }

    public function test_the_same_discount_cannot_be_applied_twice()
    {
        $this->expectException(ValidationException::class);

        $product = Product::first();
        $product->save();

        $cart = new Cart();
        $cart->addProduct($product);

        $discount       = new Discount();
        $discount->code = 'Test';
        $discount->name = 'Test discount';
        $discount->type = 'rate';
        $discount->rate = 25;
        $discount->save();

        $cart->applyDiscount($discount);
        $cart->applyDiscount($discount);

        $this->assertEquals(1, $cart->discounts->count());
    }

    public function test_only_one_alternate_price_discount_is_applied()
    {
        $this->expectException(ValidationException::class);

        $product = Product::first();
        $product->save();

        $cart = new Cart();
        $cart->addProduct($product);

        $discountA                  = new Discount();
        $discountA->code            = 'Test';
        $discountA->name            = 'Test discount';
        $discountA->type            = 'alternate_price';
        $discountA->alternate_price = 25;
        $discountA->save();

        $discountB                  = new Discount();
        $discountB->code            = 'Test';
        $discountB->name            = 'Test discount';
        $discountB->type            = 'alternate_price';
        $discountB->alternate_price = 25;
        $discountB->save();

        $cart->applyDiscount($discountA);
        $cart->applyDiscount($discountB);

        $this->assertEquals(1, $cart->discounts->count());
    }

    public function test_only_one_shipping_discount_is_applied()
    {
        $this->expectException(ValidationException::class);

        $product = Product::first();
        $product->save();

        $cart = new Cart();
        $cart->addProduct($product);

        $discountA                       = new Discount();
        $discountA->code                 = 'Test';
        $discountA->name                 = 'Test discount';
        $discountA->type                 = 'shipping';
        $discountA->shipping_price       = 25;
        $discountA->shipping_description = 'Test shipping';
        $discountA->save();

        $discountB = $discountA->replicate();
        $discountB->save();

        $cart->applyDiscount($discountA);
        $cart->applyDiscount($discountB);

        $this->assertEquals(1, $cart->discounts->count());
    }

    public function test_shipping_method_gets_reset_if_it_becomes_unavailable()
    {
        DB::table('offline_mall_shipping_methods')->truncate();

        $product        = Product::first();
        $product->price = 100;
        $product->save();

        $availableMethod                        = $this->getShippingMethod();
        $availableMethod->available_above_total = 100;
        $availableMethod->save();

        $unavailableMethod                        = $this->getShippingMethod();
        $unavailableMethod->available_above_total = 200;
        $unavailableMethod->save();

        $cart = new Cart();
        $cart->addProduct($product, 2);
        $cart->setShippingMethod($unavailableMethod);

        $available = ShippingMethod::getAvailableByCart($cart);

        $this->assertCount(2, $available);
        $this->assertEquals($unavailableMethod->id, $cart->shipping_method_id);

        // Remove one item so that the selected shipping method becomes unavailable
        $cart->setQuantity($cart->products->first()->id, 1);

        $available = ShippingMethod::getAvailableByCart($cart);

        $this->assertCount(1, $available);
        $this->assertEquals($availableMethod->id, $cart->shipping_method_id);
    }

    public function test_shipping_method_gets_nulled_of_none_is_available()
    {
        DB::table('offline_mall_shipping_methods')->truncate();

        $product        = Product::first();
        $product->price = 100;
        $product->save();

        $availableMethod                        = $this->getShippingMethod();
        $availableMethod->available_below_total = 200;
        $availableMethod->save();

        $cart = new Cart();
        $cart->addProduct($product, 1);
        $cart->setShippingMethod($availableMethod);

        $this->assertEquals($availableMethod->id, $cart->shipping_method_id);

        // The selected shipping method becomes unavailable
        $cart->addProduct($product, 1);

        $available = ShippingMethod::getAvailableByCart($cart);

        $this->assertCount(0, $available);
        $this->assertNull($cart->shipping_method_id);
    }

    /**
     * @return ShippingMethod
     */
    protected function getShippingMethod(): ShippingMethod
    {
        $availableMethod             = new ShippingMethod();
        $availableMethod->name       = 'Available';
        $availableMethod->price      = 100;
        $availableMethod->sort_order = 1;
        $availableMethod->save();

        return $availableMethod;
    }
}
