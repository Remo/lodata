<?php

namespace Flat3\Lodata\Interfaces;

/**
 * Transaction Interface
 * @package Flat3\Lodata\Interfaces
 */
interface TransactionInterface
{
    public function startTransaction();

    public function rollback();

    public function commit();
}
