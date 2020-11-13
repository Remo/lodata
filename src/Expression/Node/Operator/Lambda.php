<?php

namespace Flat3\Lodata\Expression\Node\Operator;

use Flat3\Lodata\Exception\Internal\NodeHandledException;
use Flat3\Lodata\Expression\Event\EndGroup;
use Flat3\Lodata\Expression\Event\StartGroup;
use Flat3\Lodata\Expression\Node\Lambda\Argument;
use Flat3\Lodata\Expression\Node\NavigationPropertyPath;
use Flat3\Lodata\Expression\Operator;

/**
 * Lambda
 * @package Flat3\Lodata\Expression\Node\Operator
 */
abstract class Lambda extends Operator
{
    const unary = true;

    protected $navigationPath;

    protected $lambdaArgument;

    public function getNavigationPath(): ?NavigationPropertyPath
    {
        return $this->navigationPath;
    }

    public function setNavigationPath(NavigationPropertyPath $path): self
    {
        $this->navigationPath = $path;

        return $this;
    }

    public function getLambdaArgument(): ?Argument
    {
        return $this->lambdaArgument;
    }

    public function setLambdaArgument(Argument $argument): self
    {
        $this->lambdaArgument = $argument;

        return $this;
    }

    public function compute(): void
    {
        try {
            $this->expressionEvent(new \Flat3\Lodata\Expression\Event\Operator($this));
            $this->expressionEvent(new StartGroup());
            $this->computeCommaSeparatedArguments();
            $this->expressionEvent(new EndGroup());
        } catch (NodeHandledException $e) {
            return;
        }
    }
}
