<?php

namespace Flat3\OData\Resource;

use Flat3\OData\Entity;
use Flat3\OData\Expression\Event;
use Flat3\OData\Interfaces\TypeInterface;
use Flat3\OData\Internal\ObjectArray;
use Flat3\OData\Primitive;
use Flat3\OData\Property;
use Flat3\OData\Traits\HasType;
use Flat3\OData\Transaction;

abstract class EntitySet implements TypeInterface
{
    use HasType;

    /** @var Transaction $transaction */
    protected $transaction;

    /** @var Store $store */
    protected $store;

    /** @var Property $entityKey */
    protected $entityKey;

    /** @var Primitive $entityId */
    protected $entityId;

    /** @var null|array $results Result set from the query */
    protected $results = null;

    /** @var int $top Page size to return from the query */
    protected $top = PHP_INT_MAX;

    /** @var int $skip Skip value to use in the query */
    protected $skip = 0;

    /** @var int $topCounter Total number of records fetched for internal pagination */
    private $topCounter = 0;

    /** @var int Limit of number of records to evaluate from the source */
    private $topLimit = PHP_INT_MAX;

    public function __construct(Store $store, ?Transaction $transaction = null, ?Primitive $key = null)
    {
        $this->store = $store;
        $this->transaction = $transaction;
        $this->entityKey = $key ? $key->getProperty() : $store->getType()->getKey();
        $this->entityId = $key;

        $maxPageSize = $store->getMaxPageSize();
        $skip = $transaction->getSkip();
        $top = $transaction->getTop();
        $this->top = $top->hasValue() && ($top->getValue() < $maxPageSize) ? $top->getValue() : $maxPageSize;

        if ($skip->hasValue()) {
            $this->skip = $skip->getValue();
        }

        if ($top->hasValue()) {
            $this->topLimit = $top->getValue();
        }
    }

    public function getStore(): Store
    {
        return $this->store;
    }

    /**
     * Handle a discovered expression symbol in the filter query
     *
     * @param  Event  $event
     *
     * @return bool True if the event was handled
     */
    abstract public function filter(Event $event): ?bool;

    /**
     * Handle a discovered expression symbol in the search query
     *
     * @param  Event  $event
     *
     * @return bool True if the event was handled
     */
    abstract public function search(Event $event): ?bool;

    /**
     * The number of items in this entity set query, including filters, without limit clauses
     *
     * @return int
     */
    abstract public function countResults(): int;

    /**
     * @return ObjectArray
     */
    public function getDeclaredProperties(): ObjectArray
    {
        return $this->store->getType()->getDeclaredProperties();
    }

    public function writeToResponse(Transaction $transaction): void
    {
        while ($this->hasResult()) {
            $entity = $this->getCurrentResultAsEntity();

            $transaction->outputJsonObjectStart();
            $entity->writeToResponse($transaction);
            $transaction->outputJsonObjectEnd();

            $this->nextResult();

            if (!$this->hasResult()) {
                break;
            }

            $transaction->outputJsonSeparator();
        }
    }

    /**
     * Whether there is a current entity in the result set
     * Implements internal pagination
     *
     * @return bool
     */
    public function hasResult(): bool
    {
        if (0 === $this->top) {
            return false;
        }

        if ($this->results === null) {
            $this->generateResultSet();
            $this->topCounter = count($this->results);
        } elseif (!current($this->results) && ($this->topCounter < $this->topLimit)) {
            $this->top = min($this->top, $this->topLimit - $this->topCounter);
            $this->skip += count($this->results);
            $this->results = null;
            $this->generateResultSet();
            $this->topCounter += count($this->results);
        }

        return !!current($this->results);
    }

    /**
     * Perform the query, observing $this->top and $this->skip, loading the results into $this->result_set
     */
    abstract protected function generateResultSet(): void;

    /**
     * The current entity
     *
     * @return Entity
     */
    public function getCurrentResultAsEntity(): ?Entity
    {
        if (null === $this->results && !$this->hasResult()) {
            return null;
        }

        return $this->store->convertResultToEntity(current($this->results));
    }

    /**
     * Move to the next result
     */
    public function nextResult(): void
    {
        next($this->results);
    }
}
