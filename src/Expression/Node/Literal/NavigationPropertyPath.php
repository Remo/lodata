<?php

namespace Flat3\Lodata\Expression\Node\Literal;

use Flat3\Lodata\Expression\Node\Literal;
use Flat3\Lodata\NavigationProperty;

/**
 * Navigation Property Path
 * @package Flat3\Lodata\Expression\Node\Literal
 */
class NavigationPropertyPath extends Literal
{
    protected $navigationProperty = null;

    public function setNavigationProperty(NavigationProperty $property): self
    {
        $this->navigationProperty = $property;

        return $this;
    }

    public function getValue(): NavigationProperty
    {
        return $this->navigationProperty;
    }
}
