<?php

namespace Tortuga\Validation;

use Illuminate\Support\Arr;
use Tortuga\Api\InvalidAttributeException;
use Illuminate\Support\Facades\Validator as LaravelValidatorFacade;

class LaravelValidator implements Validator
{
    /**
     * @param array           $data
     * @param ValidationRules $validationRules
     * @return array
     * @throws InvalidAttributeException
     */
    public function validate(array $data, ValidationRules $validationRules): array
    {
        $validator = LaravelValidatorFacade::make($data, $validationRules->get());

        if ($validator->fails()) {
            foreach ($validator->errors()->toArray() as $attribute => $message) {
                throw new InvalidAttributeException($attribute, $message[0]);
            }
        }

        return Arr::only($data, $validationRules->keys());
    }
}
