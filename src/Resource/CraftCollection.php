<?php

namespace RestfulApi\Resource;

use League\Fractal\Resource\Collection;

class CraftCollection extends Collection
{
    public function __construct($data, $transformer, $resourceKey = null)
    {
        foreach ($data as $key => $value) {
            $data[$key] = $value;
        }

        parent::__construct($data, $transformer, $resourceKey);
    }
}
