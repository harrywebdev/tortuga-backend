<?php

namespace Tortuga\Customer;

use App\Customer;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Tortuga\Api\AccountKitException;
use Tortuga\Api\InvalidAttributeException;
use Tortuga\ValidationRules\AccountKitCustomerValidationRules;
use Tortuga\ValidationRules\ValidationRules;
use Tayokin\FacebookAccountKit\Facades\FacebookAccountKitFacade;

class CustomerRegistrationStrategy
{
    public function registerCustomer(string $registrationType, array $customerData): Customer
    {
        switch ($registrationType) {
            case 'email':
                return $this->_registerCustomerViaEmail($customerData);
            case 'mobile':
                return $this->_registerCustomerViaMobile($customerData);
            default:
                throw new InvalidAttributeException('reg_type',
                    'Registration Type must be one of following: "email", "mobile"', !$registrationType);
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
        $customerData = $this->_validateCustomerData($customerData, (new AccountKitCustomerValidationRules()));

        throw new \Exception('Registration via email is not supported at the moment');
    }

    /**
     * @param array $customerData
     * @return Customer
     */
    private function _registerCustomerViaMobile(array $customerData): Customer
    {
        $customerData = $this->_validateCustomerData($customerData, (new AccountKitCustomerValidationRules()));

        try {
            $accountData = FacebookAccountKitFacade::getAccountDataByCode($customerData['code']);

            $customer                         = new Customer();
            $customer->name                   = $customerData['name'];
            $customer->mobile_number          = $accountData->phone->number;
            $customer->mobile_country_prefix  = $accountData->phone->country_prefix;
            $customer->mobile_national_number = $accountData->phone->national_number;
            $customer->account_kit_id         = $accountData->id;

            $customer->save();

            return $customer;
        } catch (ClientException $e) {
            $responseContents = json_decode($e->getResponse()->getBody()->getContents());
            if (isset($responseContents->error) && isset($responseContents->error->message)) {
                throw new AccountKitException($responseContents->error->message);
            }

            throw new \Exception($e->getMessage());
        }
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