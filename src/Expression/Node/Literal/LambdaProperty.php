<?php

namespace Flat3\Lodata\Expression\Node\Literal;

use Flat3\Lodata\Expression\Node\Literal;
use Flat3\Lodata\Property;

/**
 * Lambda Property
 * @package Flat3\Lodata\Expression\Node\Literal
 */
class LambdaProperty extends Literal
{
    protected $property;

    public function setProperty(Property $property): self
    {
        $this->property = $property;

        return $this;
    }
}
