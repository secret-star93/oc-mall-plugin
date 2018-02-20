<?php

namespace OFFLINE\Mall\Classes\Payments;

use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\PaymentMethod;
use Session;
use Symfony\Component\DependencyInjection\Exception\LogicException;

class DefaultPaymentGateway implements PaymentGateway
{
    public $provider;
    public $providers = [];

    public function registerProvider(PaymentProvider $provider)
    {
        $this->providers[$provider->identifier()] = get_class($provider);
    }

    public function getProviderById(string $identifier): PaymentProvider
    {
        if ( ! isset($this->providers[$identifier])) {
            throw new \InvalidArgumentException(sprintf('Payment provider %s is not registered.', $identifier));
        }

        return $this->providers[$identifier];
    }

    public function init(Cart $cart, array $data)
    {
        $this->provider = $this->getProviderForMethod(PaymentMethod::findOrFail($cart->payment_method_id));
        $this->provider->setData($data);
        $this->provider->validate();
    }

    public function process(Order $order): PaymentResult
    {
        if ( ! $this->provider) {
            throw new \LogicException('Missing data for payment. Make sure to call init() before process()');
        }

        Session::put('oc-mall.payment.id', str_random(8));

        $this->provider->setOrder($order);

        return $this->provider->process();
    }

    protected function getProviderForMethod(PaymentMethod $method): PaymentProvider
    {
        if (isset($this->providers[$method->payment_provider])) {
            return new $this->providers[$method->payment_provider];
        }

        throw new \LogicException(
            sprintf('The selected payment provider "%s" is unavailable.', $method->payment_provider)
        );
    }

    public function getProviders(): array
    {
        return $this->providers;
    }
}
