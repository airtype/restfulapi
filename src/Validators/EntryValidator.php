<?php

namespace RestApi\Validators;

class EntryValidator extends AbstractValidator
{
    /**
     * Rules
     *
     * @var array
     */
    public $rules = [
        ['sectionId', 'required'],
    ];
}
