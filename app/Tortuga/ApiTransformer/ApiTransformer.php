<?php

namespace Tortuga\ApiTransformer;

interface ApiTransformer
{
    /**
     * @param array $data
     * @return array
     */
    public function output(array $data): array;
}
