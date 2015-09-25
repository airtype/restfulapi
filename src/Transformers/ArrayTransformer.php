<?php

namespace RestApi\Transformers;

class ArrayTransformer extends BaseTransformer
{
    public function transform(array $array)
    {
        return $array;
    }
}
