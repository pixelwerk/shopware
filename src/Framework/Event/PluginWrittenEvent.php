<?php declare(strict_types=1);

namespace Shopware\Framework\Event;

use Shopware\Framework\Write\AbstractWrittenEvent;

class PluginWrittenEvent extends AbstractWrittenEvent
{
    const NAME = 'plugin.written';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getEntityName(): string
    {
        return 'plugin';
    }
}
