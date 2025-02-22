<?php

namespace Flat3\Lodata;

use Flat3\Lodata\Controller\Transaction;
use Flat3\Lodata\Exception\Protocol\InternalServerErrorException;
use Flat3\Lodata\Helper\ObjectArray;
use Flat3\Lodata\Helper\PropertyValue;
use Flat3\Lodata\Interfaces\IdentifierInterface;
use Flat3\Lodata\Transaction\NavigationRequest;

/**
 * Navigation Property
 * @link https://docs.oasis-open.org/odata/odata-csdl-xml/v4.01/odata-csdl-xml-v4.01.html#_Toc38530365
 * @package Flat3\Lodata
 */
class NavigationProperty extends Property
{
    const identifier = 'Edm.NavigationPropertyPath';

    /**
     * The partner property referring back to this property
     * @var self $partner
     * @internal
     */
    protected $partner;

    /**
     * The referential constraints attached to this property
     * @var ObjectArray $constraints
     * @internal
     */
    protected $constraints;

    /**
     * Whether the target of this navigation property refers to a collection
     * @var bool $collection
     * @internal
     */
    protected $collection = false;

    /**
     * Whether this navigation property can be used as an expand request
     * @var bool $expandable
     * @internal
     */
    protected $expandable = true;

    public function __construct($name, EntityType $entityType)
    {
        if (!$entityType->getKey()) {
            throw new InternalServerErrorException(
                'missing_entity_type_key',
                'The specified entity type must have a key defined'
            );
        }

        if ($name instanceof IdentifierInterface) {
            $name = $name->getName();
        }

        parent::__construct($name, $entityType);

        $this->constraints = new ObjectArray();
    }

    /**
     * Get whether this navigation property represents a collection
     * @return bool
     */
    public function isCollection(): bool
    {
        return $this->collection;
    }

    /**
     * Set whether this navigation property represents a collection
     * @param  bool  $collection
     * @return $this
     */
    public function setCollection(bool $collection): self
    {
        $this->collection = $collection;
        return $this;
    }

    /**
     * Get whether this property can be expanded
     * @return bool
     */
    public function isExpandable(): bool
    {
        return $this->expandable;
    }

    /**
     * Set whether this property can be expanded
     * @param  bool  $expandable
     * @return $this
     */
    public function setExpandable(bool $expandable): self
    {
        $this->expandable = $expandable;

        return $this;
    }

    /**
     * Get the partner navigation property of this property
     * @return $this|null
     */
    public function getPartner(): ?self
    {
        return $this->partner;
    }

    /**
     * Set the partner navigation property of this property
     * @param  NavigationProperty  $partner  Partner
     * @return $this
     */
    public function setPartner(self $partner): self
    {
        $this->partner = $partner;

        return $this;
    }

    /**
     * Add a referential constraint of this property
     * @param  ReferentialConstraint  $constraint  Referential constraint
     * @return $this
     */
    public function addConstraint(ReferentialConstraint $constraint): self
    {
        $this->constraints[] = $constraint;

        return $this;
    }

    /**
     * Get the referential constraints attached to this property
     * @return ObjectArray|ReferentialConstraint[] Referential constraints
     */
    public function getConstraints(): ObjectArray
    {
        return $this->constraints;
    }

    /**
     * Generate a property value from this property
     * @param  Transaction  $transaction  Related transaction
     * @param  NavigationRequest  $navigationRequest  Navigation request
     * @param  Entity  $entity  Entity this property is attached to
     * @return PropertyValue|null Property value
     */
    public function generatePropertyValue(
        Transaction $transaction,
        NavigationRequest $navigationRequest,
        Entity $entity
    ): ?PropertyValue {
        $expansionTransaction = clone $transaction;
        $expansionTransaction->setRequest($navigationRequest);

        $propertyValue = $entity->newPropertyValue();
        $propertyValue->setProperty($this);

        $binding = $entity->getEntitySet()->getBindingByNavigationProperty($this);
        $targetEntitySet = $binding->getTarget();

        $expansionSet = clone $targetEntitySet;
        $expansionSet->setTransaction($expansionTransaction);
        $expansionSet->setNavigationPropertyValue($propertyValue);

        if ($this->isCollection()) {
            $propertyValue->setValue($expansionSet);
        } else {
            $expansionSingular = $expansionSet->query()->current();
            $propertyValue->setValue($expansionSingular);
        }

        $entity->addProperty($propertyValue);

        return $propertyValue;
    }
}
