<?php declare(strict_types=1);

namespace Shopware\Framework\Write\Resource;

use Shopware\Context\Struct\TranslationContext;
use Shopware\Framework\Event\CoreAclPrivilegesWrittenEvent;
use Shopware\Framework\Write\Field\IntField;
use Shopware\Framework\Write\Field\StringField;
use Shopware\Framework\Write\Flag\Required;
use Shopware\Framework\Write\WriteResource;

class CoreAclPrivilegesWriteResource extends WriteResource
{
    protected const RESOURCEID_FIELD = 'resourceID';
    protected const NAME_FIELD = 'name';

    public function __construct()
    {
        parent::__construct('s_core_acl_privileges');

        $this->fields[self::RESOURCEID_FIELD] = (new IntField('resourceID'))->setFlags(new Required());
        $this->fields[self::NAME_FIELD] = (new StringField('name'))->setFlags(new Required());
    }

    public function getWriteOrder(): array
    {
        return [
            self::class,
        ];
    }

    public static function createWrittenEvent(array $updates, TranslationContext $context, array $errors = []): CoreAclPrivilegesWrittenEvent
    {
        $event = new CoreAclPrivilegesWrittenEvent($updates[self::class] ?? [], $context, $errors);

        unset($updates[self::class]);

        if (!empty($updates[self::class])) {
            $event->addEvent(self::createWrittenEvent($updates, $context));
        }

        return $event;
    }
}
