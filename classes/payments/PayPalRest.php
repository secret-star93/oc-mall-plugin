<?php

namespace OFFLINE\Mall\Classes\Payments;

use OFFLINE\Mall\Classes\PaymentState\FailedState;
use OFFLINE\Mall\Classes\PaymentState\PaidState;
use OFFLINE\Mall\Models\PaymentGatewaySettings;
use Omnipay\Omnipay;
use Request;
use Session;
use Validator;

class PayPalRest extends PaymentProvider
{
    public function name(): string
    {
        return 'PayPal Rest API';
    }

    public function identifier(): string
    {
        return 'paypal-rest';
    }

    public function validate(): bool
    {
        return true;
    }

    public function process()
    {
        $gateway = $this->getGateway();

        $result   = new PaymentResult();
        $response = null;
        try {
            $response = $gateway->purchase([
                'amount'    => round((int)$this->order->getOriginal('total_post_taxes') / 100, 2),
                'currency'  => $this->order->currency,
                'returnUrl' => $this->returnUrl(),
                'cancelUrl' => $this->cancelUrl(),
            ])->send();
        } catch (\Throwable $e) {
            $result->successful    = false;
            $result->failedPayment = $this->logFailedPayment([], $e);

            return $result;
        }

        // PayPal has to return a RedirectResponse if everything went well
        if ($response->isRedirect()) {
            Session::put('oc-mall.payment.callback', self::class);
            Session::put('oc-mall.paypal.transactionReference', $response->getTransactionReference());
            $result->redirect    = true;
            $result->redirectUrl = $response->getRedirectResponse()->getTargetUrl();

            return $result;
        }

        $data                  = (array)$response->getData();
        $result->failedPayment = $this->logFailedPayment($data, $response);

        return $result;
    }

    public function complete(): PaymentResult
    {
        $result  = new PaymentResult();
        $key     = Session::pull('oc-mall.paypal.transactionReference');
        $payerId = Request::input('PayerID');

        if ( ! $key || ! $payerId) {
            info('Missing payment data', ['key' => $key, 'payer' => $payerId]);
            $result->successful = false;

            return $result;
        }

        $this->setOrder($this->getOrderFromSession());

        try {
            $response = $this->getGateway()->completePurchase([
                'transactionReference' => $key,
                'payerId'              => $payerId,
            ])->send();
        } catch (\Throwable $e) {
            $result->successful    = false;
            $result->failedPayment = $this->logFailedPayment([], $e);

            return $result;
        }

        $data = (array)$response->getData();

        $result->successful = $response->isSuccessful();

        if ($result->successful) {
            $payment                    = $this->logSuccessfulPayment($data, $response);
            $this->order->payment_id    = $payment->id;
            $this->order->payment_data  = $data;
            $this->order->payment_state = PaidState::class;
            $this->order->save();
        } else {
            $result->failedPayment      = $this->logFailedPayment($data, $response);
            $this->order->payment_state = FailedState::class;
            $this->order->save();
        }

        return $result;
    }

    protected function getGateway()
    {
        $gateway = Omnipay::create('PayPal_Rest');
        $gateway->initialize([
            'clientId' => decrypt(PaymentGatewaySettings::get('paypal_client_id')),
            'secret'   => decrypt(PaymentGatewaySettings::get('paypal_secret')),
            'testMode' => (bool)PaymentGatewaySettings::get('paypal_test_mode'),
        ]);

        return $gateway;
    }
}
