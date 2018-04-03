<?php

namespace OFFLINE\Mall\Classes\Customer;

use DB;
use Event;
use Flash;
use Illuminate\Validation\Rule;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Models\Address;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Customer;
use OFFLINE\Mall\Models\User;
use RainLab\User\Facades\Auth;
use RainLab\User\Models\UserGroup;
use Redirect;
use Validator;

class DefaultSignUpHandler implements SignUpHandler
{
    protected $asGuest;

    public function handle(array $data, bool $asGuest = false): ?User
    {
        $this->asGuest = $asGuest;

        return $this->signUp($data);
    }

    /**
     * @throws ValidationException
     */
    protected function signUp(array $data): ?User
    {
        if ($this->asGuest) {
            $data['password'] = $data['password_repeat'] = str_random(30);
        }

        $this->validate($data);

        Event::fire('mall.user.beforeSignup', [$this, $data]);

        $user = DB::transaction(function () use ($data) {

            $user = $this->createUser($data);

            $customer           = new Customer();
            $customer->name     = $data['name'];
            $customer->user_id  = $user->id;
            $customer->is_guest = $this->asGuest;
            $customer->save();

            $addressData = $this->transformAddressKeys($data, 'billing');

            $billing = new Address();
            $billing->fill($addressData);
            $billing->name        = $addressData['address_name'] ?? $data['name'];
            $billing->customer_id = $customer->id;
            $billing->save();
            $customer->default_billing_address_id = $billing->id;

            if ( ! empty($data['use_different_shipping'])) {
                $addressData = $this->transformAddressKeys($data, 'shipping');

                $shipping = new Address();
                $shipping->fill($addressData);
                $shipping->name        = $addressData['address_name'] ?? $data['name'];
                $shipping->customer_id = $customer->id;
                $shipping->save();
                $customer->default_shipping_address_id = $shipping->id;
            } else {
                $customer->default_shipping_address_id = $billing->id;
            }

            $customer->save();

            Cart::transferToCustomer($user->customer);

            return $user;
        });

        // To prevent multiple guest accounts with the same email address we edit
        // the email of all existing guest accounts registered to the same email.
        $this->renameExistingGuestAccounts($data, $user);

        Event::fire('mall.user.afterSignup', [$this, $data]);

        $credentials = [
            'login'    => array_get($data, 'email'),
            'password' => array_get($data, 'password'),
        ];

        return Auth::authenticate($credentials, true);
    }

    /**
     * @throws ValidationException
     */
    protected function validate(array $data)
    {
        $rules = self::rules();

        if ($this->asGuest) {
            unset($rules['password'], $rules['password_repeat']);
        }

        $messages = self::messages();

        $validation = Validator::make($data, $rules, $messages);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }
    }

    protected function createUser($data): User
    {
        $data = [
            'name'                  => $data['name'],
            'email'                 => $data['email'],
            'password'              => $data['password'],
            'password_confirmation' => $data['password_repeat'],
        ];

        $user = Auth::register($data, true);
        if ($this->asGuest && $user && $group = UserGroup::getGuestGroup()) {
            $user->groups()->add($group);
        }

        return $user;
    }

    protected function transformAddressKeys(array $data, string $type): array
    {
        $transformed = [];
        foreach ($data as $key => $value) {
            if (starts_with($key, $type)) {
                $newKey               = str_replace($type . '_', '', $key);
                $transformed[$newKey] = $value;
            }
        }

        return $transformed;
    }

    protected function renameExistingGuestAccounts(array $data, $user)
    {
        $newEmail = sprintf('%s_%s%s', $data['email'], 'old_', date('Y-m-d_His'));
        User::where('id', '<>', $user->id)
            ->where('email', $data['email'])
            ->whereHas('customer', function ($q) {
                $q->where('is_guest', 1);
            })
            ->update(['email' => $newEmail]);
    }

    public static function rules($forSignup = true): array
    {
        return [
            'name'                => 'required',
            'email'               => ['required', 'email', ($forSignup ? 'non_existing_user' : null)],
            'billing_lines'       => 'required',
            'billing_zip'         => 'required',
            'billing_city'        => 'required',
            'billing_country_id'  => 'required|exists:offline_mall_countries,id',
            'shipping_lines'      => 'required_if:use_different_shipping,1',
            'shipping_zip'        => 'required_if:use_different_shipping,1',
            'shipping_city'       => 'required_if:use_different_shipping,1',
            'shipping_country_id' => 'required_if:use_different_shipping,1|exists:offline_mall_countries,id',
            'password'            => 'required|min:8|max:255',
            'password_repeat'     => 'required|same:password',
        ];
    }

    public static function messages(): array
    {
        return [
            'email.required'          => trans('offline.mall::lang.components.signup.errors.email.required'),
            'email.email'             => trans('offline.mall::lang.components.signup.errors.email.email'),
            'email.unique'            => trans('offline.mall::lang.components.signup.errors.email.unique'),
            'email.non_existing_user' => trans('offline.mall::lang.components.signup.errors.email.non_existing_user'),

            'name.required'                => trans('offline.mall::lang.components.signup.errors.name.required'),
            'billing_lines.required'       => trans('offline.mall::lang.components.signup.errors.lines.required'),
            'billing_zip.required'         => trans('offline.mall::lang.components.signup.errors.zip.required'),
            'billing_city.required'        => trans('offline.mall::lang.components.signup.errors.city.required'),
            'billing_country_id.required'  => trans('offline.mall::lang.components.signup.errors.country_id.required'),
            'billing_country_id.exists'    => trans('offline.mall::lang.components.signup.errors.country_id.exists'),
            'shipping_lines.required'      => trans('offline.mall::lang.components.signup.errors.lines.required'),
            'shipping_zip.required'        => trans('offline.mall::lang.components.signup.errors.zip.required'),
            'shipping_city.required'       => trans('offline.mall::lang.components.signup.errors.city.required'),
            'shipping_country_id.required' => trans('offline.mall::lang.components.signup.errors.country_id.required'),
            'shipping_country_id.exists'   => trans('offline.mall::lang.components.signup.errors.country_id.exists'),

            'password.required' => trans('offline.mall::lang.components.signup.errors.password.required'),
            'password.min'      => trans('offline.mall::lang.components.signup.errors.password.min'),
            'password.max'      => trans('offline.mall::lang.components.signup.errors.password.max'),

            'password_repeat.required' => trans('offline.mall::lang.components.signup.errors.password_repeat.required'),
            'password_repeat.same'     => trans('offline.mall::lang.components.signup.errors.password_repeat.same'),
        ];
    }
}
