<?php declare(strict_types=1);

namespace Shopware\AreaCountryState\Event;

use Shopware\Framework\Write\AbstractWrittenEvent;

class AreaCountryStateWrittenEvent extends AbstractWrittenEvent
{
    const NAME = 'area_country_state.written';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getEntityName(): string
    {
        return 'area_country_state';
    }
}
