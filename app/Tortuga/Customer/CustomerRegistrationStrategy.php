<?php

namespace Tortuga\Customer;

use App\Customer;
use Illuminate\Support\Facades\Validator;
use Tortuga\Api\InvalidAttributeException;
use Tortuga\ValidationRules\Customer\EmailCustomerValidationRules;

class CustomerRegistrationStrategy
{
    public function registerCustomer(string $registrationType, array $customerData): Customer
    {
        switch ($registrationType) {
            case 'email':
                return $this->_registerCustomerViaEmail($customerData);
            case 'mobile':
                return $this->_registerCustomerViaMobile($customerData);
            case 'facebook':
                return $this->_registerCustomerViaFacebook($customerData);
            default:
                throw new InvalidAttributeException('reg_type',
                    'Registraton Type must be one of following: "email", "mobile", "facebook"', !$registrationType);
        }
    }

    private function _registerCustomerViaEmail(array $customerData): Customer
    {
        $validator = Validator::make($customerData, (new EmailCustomerValidationRules())->get());

        if ($validator->fails()) {
            foreach ($validator->errors()->toArray() as $attribute => $message) {
                throw new InvalidAttributeException($attribute, $message[0]);
            }
        }

        $customer = new Customer($customerData);

        return $customer;
    }

    private function _registerCustomerViaMobile(array $customerData): Customer
    {
        $customer = new Customer($customerData);

        return $customer;
    }

    private function _registerCustomerViaFacebook(array $customerData): Customer
    {
        $customer = new Customer($customerData);

        return $customer;
    }
}