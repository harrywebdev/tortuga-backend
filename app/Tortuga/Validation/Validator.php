<?php

namespace Tortuga\Validation;

interface Validator
{
    /**
     * @param array           $data
     * @param ValidationRules $validationRules
     * @return array
     */
    public function validate(array $data, ValidationRules $validationRules): array;
}
