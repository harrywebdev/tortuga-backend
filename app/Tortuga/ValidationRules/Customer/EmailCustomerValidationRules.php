<?php

namespace Tortuga\ValidationRules\Customer;

use Tortuga\ValidationRules\ValidationRules;

class EmailCustomerValidationRules implements ValidationRules
{

    /**
     * Returns array of field->rules for Laravel validator
     * @return array
     */
    public function get(): array
    {
        return [
            'name'   => 'required|string|max:191',
            'email'  => 'required|email|max:191',
            'mobile' => 'nullable|regex:/^[0-9 \+]{9,}$/i|max:191',
        ];
    }
}