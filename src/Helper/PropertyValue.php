<?php

namespace Flat3\Lodata\Helper;

use Flat3\Lodata\Controller\Response;
use Flat3\Lodata\Controller\Transaction;
use Flat3\Lodata\Entity;
use Flat3\Lodata\EntitySet;
use Flat3\Lodata\Exception\Internal\LexerException;
use Flat3\Lodata\Exception\Internal\PathNotHandledException;
use Flat3\Lodata\Exception\Protocol\BadRequestException;
use Flat3\Lodata\Exception\Protocol\MethodNotAllowedException;
use Flat3\Lodata\Exception\Protocol\NoContentException;
use Flat3\Lodata\Exception\Protocol\NotFoundException;
use Flat3\Lodata\Expression\Lexer;
use Flat3\Lodata\GeneratedProperty;
use Flat3\Lodata\Interfaces\ContextInterface;
use Flat3\Lodata\Interfaces\EntitySet\UpdateInterface;
use Flat3\Lodata\Interfaces\JsonInterface;
use Flat3\Lodata\Interfaces\PipeInterface;
use Flat3\Lodata\Interfaces\ResourceInterface;
use Flat3\Lodata\NavigationProperty;
use Flat3\Lodata\Primitive;
use Flat3\Lodata\Property;
use Flat3\Lodata\Transaction\MetadataContainer;
use Flat3\Lodata\Transaction\NavigationRequest;
use Flat3\Lodata\Type\Stream;
use Illuminate\Http\Request;

/**
 * Property Value
 * @package Flat3\Lodata\Helper
 */
class PropertyValue implements ContextInterface, PipeInterface, JsonInterface, ResourceInterface
{
    /**
     * The entity that contains this property value
     * @var Entity $entity Entity
     * @internal
     */
    protected $entity;

    /**
     * The entity type property
     * @var Property $property Property
     * @internal
     */
    protected $property;

    /**
     * The value of this property
     * @var mixed $value Value
     * @internal
     */
    protected $value;

    /**
     * Set the property
     * @param  Property  $property  Property
     * @return $this
     */
    public function setProperty(Property $property): self
    {
        $this->property = $property;
        return $this;
    }

    /**
     * Get the property
     * @return Property Property
     */
    public function getProperty(): Property
    {
        return $this->property;
    }

    /**
     * Set the attached entity
     * @param  Entity  $entity  Entity
     * @return $this
     */
    public function setEntity(Entity $entity): self
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * Get the attached entity
     * @return Entity Entity
     */
    public function getEntity(): Entity
    {
        return $this->entity;
    }

    /**
     * Set the attached value
     * @param  JsonInterface|null  $value  Value
     * @return $this
     */
    public function setValue(?JsonInterface $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Get the attached value
     * @return JsonInterface|null Value
     */
    public function getValue(): ?JsonInterface
    {
        return $this->value;
    }

    /**
     * Get the attached primitive value
     * @return Primitive Value
     */
    public function getPrimitiveValue(): Primitive
    {
        return $this->value;
    }

    /**
     * Get the attached entity set value
     * @return EntitySet Value
     */
    public function getEntitySetValue(): EntitySet
    {
        return $this->value;
    }

    /**
     * Get the attached entity value
     * @return Entity Value
     */
    public function getEntityValue(): Entity
    {
        return $this->value;
    }

    /**
     * @return string
     * @internal
     */
    public function __toString()
    {
        return (string) $this->property;
    }

    /**
     * Whether this property value should be emitted based on the provided transaction
     * @param  Transaction  $transaction  Transaction
     * @return bool
     */
    public function shouldEmit(Transaction $transaction): bool
    {
        if ($this->value instanceof Primitive) {
            $omitNulls = $transaction->getPreferenceValue(Constants::OMIT_VALUES) === Constants::NULLS;

            if ($omitNulls && $this->value->get() === null && $this->property->isNullable()) {
                return false;
            }
        }

        if ($this->property instanceof NavigationProperty) {
            return true;
        }

        $select = $transaction->getSelect();
        $selected = $select->getCommaSeparatedValues();

        if (
            $this->getProperty()->getPrimitiveType()->is(Stream::class) &&
            !in_array($this->property->getName(), $selected)
        ) {
            return false;
        }

        if (($select->isStar() || !$select->hasValue())) {
            return true;
        }

        if ($selected && !in_array($this->property->getName(), $selected)) {
            return false;
        }

        return true;
    }

    /**
     * Get the context URL for this property value
     * @param  Transaction  $transaction  Transaction
     * @return string Context URL
     */
    public function getContextUrl(Transaction $transaction): string
    {
        $entity = $this->entity;

        if (!$entity->getEntitySet()) {
            return $entity->getContextUrl($transaction);
        }

        /** @var Primitive $value */
        $value = $entity->getEntityId()->getValue();

        $url = sprintf(
            '%s(%s)/%s',
            $transaction->getContextUrl().'#'.$entity->getEntitySet()->getName(),
            $value->toUrl(),
            $this->property
        );

        $properties = $transaction->getContextUrlProperties();

        if ($properties) {
            $url .= sprintf('(%s)', join(',', $properties));
        }

        return $url;
    }

    /**
     * Get the resource URL for this property value
     * @param  Transaction  $transaction  Transaction
     * @return string Context URL
     */
    public function getResourceUrl(Transaction $transaction): string
    {
        return sprintf("%s/%s", $this->entity->getResourceUrl($transaction), $this->property->getName());
    }

    public static function pipe(
        Transaction $transaction,
        string $currentSegment,
        ?string $nextSegment,
        ?PipeInterface $argument
    ): ?PipeInterface {
        $lexer = new Lexer($currentSegment);

        try {
            $propertyName = $lexer->identifier();
        } catch (LexerException $e) {
            throw new PathNotHandledException();
        }

        if (null === $argument) {
            throw new PathNotHandledException();
        }

        if ($argument instanceof self && $argument->getValue() instanceof Entity) {
            $argument = $argument->getEntityValue();
        }

        if (!$argument instanceof Entity) {
            throw new PathNotHandledException();
        }

        $property = $argument->getType()->getProperty($propertyName);

        if (null === $property) {
            throw new NotFoundException(
                'unknown_property',
                sprintf('The requested property (%s) was not known', $propertyName)
            );
        }

        if ($property instanceof NavigationProperty) {
            $navigationRequest = new NavigationRequest();
            $navigationRequest->setOuterRequest($transaction->getRequest());
            $navigationRequest->setNavigationProperty($property);
            $property->generatePropertyValue($transaction, $navigationRequest, $argument);
        }

        if ($property instanceof GeneratedProperty) {
            $property->generatePropertyValue($argument);
        }

        return $argument->getPropertyValues()->get($property);
    }

    public function emitJson(Transaction $transaction): void
    {
        $this->value->emitJson($transaction);
    }

    public function response(Transaction $transaction, ?ContextInterface $context = null): Response
    {
        $value = $this->value;

        if ($value instanceof Entity || $value instanceof EntitySet) {
            return $value->response($transaction, $this);
        }

        switch ($transaction->getMethod()) {
            case Request::METHOD_GET:
                return $this->get($transaction, $context);

            case Request::METHOD_DELETE:
                return $this->delete($transaction, $context);
        }

        throw new MethodNotAllowedException();
    }

    public function delete(Transaction $transaction, ?ContextInterface $context = null): Response
    {
        $entitySet = $this->entity->getEntitySet();

        if (!$entitySet instanceof UpdateInterface) {
            throw new BadRequestException(
                'entity_set_not_updatable',
                'The entity set for this entity does not support updates'
            );
        }

        if (!$this->getProperty()->isNullable()) {
            throw new BadRequestException('property_not_nullable', 'This property cannot be set to null');
        }

        Gate::check(Gate::DELETE, $this, $transaction);

        $transaction->getRequest()->setContent([
            $this->getProperty()->getName() => null,
        ]);
        $entitySet->update($this->entity->getEntityId());

        throw new NoContentException();
    }

    public function get(Transaction $transaction, ?ContextInterface $context = null): Response
    {
        Gate::check(Gate::READ, $this, $transaction);

        $value = $this->value;
        $context = $context ?: $this;

        if ($value instanceof Primitive && null === $value->get() || $value === null) {
            throw new NoContentException('null_value');
        }

        $metadata = $transaction->createMetadataContainer();

        $metadata['context'] = $context->getContextUrl($transaction);

        return $transaction->getResponse()->setResourceCallback($this, function () use ($transaction, $metadata) {
            $transaction->outputJsonObjectStart();

            if ($metadata->hasProperties()) {
                $transaction->outputJsonKV($metadata->getProperties());
                $transaction->outputJsonSeparator();
            }

            $transaction->outputJsonKey('value');
            $this->emitJson($transaction);

            $transaction->outputJsonObjectEnd();
        });
    }

    /**
     * Get the metadata for this property value
     * @param  Transaction  $transaction  Related transaction
     * @return MetadataContainer Metadata container
     */
    public function getMetadata(Transaction $transaction): MetadataContainer
    {
        $metadata = $transaction->createMetadataContainer();
        $metadata->setPrefix($this->getProperty()->getName());

        $property = $this->property;

        switch (true) {
            case $property instanceof NavigationProperty:
                $metadata['navigationLink'] = $this->entity->getResourceUrl($transaction).'/'.$this->getProperty()->getName();

                if ($this->getValue() instanceof EntitySet) {
                    $entitySet = $this->getEntitySetValue();
                    $transaction = $entitySet->getTransaction();

                    if (!$transaction) {
                        break;
                    }

                    $entitySet->addTrailingMetadata($transaction, $metadata, $metadata['navigationLink']);
                }
                break;

            case $property->getPrimitiveType()->is(Stream::class):
                $metadata['mediaContentType'] = (string) $this->getPrimitiveValue()->getContentType();
                $metadata['mediaReadLink'] = (string) $this->getPrimitiveValue()->getReadLink();
                break;
        }

        return $metadata;
    }
}
