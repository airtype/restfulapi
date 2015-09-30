<?php

namespace RestfulApi\Validators;

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
