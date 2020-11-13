<?php

namespace Flat3\Lodata\Expression\Node;

use Flat3\Lodata\Expression\Event\DeclaredPropertyEvent;
use Flat3\Lodata\Expression\Node;

/**
 * Declared Property
 * @package Flat3\Lodata\Expression\Node
 */
class DeclaredProperty extends Node
{
    public function compute(): void
    {
        $this->expressionEvent(new DeclaredPropertyEvent($this));
    }
}
