<?php

namespace Tortuga\Customer;

use App\Customer;
use App\Events\CustomerRegistered;
use App\Events\CustomerUpdated;
use Facebook\FacebookResponse;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;
use SammyK\LaravelFacebookSdk\FacebookFacade as Facebook;
use Tayokin\FacebookAccountKit\Facades\FacebookAccountKitFacade;
use Tortuga\Validation\AccountKitException;
use Tortuga\Validation\JsonSchemaValidator;

class CustomerRegistrationStrategy
{
    /**
     * @var JsonSchemaValidator
     */
    private $validator;

    /**
     * CustomerRegistrationStrategy constructor.
     * @param JsonSchemaValidator $validator
     */
    function __construct(JsonSchemaValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param object $customerData
     * @return Customer
     * @throws \Exception
     */
    public function registerCustomer(object $customerData): Customer
    {
        $this->validator->validate(
            $customerData,
            'http://localhost/create_customer.json'
        );

        switch ($customerData->data->attributes->reg_type) {
            case 'mobile':
                return $this->_registerCustomerViaMobile($customerData->data->attributes);
            case 'facebook':
                return $this->_registerCustomerViaFacebook($customerData->data->attributes);
        }

        throw new \Exception('Create Customer Validation fail: unsupported `reg_type`');
    }

    /**
     * @param object $customerData
     * @return Customer
     */
    private function _registerCustomerViaMobile(object $customerData): Customer
    {
        try {
            $accountData = FacebookAccountKitFacade::getAccountDataByCode($customerData->code);

            $customer = Customer::where('account_kit_id', '=', $accountData->id)
                ->where('reg_type', '=', 'mobile')
                ->first();

            if ($customer) {
                // update name if exists
                if ($customerData->name && $customer->name !== $customerData->name) {
                    $oldCustomer = (object)$customer->toArray();

                    $customer->name = $customerData->name;
                    $customer->save();

                    event(new CustomerUpdated($customer, $oldCustomer));
                }
                return $customer;
            }

            $customer                         = new Customer();
            $customer->reg_type               = 'mobile';
            $customer->mobile_number          = $accountData->phone->number;
            $customer->mobile_country_prefix  = $accountData->phone->country_prefix;
            $customer->mobile_national_number = $accountData->phone->national_number;
            $customer->account_kit_id         = $accountData->id;

            if ($customerData->name) {
                $customer->name = $customerData->name;
            }

            $customer->save();

            event(new CustomerRegistered($customer));

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
     * @param object $customerData
     * @return Customer
     */
    private function _registerCustomerViaFacebook(object $customerData): Customer
    {
        try {
            /** @var FacebookResponse $response */
            $response = Facebook::get('/me?fields=id,name,email,link', $customerData->code);
            $userNode = $response->getGraphUser();

            $customer = Customer::where('facebook_id', '=', $userNode->getId())
                ->where('reg_type', '=', 'facebook')
                ->first();

            if ($customer) {
                return $customer;
            }

            $customer               = new Customer();
            $customer->reg_type     = 'facebook';
            $customer->name         = $userNode->getName();
            $customer->email        = $userNode->getEmail();
            $customer->facebook_id  = $userNode->getId();
            $customer->facebook_url = $userNode->getLink();

            $customer->save();

            return $customer;
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
