<?php

namespace Tortuga\Customer;

use App\Customer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Tortuga\Api\InvalidAttributeException;
use Tortuga\ValidationRules\Customer\EmailCustomerValidationRules;
use Tortuga\ValidationRules\Customer\MobileCustomerValidationRules;
use Tortuga\ValidationRules\ValidationRules;

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
                    'Registration Type must be one of following: "email", "mobile", "facebook"', !$registrationType);
        }
    }

    /**
     * @param array $customerData
     * @return Customer
     * @throws InvalidAttributeException
     * @throws \Exception
     */
    private function _registerCustomerViaEmail(array $customerData): Customer
    {
        $customerData = $this->_validateCustomerData($customerData, (new EmailCustomerValidationRules()));

        throw new \Exception('Registration via email is not supported at the moment');
    }

    /**
     * @param array $customerData
     * @return Customer
     */
    private function _registerCustomerViaMobile(array $customerData): Customer
    {
        $customerData = $this->_validateCustomerData($customerData, (new MobileCustomerValidationRules()));

        $customer = new Customer($customerData);
        $customer->save();

        return $customer;
    }

    private function _registerCustomerViaFacebook(array $customerData): Customer
    {
        $customer = new Customer($customerData);

        return $customer;
    }

    /**
     * @param array           $customerData
     * @param ValidationRules $validationRules
     * @return array
     * @throws InvalidAttributeException
     */
    private function _validateCustomerData(array $customerData, ValidationRules $validationRules): array
    {
        $validator = Validator::make($customerData, $validationRules->get());

        if ($validator->fails()) {
            foreach ($validator->errors()->toArray() as $attribute => $message) {
                throw new InvalidAttributeException($attribute, $message[0]);
            }
        }

        return Arr::only($customerData, $validationRules->keys());
    }
}