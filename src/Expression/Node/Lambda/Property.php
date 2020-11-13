<?php

namespace Flat3\Lodata\Expression\Node\Lambda;

use Flat3\Lodata\Expression\Node\Lambda;
use Flat3\Lodata\Property as ModelProperty;

/**
 * Property
 * @package Flat3\Lodata\Expression\Node\Literal
 */
class Property extends Lambda
{
    protected $property;

    protected $argument;

    public function setProperty(ModelProperty $property): self
    {
        $this->property = $property;

        return $this;
    }

    public function getProperty(): ModelProperty
    {
        return $this->property;
    }

    public function setArgument(Argument $argument): self
    {
        $this->argument = $argument;

        return $this;
    }

    public function getArgument(): Argument
    {
        return $this->argument;
    }
}
