<?php

namespace Flat3\Lodata\Tests\Unit\Parser;

use Flat3\Lodata\Controller\Request;
use Flat3\Lodata\Controller\Transaction;
use Flat3\Lodata\Drivers\SQLEntitySet;
use Flat3\Lodata\EntityType;
use Flat3\Lodata\Exception\Internal\ParserException;
use Flat3\Lodata\Exception\Protocol\NotImplementedException;
use Flat3\Lodata\Expression\Parser\Filter;
use Flat3\Lodata\Facades\Lodata;
use Flat3\Lodata\NavigationProperty;
use Flat3\Lodata\Tests\LoopbackEntitySet;
use Flat3\Lodata\Tests\TestCase;

class FilterTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->withFlightModel();
    }

    public function test_0()
    {
        $this->assertResult('origin eq "test"',);
    }

    public function test_1()
    {
        $this->assertResult("origin eq 'test'",);
    }

    public function test_2()
    {
        $this->assertResult("origin eq 'test",);
    }

    public function test_3()
    {
        $this->assertResult('id eq 4',);
    }

    public function test_4()
    {
        $this->assertResult('id gt 4',);
    }

    public function test_5()
    {
        $this->assertResult('id lt 4',);
    }

    public function test_6()
    {
        $this->assertResult('id ge 4',);
    }

    public function test_7()
    {
        $this->assertResult('id le 4',);
    }

    public function test_8()
    {
        $this->assertResult('id eq test',);
    }

    public function test_9()
    {
        $this->assertResult("origin in ('a', 'b', 'c')",);
    }

    public function test_a()
    {
        $this->assertResult("origin in ('a')",);
    }

    public function test_b()
    {
        $this->assertResult('id in (4, 3)',);
    }

    public function test_c()
    {
        $this->assertResult('id lt 4 and id gt 2',);
    }

    public function test_d()
    {
        $this->assertResult('id lt 4 or id gt 2',);
    }

    public function test_e()
    {
        $this->assertResult('id lt 4 or id lt 3 or id lt 2',);
    }

    public function test_f()
    {
        $this->assertResult('id lt 4 or id lt 3 and id lt 2',);
    }

    public function test_10()
    {
        $this->assertResult('id lt 4 or id in (3, 1) and id ge 2',);
    }

    public function test_11()
    {
        $this->assertResult('(id lt 4 and (id ge 7 or id gt 3)',);
    }

    public function test_12()
    {
        $this->assertResult('(id lt 4 a',);
    }

    public function test_13()
    {
        $this->assertResult('(id lt 4 and id ge 7) or id gt 3',);
    }

    public function test_14()
    {
        $this->assertResult('id lt 4 or (id gt 3 and id gt 2)',);
    }

    public function test_15()
    {
        $this->assertResult('(id lt 4 and id ge 7) or (id gt 3 and id gt 2)',);
    }

    public function test_16()
    {
        $this->assertResult('id add 3.14 eq 1.59',);
    }

    public function test_17()
    {
        $this->assertResult('id in (1.59, 2.14)',);
    }

    public function test_18()
    {
        $this->assertResult('(id add 3.14) in (1.59, 2.14) or (id gt -2.40 and id gt 4 add 5)',);
    }

    public function test_19()
    {
        $this->assertResult('id add 3.14 add 5 in (1.59, 2.14)',);
    }

    public function test_1a()
    {
        $this->assertResult('id add 3.14 in (1.59, 2.14)',);
    }

    public function test_1b()
    {
        $this->assertResult('id add 3.14 in (1.59, 2.14) or (id gt -2.40 and id gt 4 add 5)',);
    }

    public function test_1c()
    {
        $this->assertResult("not(contains(origin,'a')) and ((origin eq 'abcd') or (origin eq 'e'))",);
    }

    public function test_1d()
    {
        $this->assertResult("not(origin eq 'a')",);
    }

    public function test_1e()
    {
        $this->assertResult("origin eq 'b' and not(origin eq 'a')",);
    }

    public function test_1f()
    {
        $this->assertResult("origin eq 'b' or not(origin eq 'a')",);
    }

    public function test_20()
    {
        $this->assertResult("contains(origin, 'b')",);
    }

    public function test_21()
    {
        $this->assertResult("endswith(origin, 'b')",);
    }

    public function test_22()
    {
        $this->assertResult("concat(origin, 'abc') eq '123abc'",);
    }

    public function test_23()
    {
        $this->assertResult("concat(origin, 'abc', 4.0) eq '123abc'",);
    }

    public function test_24()
    {
        $this->assertResult("concat(origin, id) eq '123abc'",);
    }

    public function test_25()
    {
        $this->assertResult("concat(origin, concat(id, 4)) eq '123abc'",);
    }

    public function test_26()
    {
        $this->assertResult("indexof(origin,'abc123') eq 1",);
    }

    public function test_27()
    {
        $this->assertResult("length(origin) eq 1",);
    }

    public function test_28()
    {
        $this->assertResult("substring(origin,1) eq 'abc123'",);
    }

    public function test_29()
    {
        $this->assertResult("substring(origin,1,4) eq 'abc123'",);
    }

    public function test_2a()
    {
        $this->assertResult("matchesPattern(origin,'^A.*e$')",);
    }

    public function test_2b()
    {
        $this->assertResult("tolower(origin) eq 'abc123'",);
    }

    public function test_2c()
    {
        $this->assertResult("toupper(origin) eq 'abc123'",);
    }

    public function test_2d()
    {
        $this->assertResult("trim(origin) eq 'abc123'",);
    }

    public function test_2e()
    {
        $this->assertResult('ceiling(origin) eq 4',);
    }

    public function test_2f()
    {
        $this->assertResult('floor(origin) eq 4',);
    }

    public function test_30()
    {
        $this->assertResult('origin eq 4 div 3');
    }

    public function test_31()
    {
        $this->assertResult('origin eq 4 divby 3');
    }

    public function test_32()
    {
        $this->assertResult('origin eq 4 add 3');
    }

    public function test_33()
    {
        $this->assertResult('origin eq 4 sub 3');
    }

    public function test_34()
    {
        $this->assertResult('origin eq 4 mul 3');
    }

    public function test_35()
    {
        $this->assertResult('origin eq 4 mod 3');
    }

    public function test_36()
    {
        $this->assertResult('origin eq 4');
    }

    public function test_37()
    {
        $this->assertResult('origin gt 4');
    }

    public function test_38()
    {
        $this->assertResult('origin ge 4');
    }

    public function test_39()
    {
        $this->assertResult('origin in (4,3)');
    }

    public function test_3a()
    {
        $this->assertResult('origin lt 4');
    }

    public function test_3b()
    {
        $this->assertResult('origin le 4');
    }

    public function test_3c()
    {
        $this->assertResult('origin ne 4');
    }

    public function test_3d()
    {
        $this->assertResult('origin eq true');
    }

    public function test_3e()
    {
        $this->assertResult('origin eq false');
    }

    public function test_3f()
    {
        $this->assertResult('origin eq 2000-01-01');
    }

    public function test_40()
    {
        $this->assertResult('origin eq 2000-01-01T12:34:59Z+00:00');
    }

    public function test_41()
    {
        $this->assertResult('origin eq 04:11:12');
    }

    public function test_42()
    {
        $this->assertResult('origin eq 4AA33245-E2D1-470D-9433-01AAFCF0C83F');
    }

    public function test_43()
    {
        $this->assertResult('origin eq PT1M');
    }

    public function test_44()
    {
        $this->assertResult('origin eq PT36H');
    }

    public function test_45()
    {
        $this->assertResult('origin eq P10DT2H30M');
    }

    public function test_46()
    {
        $this->assertResult('round(origin) eq 4',);
    }

    public function test_47()
    {
        $this->assertResult('date(origin) eq 2001-01-01');
    }

    public function test_48()
    {
        $this->assertResult('day(origin) eq 4');
    }

    public function test_49()
    {
        $this->assertResult('hour(origin) eq 3');
    }

    public function test_4a()
    {
        $this->assertResult('minute(origin) eq 33');
    }

    public function test_4b()
    {
        $this->assertResult('month(origin) eq 11');
    }

    public function test_4c()
    {
        $this->assertResult('now() eq 10:00:00');
    }

    public function test_4d()
    {
        $this->assertResult('second(origin) eq 44');
    }

    public function test_4e()
    {
        $this->assertResult('time(origin) eq 10:00:00');
    }

    public function test_4f()
    {
        $this->assertResult('year(origin) eq 1999');
    }

    public function test_50()
    {
        $this->assertResult("endswith(origin,'a')");
    }

    public function test_51()
    {
        $this->assertResult("indexof(origin,'a') eq 1");
    }

    public function test_52()
    {
        $this->assertResult("startswith(origin,'a')");
    }

    public function test_53()
    {
        $this->assertResult("passengers/any(p:p/name gt 'a')");
    }

    public function test_54()
    {
        $this->assertResult("54 gt 12 and passengers/any(p:p/name gt 'b' and p/name lt 'b' or id eq 4)");
    }

    public function assertLoopbackSet($input)
    {
        $transaction = new Transaction();
        $entitySet = new LoopbackEntitySet('flights', Lodata::getEntityType('flight'));

        $parser = new Filter($entitySet, $transaction);

        try {
            $tree = $parser->generateTree($input);
            $tree->compute();

            $this->assertMatchesSnapshot(trim($entitySet->filterBuffer));
        } catch (ParserException $e) {
            $this->assertMatchesSnapshot($e->getMessage());
        }
    }

    public function assertSQLDriver($driver, $input)
    {
        $flightType = Lodata::getEntityType('flight');
        $airportType = Lodata::getEntityType('airport');

        $flightSet = new class($driver, 'flights', $flightType) extends SQLEntitySet {
            protected $driver;

            public function __construct(string $driver, string $name, EntityType $entityType)
            {
                parent::__construct($name, $entityType);
                $this->driver = $driver;
            }

            public function getDriver(): string
            {
                return $this->driver;
            }
        };

        $airportSet = new class($driver, 'airports', $airportType) extends SQLEntitySet {
            protected $driver;

            public function __construct(string $driver, string $name, EntityType $entityType)
            {
                parent::__construct($name, $entityType);
                $this->driver = $driver;
            }

            public function getDriver(): string
            {
                return $this->driver;
            }
        };

        $cFlights = Lodata::getEntitySet('flights');

        $cFlights->getBindingByNavigationProperty($flightType->getNavigationProperty('airports'))
            ->setTarget($airportSet);

        try {
            $transaction = new Transaction();
            $request = new Request(new \Illuminate\Http\Request());
            $request->query->set('$filter', $input);
            $request->query->set('$select', 'id,origin');
            $transaction->initialize($request);
            $query = $flightSet->setTransaction($transaction);

            $queryString = $query->getSetResultQueryString();
            $queryParameters = $query->getParameters();

            $this->assertMatchesSnapshot($queryString);
            $this->assertMatchesSnapshot($queryParameters);
        } catch (ParserException $exception) {
            $this->assertMatchesSnapshot($exception->getMessage());
        } catch (NotImplementedException $exception) {
            $this->assertMatchesSnapshot($exception->getMessage());
        }
    }

    public function assertResult($input)
    {
        $this->assertLoopbackSet($input);
        $this->assertSQLDriver('mysql', $input);
        $this->assertSQLDriver('pgsql', $input);
        $this->assertSQLDriver('sqlite', $input);
        $this->assertSQLDriver('sqlsrv', $input);
    }
}
