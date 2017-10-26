<?php declare(strict_types=1);

namespace Shopware\PaymentMethod\Repository;

use Shopware\Context\Struct\TranslationContext;
use Shopware\Framework\Write\EntityWrittenEvent;
use Shopware\PaymentMethod\Event\PaymentMethodBasicLoadedEvent;
use Shopware\PaymentMethod\Event\PaymentMethodDetailLoadedEvent;
use Shopware\PaymentMethod\Event\PaymentMethodWrittenEvent;
use Shopware\PaymentMethod\Reader\PaymentMethodBasicReader;
use Shopware\PaymentMethod\Reader\PaymentMethodDetailReader;
use Shopware\PaymentMethod\Searcher\PaymentMethodSearcher;
use Shopware\PaymentMethod\Searcher\PaymentMethodSearchResult;
use Shopware\PaymentMethod\Struct\PaymentMethodBasicCollection;
use Shopware\PaymentMethod\Struct\PaymentMethodDetailCollection;
use Shopware\PaymentMethod\Writer\PaymentMethodWriter;
use Shopware\Search\AggregationResult;
use Shopware\Search\Criteria;
use Shopware\Search\UuidSearchResult;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PaymentMethodRepository
{
    /**
     * @var PaymentMethodDetailReader
     */
    protected $detailReader;

    /**
     * @var PaymentMethodBasicReader
     */
    private $basicReader;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var PaymentMethodSearcher
     */
    private $searcher;

    /**
     * @var PaymentMethodWriter
     */
    private $writer;

    public function __construct(
        PaymentMethodDetailReader $detailReader,
        PaymentMethodBasicReader $basicReader,
        EventDispatcherInterface $eventDispatcher,
        PaymentMethodSearcher $searcher,
        PaymentMethodWriter $writer
    ) {
        $this->detailReader = $detailReader;
        $this->basicReader = $basicReader;
        $this->eventDispatcher = $eventDispatcher;
        $this->searcher = $searcher;
        $this->writer = $writer;
    }

    public function readBasic(array $uuids, TranslationContext $context): PaymentMethodBasicCollection
    {
        if (empty($uuids)) {
            return new PaymentMethodBasicCollection();
        }

        $collection = $this->basicReader->readBasic($uuids, $context);

        $this->eventDispatcher->dispatch(
            PaymentMethodBasicLoadedEvent::NAME,
            new PaymentMethodBasicLoadedEvent($collection, $context)
        );

        return $collection;
    }

    public function readDetail(array $uuids, TranslationContext $context): PaymentMethodDetailCollection
    {
        if (empty($uuids)) {
            return new PaymentMethodDetailCollection();
        }
        $collection = $this->detailReader->readDetail($uuids, $context);

        $this->eventDispatcher->dispatch(
            PaymentMethodDetailLoadedEvent::NAME,
            new PaymentMethodDetailLoadedEvent($collection, $context)
        );

        return $collection;
    }

    public function search(Criteria $criteria, TranslationContext $context): PaymentMethodSearchResult
    {
        /** @var PaymentMethodSearchResult $result */
        $result = $this->searcher->search($criteria, $context);

        $this->eventDispatcher->dispatch(
            PaymentMethodBasicLoadedEvent::NAME,
            new PaymentMethodBasicLoadedEvent($result, $context)
        );

        return $result;
    }

    public function searchUuids(Criteria $criteria, TranslationContext $context): UuidSearchResult
    {
        return $this->searcher->searchUuids($criteria, $context);
    }

    public function aggregate(Criteria $criteria, TranslationContext $context): AggregationResult
    {
        $result = $this->searcher->aggregate($criteria, $context);

        return $result;
    }

    public function update(array $data, TranslationContext $context): PaymentMethodWrittenEvent
    {
        $event = $this->writer->update($data, $context);

        $container = new EntityWrittenEvent($event, $context);
        $this->eventDispatcher->dispatch($container::NAME, $container);

        return $event;
    }

    public function upsert(array $data, TranslationContext $context): PaymentMethodWrittenEvent
    {
        $event = $this->writer->upsert($data, $context);

        $container = new EntityWrittenEvent($event, $context);
        $this->eventDispatcher->dispatch($container::NAME, $container);

        return $event;
    }

    public function create(array $data, TranslationContext $context): PaymentMethodWrittenEvent
    {
        $event = $this->writer->create($data, $context);

        $container = new EntityWrittenEvent($event, $context);
        $this->eventDispatcher->dispatch($container::NAME, $container);

        return $event;
    }
}
