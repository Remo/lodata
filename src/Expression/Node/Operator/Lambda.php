<?php

namespace Flat3\Lodata\Expression\Node\Operator;

use Flat3\Lodata\Exception\Internal\NodeHandledException;
use Flat3\Lodata\Expression\Event\EndGroup;
use Flat3\Lodata\Expression\Event\Operator as OperatorEvent;
use Flat3\Lodata\Expression\Event\StartGroup;
use Flat3\Lodata\Expression\Node\Literal\LambdaVariable;
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
     * @var LambdaVariable $lambdaVariable
     * @internal
     */
    protected $lambdaVariable;

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
     * @return LambdaVariable
     */
    public function getLambdaVariable(): LambdaVariable
    {
        return $this->lambdaVariable;
    }

    /**
     * Set the lambda argument
     * @param  LambdaVariable  $argument
     * @return $this
     */
    public function setLambdaVariable(LambdaVariable $argument): self
    {
        $this->lambdaVariable = $argument;

        return $this;
    }

    public function compute(): void
    {
        try {
            $this->expressionEvent(new OperatorEvent($this));
            $this->expressionEvent(new StartGroup());
            $arguments = $this->getArguments();
            $arg = array_shift($arguments);
            $arg->compute();
            $this->expressionEvent(new EndGroup());
        } catch (NodeHandledException $e) {
            return;
        }
    }
}
