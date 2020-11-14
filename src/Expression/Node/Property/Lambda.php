<?php

namespace Flat3\Lodata\Expression\Node\Property;

use Flat3\Lodata\Expression\Node\Literal\LambdaArgument;
use Flat3\Lodata\Expression\Node\Property;

/**
 * Lambda
 * @package Flat3\Lodata\Expression\Node\Property
 */
class Lambda extends Property
{
    protected $argument;

    public function setArgument(LambdaArgument $argument): self
    {
        $this->argument = $argument;

        return $this;
    }

    public function getArgument(): LambdaArgument
    {
        return $this->argument;
    }
}
