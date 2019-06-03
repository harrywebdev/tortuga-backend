<?php

namespace Tortuga\ValidationRules\Customer;

use Tortuga\ValidationRules\ValidationRules;

class MobileCustomerValidationRules implements ValidationRules
{

    /**
     * Returns array of field->rules for Laravel validator
     * @return array
     */
    public function get(): array
    {
        return [
            'name'   => 'required|string|max:191',
            'email'  => 'nullable|email|max:191',
            'mobile' => 'required|regex:/^[0-9 \+]{9,}$/i|max:191',
        ];
    }

    /**
     * @return array Field names
     */
    public function keys(): array
    {
        return ['name', 'email', 'mobile'];
    }
}