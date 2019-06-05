<?php

namespace Tortuga\Validation;

class AccountKitCustomerUpdateValidationRules implements ValidationRules
{

    /**
     * Returns array of field->rules for Laravel validator
     * @return array
     */
    public function get(): array
    {
        return [
            'name'  => 'required|string|max:512',
            'email' => 'nullable|email|max:191',
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