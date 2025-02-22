<?php

namespace Flat3\Lodata\Drivers\SQL;

use Flat3\Lodata\Exception\Internal\NodeHandledException;
use Flat3\Lodata\Expression\Event;
use Flat3\Lodata\Expression\Event\StartFunction;
use Flat3\Lodata\Expression\Node\Func\Arithmetic\Ceiling;
use Flat3\Lodata\Expression\Node\Func\Arithmetic\Floor;
use Flat3\Lodata\Expression\Node\Func\Arithmetic\Round;
use Flat3\Lodata\Expression\Node\Func\DateTime\Day;
use Flat3\Lodata\Expression\Node\Func\DateTime\Hour;
use Flat3\Lodata\Expression\Node\Func\DateTime\Minute;
use Flat3\Lodata\Expression\Node\Func\DateTime\Month;
use Flat3\Lodata\Expression\Node\Func\DateTime\Now;
use Flat3\Lodata\Expression\Node\Func\DateTime\Second;
use Flat3\Lodata\Expression\Node\Func\DateTime\Year;
use Flat3\Lodata\Expression\Node\Func\String\MatchesPattern;
use Flat3\Lodata\Expression\Node\Func\String\ToLower;
use Flat3\Lodata\Expression\Node\Func\String\ToUpper;
use Flat3\Lodata\Expression\Node\Func\String\Trim;
use Flat3\Lodata\Expression\Node\Func\StringCollection\Concat;
use Flat3\Lodata\Expression\Node\Func\StringCollection\IndexOf;
use Flat3\Lodata\Expression\Node\Func\StringCollection\Length;
use Flat3\Lodata\Expression\Node\Func\StringCollection\Substring;

/**
 * SQLServer Filter
 * @package Flat3\Lodata\Drivers\SQL
 */
trait SQLServerFilter
{
    /**
     * Microsoft SQL Server-specific SQL filter generation
     * @param  Event  $event  Filter event
     * @return bool|null
     * @throws NodeHandledException
     */
    public function sqlsrvFilter(Event $event): ?bool
    {
        switch (true) {
            case $event instanceof StartFunction:
                $func = $event->getNode();

                switch (true) {
                    case $func instanceof Ceiling:
                        $this->addWhere('CEILING(');

                        return true;

                    case $func instanceof Floor:
                        $this->addWhere('FLOOR(');

                        return true;

                    case $func instanceof Round:
                        $this->addWhere('ROUND(');

                        return true;

                    case $func instanceof Day:
                        $this->addWhere('DATEPART(day, ');

                        return true;

                    case $func instanceof Hour:
                        $this->addWhere('DATEPART(hour, ');

                        return true;

                    case $func instanceof Minute:
                        $this->addWhere('DATEPART(minute, ');

                        return true;

                    case $func instanceof Month:
                        $this->addWhere('DATEPART(month, ');

                        return true;

                    case $func instanceof Now:
                        $this->addWhere('CURRENT_TIMESTAMP(');

                        return true;

                    case $func instanceof Second:
                        $this->addWhere('DATEPART(second, ');

                        return true;

                    case $func instanceof Year:
                        $this->addWhere('DATEPART(year, ');

                        return true;

                    case $func instanceof MatchesPattern:
                        $arguments = $func->getArguments();
                        list($arg1, $arg2) = $arguments;
                        $arg1->compute();
                        $this->addWhere('LIKE');
                        $arg2->compute();
                        throw new NodeHandledException();

                    case $func instanceof ToLower:
                        $this->addWhere('LOWER(');

                        return true;

                    case $func instanceof ToUpper:
                        $this->addWhere('UPPER(');

                        return true;

                    case $func instanceof Trim:
                        $this->addWhere('TRIM(');

                        return true;

                    case $func instanceof Concat:
                        $this->addWhere('CONCAT(');

                        return true;

                    case $func instanceof IndexOf:
                        $this->addWhere('CHARINDEX(');

                        return true;

                    case $func instanceof Length:
                        $this->addWhere('LEN(');

                        return true;

                    case $func instanceof Substring:
                        $this->addWhere('SUBSTRING(');

                        return true;
                }
                break;
        }

        return false;
    }
}
