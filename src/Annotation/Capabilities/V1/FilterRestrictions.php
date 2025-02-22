<?php

namespace Flat3\Lodata\Annotation\Capabilities\V1;

use Flat3\Lodata\Annotation;
use Flat3\Lodata\Annotation\Capabilities\FilterRestrictionsType;
use Flat3\Lodata\Helper\PropertyValue;
use Flat3\Lodata\Type\Boolean;

/**
 * Filter Restrictions
 * @package Flat3\Lodata\Annotation\Capabilities\V1
 */
class FilterRestrictions extends Annotation
{
    protected $name = 'Org.OData.Capabilities.V1.FilterRestrictions';

    public function __construct()
    {
        $type = new FilterRestrictionsType();

        $value = new Annotation\Record();
        $value->setType($type);

        $value[] = (new PropertyValue())
            ->setProperty($type->getProperty(FilterRestrictionsType::Filterable))
            ->setValue(Boolean::factory(true));

        $this->value = $value;
    }

    public function setFilterable(bool $filterable): self
    {
        $this->value[FilterRestrictionsType::Filterable]->setValue(Boolean::factory($filterable));

        return $this;
    }
}