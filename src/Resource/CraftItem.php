<?php

namespace RestfulApi\Resource;

use League\Fractal\Resource\Item;

class CraftItem extends Item
{
    public function __construct($data, $transformer, $resourceKey = null)
    {
        // $data = [$data];

        parent::__construct($data, $transformer, $resourceKey);
    }
}
