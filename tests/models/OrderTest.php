<?php namespace OFFLINE\Mall\Tests\Models;

use OFFLINE\Mall\Classes\Exceptions\OutOfStockException;
use OFFLINE\Mall\Classes\OrderStatus\InProgressState;
use OFFLINE\Mall\Classes\PaymentState\PendingState;
use OFFLINE\Mall\Models\Address;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Customer;
use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\CustomFieldOption;
use OFFLINE\Mall\Models\CustomFieldValue;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\OrderState;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\ShippingMethod;
use OFFLINE\Mall\Models\Tax;
use OFFLINE\Mall\Models\Variant;
use PluginTestCase;

class OrderTest extends PluginTestCase
{
    public function test_it_creates_a_new_order_from_a_cart()
    {
        $cart  = $this->getFullCart();
        $order = Order::fromCart($cart);
        $order->save();

        $this->assertEquals(1, $order->order_number);
        $this->assertEquals(PendingState::class, $order->payment_state);
        $this->assertEquals(OrderState::where('flag', OrderState::FLAG_NEW)->first()->id, $order->order_state_id);
        $this->assertEquals(76.92, $order->total_shipping_pre_taxes);
        $this->assertEquals(23.08, $order->total_shipping_taxes);
        $this->assertEquals(100.00, $order->total_shipping_post_taxes);
        $this->assertEquals(923.08, $order->total_product_pre_taxes);
        $this->assertEquals(1200.00, $order->total_product_post_taxes);

        $this->assertEquals(1000.00, $order->total_pre_taxes);
        $this->assertEquals(300.00, $order->total_taxes);
        $this->assertEquals(1300.00, $order->total_post_taxes);
        $this->assertEquals(2800, $order->total_weight);
        $this->assertNotEmpty($order->ip_address);

        $this->assertFalse($order->shipping_address_same_as_billing);
        $this->assertEquals(json_encode(Address::find(1)), $order->getOriginal('billing_address'));
        $this->assertEquals(json_encode(Address::find(2)), $order->getOriginal('shipping_address'));

        $this->assertNotNull($cart->deleted_at);
    }

    public function test_it_updates_product_stock()
    {
        $product        = Product::first();
        $product->price = 200;
        $product->stock = 10;
        $product->save();

        $cart = $this->getSimpleCart();
        $cart->addProduct($product, 2);

        $order = Order::fromCart($cart);
        $order->save();

        $this->assertEquals(8, $product->fresh()->stock);
    }

    public function test_it_prevents_out_of_stock_purchase()
    {
        $this->expectException(OutOfStockException::class);

        $product                               = Product::first();
        $product->price                        = 200;
        $product->stock                        = 10;
        $product->allow_out_of_stock_purchases = false;
        $product->save();

        $cart = $this->getSimpleCart();
        $cart->addProduct($product, 12);

        $order = Order::fromCart($cart);
        $order->save();

        $this->assertEquals(10, $product->fresh()->stock);
    }

    public function test_it_allows_explicit_out_of_stock_purchase()
    {
        $product                               = Product::first();
        $product->price                        = 200;
        $product->stock                        = 10;
        $product->allow_out_of_stock_purchases = true;
        $product->save();

        $cart = $this->getSimpleCart();
        $cart->addProduct($product, 12);

        $order = Order::fromCart($cart);
        $order->save();

        $this->assertEquals(-2, $product->fresh()->stock);
    }

    public function test_it_updates_variant_stock()
    {
        $product        = Product::first();
        $product->price = 200;
        $product->stock = 10;
        $product->save();

        $variant             = new Variant();
        $variant->product_id = $product->id;
        $variant->stock      = 20;
        $variant->save();

        $cart = $this->getSimpleCart();
        $cart->addProduct($product, 2, $variant);

        $order = Order::fromCart($cart);
        $order->save();

        $this->assertEquals(10, $product->fresh()->stock);
        $this->assertEquals(18, $variant->fresh()->stock);
    }

    public function test_it_prevents_out_of_stock_variant_purchase()
    {
        $this->expectException(OutOfStockException::class);

        $product        = Product::first();
        $product->price = 200;
        $product->stock = 10;
        $product->save();

        $variant                               = new Variant();
        $variant->product_id                   = $product->id;
        $variant->stock                        = 20;
        $product->allow_out_of_stock_purchases = false;
        $variant->save();

        $cart = $this->getSimpleCart();
        $cart->addProduct($product, 21, $variant);

        $order = Order::fromCart($cart);
        $order->save();

        $this->assertEquals(10, $product->fresh()->stock);
        $this->assertEquals(20, $variant->fresh()->stock);
    }

    public function test_it_allows_explicit_out_of_stock_variant_purchase()
    {
        $this->expectException(OutOfStockException::class);

        $product        = Product::first();
        $product->price = 200;
        $product->stock = 10;
        $product->save();

        $variant                               = new Variant();
        $variant->product_id                   = $product->id;
        $variant->stock                        = 20;
        $product->allow_out_of_stock_purchases = true;
        $variant->save();

        $cart = $this->getSimpleCart();
        $cart->addProduct($product, 21, $variant);

        $order = Order::fromCart($cart);
        $order->save();

        $this->assertEquals(10, $product->fresh()->stock);
        $this->assertEquals(-1, $variant->fresh()->stock);
    }

    protected function getFullCart(): Cart
    {
        $tax1             = new Tax();
        $tax1->name       = 'Tax 1';
        $tax1->percentage = 10;
        $tax1->save();
        $tax2             = new Tax();
        $tax2->name       = 'Tax 2';
        $tax2->percentage = 20;
        $tax2->save();

        $productA                     = Product::first();
        $productA->stackable          = true;
        $productA->price              = 200;
        $productA->weight             = 400;
        $productA->stock              = 10;
        $productA->price_includes_tax = true;
        $productA->save();
        $productA->taxes()->attach([$tax1->id, $tax2->id]);

        $productB                     = new Product;
        $productB->name               = 'Another Product';
        $productB->price              = 100;
        $productB->stock              = 10;
        $productB->weight             = 800;
        $productB->price_includes_tax = true;
        $productB->save();
        $productB->taxes()->attach([$tax1->id, $tax2->id]);

        $sizeA             = new CustomFieldOption();
        $sizeA->name       = 'Size A';
        $sizeA->price      = 100;
        $sizeA->sort_order = 1;
        $sizeB             = new CustomFieldOption();
        $sizeB->name       = 'Size B';
        $sizeB->price      = 200;
        $sizeB->sort_order = 1;

        $field       = new CustomField();
        $field->name = 'Size';
        $field->type = 'dropdown';
        $field->save();

        $field->custom_field_options()->save($sizeA);
        $field->custom_field_options()->save($sizeB);

        $productA->custom_fields()->attach($field);

        $customFieldValueA                         = new CustomFieldValue();
        $customFieldValueA->custom_field_id        = $field->id;
        $customFieldValueA->custom_field_option_id = $sizeA->id;

        $customFieldValueB                         = new CustomFieldValue();
        $customFieldValueB->custom_field_id        = $field->id;
        $customFieldValueB->custom_field_option_id = $sizeB->id;

        $cart = new Cart();
        $cart->addProduct($productA, 2, null, collect([$customFieldValueA]));
        $cart->addProduct($productA, 1, null, collect([$customFieldValueB]));
        $cart->addProduct($productB, 2);

        $shippingMethod        = ShippingMethod::first();
        $shippingMethod->price = 100;
        $shippingMethod->save();

        $shippingMethod->taxes()->attach([$tax1->id, $tax2->id]);

        $cart->setShippingMethod($shippingMethod);

        $cart->setCustomer(Customer::first());

        $cart->setBillingAddress(Address::find(1));
        $cart->setShippingAddress(Address::find(2));

        return $cart;
    }

    protected function getSimpleCart(): Cart
    {
        $cart = new Cart();
        $cart->setShippingMethod(ShippingMethod::first());
        $cart->setCustomer(Customer::first());
        $cart->setBillingAddress(Address::find(1));
        $cart->setShippingAddress(Address::find(2));

        return $cart;
    }
}
