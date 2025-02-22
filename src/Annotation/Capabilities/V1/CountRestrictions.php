<?php

namespace Flat3\Lodata\Annotation\Capabilities\V1;

use Flat3\Lodata\Annotation;
use Flat3\Lodata\Annotation\Capabilities\CountRestrictionsType;
use Flat3\Lodata\Helper\PropertyValue;
use Flat3\Lodata\Type\Boolean;

/**
 * Count Restrictions
 * @package Flat3\Lodata\Annotation\Capabilities\V1
 */
class CountRestrictions extends Annotation
{
    protected $name = 'Org.OData.Capabilities.V1.CountRestrictions';

    public function __construct()
    {
        $type = new CountRestrictionsType();

        $value = new Annotation\Record();
        $value->setType($type);

        $value[] = (new PropertyValue())
            ->setProperty($type->getProperty(CountRestrictionsType::Countable))
            ->setValue(Boolean::factory(true));

        $this->value = $value;
    }

    public function setCountable(bool $countable): self
    {
        $this->value[CountRestrictionsType::Countable]->setValue(Boolean::factory($countable));

        return $this;
    }
}