<?php

namespace Flat3\Lodata\PathSegment\Metadata;

use Flat3\Lodata\Controller\Response;
use Flat3\Lodata\Controller\Transaction;
use Flat3\Lodata\EntitySet;
use Flat3\Lodata\Facades\Lodata;
use Flat3\Lodata\Interfaces\AnnotationInterface;
use Flat3\Lodata\Interfaces\ContextInterface;
use Flat3\Lodata\Interfaces\JsonInterface;
use Flat3\Lodata\Interfaces\Operation\ActionInterface;
use Flat3\Lodata\Operation;
use Flat3\Lodata\PathSegment\Metadata;
use Flat3\Lodata\Singleton;
use Flat3\Lodata\Transaction\MediaType;

/**
 * JSON
 * @link https://docs.oasis-open.org/odata/odata-csdl-json/v4.01/odata-csdl-json-v4.01.html
 * @package Flat3\Lodata\PathSegment\Metadata
 */
class JSON extends Metadata implements JsonInterface
{
    /**
     * Emit the service metadata document
     * @param  Transaction  $transaction  Transaction
     */
    public function emitJson(Transaction $transaction): void
    {
        $schema = (object) [];

        $version = $transaction->getVersion();
        $namespace = Lodata::getNamespace();

        $schema->{'$Version'} = $version;
        $schema->{'$EntityContainer'} = $namespace.'.'.'DefaultContainer';

        $schema->{'$Reference'} = [];
        foreach (Lodata::getReferences() as $reference) {
            $reference->appendJson($schema);
        }

        $entityContainer = (object) [];
        $entityContainer->{'$Kind'} = 'EntityContainer';
        $schema->{$namespace} = $entityContainer;

        foreach (Lodata::getEntityTypes() as $entityType) {
            $entityTypeElement = (object) [];
            $schema->{$entityType->getName()} = $entityTypeElement;
            $entityTypeElement->{'$Kind'} = 'EntityType';

            $keyField = $entityType->getKey();

            if ($keyField) {
                $entityTypeElement->{'$Key'} = [
                    $keyField->getName(),
                ];
            }

            foreach ($entityType->getDeclaredProperties() as $property) {
                $entityTypeProperty = (object) [];
                $entityTypeElement->{$property->getName()} = $entityTypeProperty;
                $entityTypeProperty->{'$Type'} = $property->getType()->getIdentifier();
                $entityTypeProperty->{'$Nullable'} = $property->isNullable();

                foreach ($property->getAnnotations() as $annotation) {
                    $annotation->appendJson($entityTypeProperty);
                }
            }

            foreach ($entityType->getNavigationProperties() as $navigationProperty) {
                $targetEntityType = $navigationProperty->getType();

                $navigationPropertyElement = (object) [];
                $entityTypeElement->{$navigationProperty->getName()} = $navigationPropertyElement;
                $navigationPropertyElement->{'$Collection'} = $navigationProperty->isCollection();

                $navigationPropertyPartner = $navigationProperty->getPartner();
                if ($navigationPropertyPartner) {
                    $navigationPropertyElement->{'$Partner'} = $navigationPropertyPartner->getName();
                }

                $navigationPropertyElement->{'$Type'} = $targetEntityType->getIdentifier();
                $navigationPropertyElement->{'$Nullable'} = $navigationProperty->isNullable();

                $constraints = $navigationProperty->getConstraints();
                if ($constraints) {
                    $constraintsElement = (object) [];
                    $navigationPropertyElement->{'$ReferentialConstraint'} = $constraintsElement;
                    foreach ($navigationProperty->getConstraints() as $constraint) {
                        $constraintsElement->{$constraint->getProperty()->getName()} = $constraint->getReferencedProperty()->getName();
                    }
                }
            }
        }

        foreach (Lodata::getEnumerationTypes() as $enumerationType) {
            $enumerationTypeElement = (object) [];
            $schema->{$enumerationType->getName()} = $enumerationTypeElement;

            $enumerationTypeElement->{'$UnderlyingType'} = $enumerationType->getUnderlyingType()->getResolvedName($namespace);
            $enumerationTypeElement->{'$IsFlags'} = $enumerationType->getIsFlags();

            foreach ($enumerationType->getMembers() as $memberName => $memberValue) {
                $enumerationTypeElement->{$memberName} = $memberValue;
            }
        }

        foreach (Lodata::getResources() as $resource) {
            $resourceElement = (object) [];

            switch (true) {
                case $resource instanceof Singleton:
                    $entityContainer->{$resource->getResolvedName($namespace)} = $resourceElement;
                    $resourceElement->{'$Type'} = $resource->getType()->getIdentifier();
                    break;

                case $resource instanceof EntitySet:
                    $entityContainer->{$resource->getResolvedName($namespace)} = $resourceElement;
                    $resourceElement->{'$EntityType'} = $resource->getType()->getIdentifier();

                    $navigationBindings = $resource->getNavigationBindings();
                    if ($navigationBindings) {
                        $navigationPropertyBindingElement = (object) [];
                        $resourceElement->{'$NavigationPropertyBinding'} = $navigationPropertyBindingElement;

                        foreach ($resource->getNavigationBindings() as $binding) {
                            $navigationPropertyBindingElement->{$binding->getPath()->getName()} = $binding->getTarget()->getResolvedName($namespace);
                        }
                    }
                    break;

                case $resource instanceof Operation:
                    $isBound = null !== $resource->getBindingParameterName();

                    $schema->{$resource->getResolvedName($namespace)} = $resourceElement;
                    $resourceElement->{'$Kind'} = $resource->getKind();
                    $resourceElement->{'$IsBound'} = $isBound;

                    $arguments = $resource->getExternalArguments();

                    if ($arguments) {
                        $argumentsElement = [];
                        foreach ($arguments as $argument) {
                            $argumentsElement[] = [
                                '$Name' => $argument->getName(),
                                '$Nullable' => $argument->isNullable(),
                                '$Type' => $argument->getType()->getIdentifier(),
                            ];
                        }
                        $resourceElement->{'$Parameter'} = $argumentsElement;
                    }

                    $returnType = $resource->getReturnType();

                    if (null !== $returnType) {
                        $returnTypeElement = (object) [];
                        $resourceElement->{'$ReturnType'} = $returnTypeElement;
                        $returnTypeElement->{'$Collection'} = $resource->returnsCollection();
                        $returnTypeElement->{'$Type'} = $returnType->getIdentifier();
                        $returnTypeElement->{'$Nullable'} = $resource->isNullable();
                    }

                    if (!$isBound) {
                        $operationImportElement = (object) [];
                        $entityContainer->{$resource->getResolvedName($namespace).'Import'} = $operationImportElement;
                        $operationImportElement->{$resource instanceof ActionInterface ? '$Action' : '$Function'} = $resource->getIdentifier();

                        if (null !== $returnType && $returnType instanceof EntitySet) {
                            $operationImportElement->{'$EntitySet'} = $returnType->getName();
                        }
                    }
                    break;
            }

            if ($resource instanceof AnnotationInterface) {
                foreach ($resource->getAnnotations() as $annotation) {
                    $annotation->appendJson($resourceElement);
                }
            }
        }

        $schemaAnnotationsElement = (object) [];
        $entityContainer->{'$Annotations'} = $schemaAnnotationsElement;

        $targetElement = (object) [];
        $schemaAnnotationsElement->{$namespace.'.'.'DefaultContainer'} = $targetElement;

        foreach (Lodata::getAnnotations() as $annotation) {
            $annotation->appendJson($targetElement);
        }

        $transaction->sendJson($schema);
    }

    public function response(Transaction $transaction, ?ContextInterface $context = null): Response
    {
        $transaction->sendContentType(MediaType::factory()->parse(MediaType::json));

        return $transaction->getResponse()->setCallback(function () use ($transaction) {
            $this->emitJson($transaction);
        });
    }
}
