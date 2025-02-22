<?php

namespace Flat3\Lodata\Annotation\Capabilities\V1;

use Flat3\Lodata\Annotation;
use Flat3\Lodata\Helper\Constants;
use Flat3\Lodata\Transaction\MediaType;
use Flat3\Lodata\Transaction\MetadataType;
use Flat3\Lodata\Transaction\MetadataType\Full;
use Flat3\Lodata\Transaction\MetadataType\Minimal;
use Flat3\Lodata\Transaction\MetadataType\None;
use Flat3\Lodata\Type\Collection;
use Flat3\Lodata\Type\String_;

/**
 * Supported Formats
 * @package Flat3\Lodata\Annotation\Capabilities\V1
 */
class SupportedFormats extends Annotation
{
    protected $name = 'Org.OData.Capabilities.V1.SupportedFormats';

    public function __construct()
    {
        $this->value = new Collection();

        /** @var MetadataType $attribute */
        foreach ([Full::class, Minimal::class, None::class] as $attribute) {
            $this->value->add(new String_(
                MediaType::factory()->parse(MediaType::json)
                    ->setParameter('odata.metadata', $attribute::name)
                    ->setParameter('IEEE754Compatible', Constants::TRUE)
                    ->setParameter('odata.streaming', Constants::TRUE)
            ));
        }
    }
}