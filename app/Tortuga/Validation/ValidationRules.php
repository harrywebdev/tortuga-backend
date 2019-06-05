<?php

namespace Tortuga\Validation;

interface ValidationRules
{
    /**
     * Returns array of field->rules for Laravel validator
     * @return array
     */
    public function get(): array;

    /**
     * @return array Field names
     */
    public function keys(): array;
}