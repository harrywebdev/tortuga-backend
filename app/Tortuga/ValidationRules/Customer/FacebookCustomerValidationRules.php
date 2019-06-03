<?php

namespace Tortuga\ValidationRules\Customer;

use Tortuga\ValidationRules\ValidationRules;

class FacebookCustomerValidationRules implements ValidationRules
{

    /**
     * Returns array of field->rules for Laravel validator
     * @return array
     */
    public function get(): array
    {
        return [
            'name'        => 'required|string|max:191',
            'email'       => 'required|email|max:191',
            'facebook_id' => 'required|string|max:191',
            'mobile'      => 'nullable|regex:/^[0-9 \+]{9,}$/i|max:191',
        ];
    }

    /**
     * @return array Field names
     */
    public function keys(): array
    {
        return ['name', 'email', 'faebook_id', 'mobile'];
    }
}