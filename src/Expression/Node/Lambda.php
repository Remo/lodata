<?php

namespace Flat3\Lodata\Expression\Node;

use Flat3\Lodata\Expression\Event\Lambda as LambdaEvent;
use Flat3\Lodata\Expression\Node;

class Lambda extends Node
{
    public function compute(): void
    {
        $this->expressionEvent(new LambdaEvent($this));
    }
}