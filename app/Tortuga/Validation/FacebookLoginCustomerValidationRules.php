<?php

namespace Tortuga\Validation;

class FacebookLoginCustomerValidationRules implements ValidationRules
{

    /**
     * Returns array of field->rules for Laravel validator
     * @return array
     */
    public function get(): array
    {
        return [
            'access_token' => 'required|string|max:512',
        ];
    }

    /**
     * @return array Field names
     */
    public function keys(): array
    {
        return array_keys($this->get());
    }
}