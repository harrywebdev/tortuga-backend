<?php

namespace Tortuga\Validation;

use Opis\JsonSchema\Loaders\File;
use Opis\JsonSchema\Validator as OpisValidator;

class JsonSchemaValidator
{
    /**
     * @var OpisValidator
     */
    private $validator;

    /**
     * JsonSchemaValidator constructor.
     */
    function __construct()
    {
        $loader          = new File('http://localhost/', [resource_path('schemas/'),]);
        $this->validator = new OpisValidator(null, $loader);
    }

    /**
     * @param object $data
     * @param string $schema
     * @return bool
     */
    public function validate(object $data, string $schema): bool
    {
        $result = $this->validator->uriValidation($data, $schema);

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
