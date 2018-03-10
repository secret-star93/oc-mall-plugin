<?php namespace OFFLINE\Mall\Components;

use Cms\Classes\ComponentBase;
use October\Rain\Support\Facades\Flash;
use OFFLINE\Mall\Classes\Traits\HashIds;
use OFFLINE\Mall\Classes\Traits\SetVars;
use OFFLINE\Mall\Models\Address;
use OFFLINE\Mall\Models\Country;
use OFFLINE\Mall\Models\GeneralSettings;
use RainLab\User\Facades\Auth;
use OFFLINE\Mall\Models\Cart;

class AddressForm extends ComponentBase
{
    public $address;
    public $countries;
    public $setAddressAs;
    public $cart;

    use SetVars;
    use HashIds;

    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.addressForm.details.name',
            'description' => 'offline.mall::lang.components.addressForm.details.description',
        ];
    }

    public function defineProperties()
    {
        return [
            'address'  => [
                'type'  => 'dropdown',
                'title' => 'offline.mall::lang.components.addressForm.properties.address.title',
            ],
            'redirect' => [
                'type'  => 'dropdown',
                'title' => 'offline.mall::lang.components.addressForm.properties.redirect.title',
            ],
            'set'      => [
                'type'  => 'dropdown',
                'title' => 'offline.mall::lang.components.addressForm.properties.set.title',
            ],
        ];
    }

    public function getAddressOptions()
    {
        return Address::get()->pluck('name', 'id');
    }

    public function getRedirectOptions()
    {
        return [
            'checkout' => trans('offline.mall::lang.components.addressForm.redirects.checkout'),
        ];
    }

    public function getSetOptions()
    {
        return [
            null       => trans('offline.mall::lang.common.not_in_use'),
            'billing'  => trans('offline.mall::lang.components.addressForm.set.billing'),
            'shipping' => trans('offline.mall::lang.components.addressForm.set.shipping'),
        ];
    }

    public function onRun()
    {
        if ( ! $this->setData()) {
            return $this->controller->run('404');
        }
    }

    public function onSubmit()
    {
        $this->setData();
        $user = Auth::getUser();
        if ( ! $user) {
            return $this->controller->run('404');
        }

        $data  = post();
        $isNew = $this->property('address') === 'new';

        if ($isNew) {
            $this->address              = new Address();
            $this->address->customer_id = $user->customer->id;
        }

        $this->address->fill($data);
        $this->address->name = $data['address_name'];
        $this->address->save();

        if (in_array($this->setAddressAs, ['billing', 'shipping'])) {
            $this->cart->{$this->setAddressAs . '_address_id'} = $this->address->id;
            $this->cart->save();
        }

        if ($url = $this->getRedirectUrl()) {
            return redirect()->to(url($url));
        }
    }

    protected function setData()
    {
        $user = Auth::getUser();
        if ( ! $user) {
            return false;
        }

        $this->setVar('countries', Country::orderBy('name')->get());
        $this->setVar('setAddressAs', $this->property('set'));
        $this->setVar('cart', Cart::byUser(Auth::getUser()));

        $hashId = $this->property('address');
        if ($hashId === 'new') {
            return true;
        }

        $id = $this->decode($hashId);
        $this->setVar('address', Address::byCustomer($user->customer)->findOrFail($id));

        return true;
    }

    protected function getRedirectUrl()
    {
        $redirect = $this->property('redirect');
        $url      = '';
        if ($redirect === 'checkout') {
            $url = $this->controller->pageUrl(GeneralSettings::get('checkout_page'), ['step' => 'confirm']);
        }

        return $url;

    }
}
