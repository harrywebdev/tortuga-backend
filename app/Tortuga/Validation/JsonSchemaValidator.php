<?php

namespace Tortuga\Validation;

use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Validator as OpisValidator;
use Tortuga\Api\InvalidDataException;

class JsonSchemaValidator
{

    /**
     * @param object $data
     * @param string $schema
     * @return bool
     */
    public function validate(object $data, string $schema): bool
    {
        $schema = Schema::fromJsonString($schema);

        $validator = new OpisValidator();

        $result = $validator->schemaValidation($data, $schema);

        if ($result->isValid()) {
            return true;
        } else {
            $error = $result->getFirstError();
            throw new InvalidDataException(
                $error->keyword() . ' ' . json_encode($error->keywordArgs()),
                '/' . implode("/", $error->dataPointer())
            );
        }
    }
}
