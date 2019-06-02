<?php

namespace Tortuga\ValidationRules;

interface ValidationRules
{
    /**
     * Returns array of field->rules for Laravel validator
     * @return array
     */
    public function get(): array;
}