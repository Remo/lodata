<?php

namespace Flat3\Lodata\Type;

use DateTime;
use Flat3\Lodata\Expression\Lexer;
use Flat3\Lodata\Helper\Constants;

/**
 * Time Of Day
 * @package Flat3\Lodata\Type
 * @method static self factory($value = null, ?bool $nullable = true)
 */
class TimeOfDay extends DateTimeOffset
{
    const identifier = 'Edm.TimeOfDay';

    const openApiSchema = [
        'type' => Constants::OAPI_STRING,
        'format' => 'time',
        'pattern' => '^'.Lexer::TIME_OF_DAY.'$',
    ];

    public const DATE_FORMAT = 'H:i:s.u';

    protected function repack(DateTime $dt)
    {
        return $dt->setDate(1970, 1, 1);
    }
}
