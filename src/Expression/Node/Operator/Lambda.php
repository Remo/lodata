<?php

namespace Flat3\Lodata\Expression\Node\Operator;

use Flat3\Lodata\Exception\Internal\NodeHandledException;
use Flat3\Lodata\Expression\Event\EndGroup;
use Flat3\Lodata\Expression\Event\Operator as OperatorEvent;
use Flat3\Lodata\Expression\Event\StartGroup;
use Flat3\Lodata\Expression\Node\Literal\LambdaArgument;
use Flat3\Lodata\Expression\Node\Property\Navigation;
use Flat3\Lodata\Expression\Operator;

/**
 * Lambda
 * @package Flat3\Lodata\Expression\Node\Operator
 */
abstract class Lambda extends Operator
{
    const unary = true;

    /**
     * @var Navigation $navigationProperty
     * @internal
     */
    protected $navigationProperty;

    /**
     * @var LambdaArgument $lambdaArgument
     * @internal
     */
    protected $lambdaArgument;

    /**
     * Get the navigation property
     * @return Navigation
     */
    public function getNavigationProperty(): Navigation
    {
        return $this->navigationProperty;
    }

    /**
     * Set the navigation property
     * @param  Navigation  $property
     * @return $this
     */
    public function setNavigationProperty(Navigation $property): self
    {
        $this->navigationProperty = $property;

        return $this;
    }

    /**
     * Get the lambda argument
     * @return LambdaArgument
     */
    public function getLambdaArgument(): LambdaArgument
    {
        return $this->lambdaArgument;
    }

    /**
     * Set the lambda argument
     * @param  LambdaArgument  $argument
     * @return $this
     */
    public function setLambdaArgument(LambdaArgument $argument): self
    {
        $this->lambdaArgument = $argument;

        return $this;
    }

    public function compute(): void
    {
        try {
            $this->expressionEvent(new OperatorEvent($this));
            $this->expressionEvent(new StartGroup());
            $this->computeCommaSeparatedArguments();
            $this->expressionEvent(new EndGroup());
        } catch (NodeHandledException $e) {
            return;
        }
    }
}
