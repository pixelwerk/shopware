<?php declare(strict_types=1);

namespace Shopware\Currency\Repository;

use Shopware\Context\Struct\TranslationContext;
use Shopware\Currency\Event\CurrencyBasicLoadedEvent;
use Shopware\Currency\Event\CurrencyDetailLoadedEvent;
use Shopware\Currency\Event\CurrencyWrittenEvent;
use Shopware\Currency\Reader\CurrencyBasicReader;
use Shopware\Currency\Reader\CurrencyDetailReader;
use Shopware\Currency\Searcher\CurrencySearcher;
use Shopware\Currency\Searcher\CurrencySearchResult;
use Shopware\Currency\Struct\CurrencyBasicCollection;
use Shopware\Currency\Struct\CurrencyDetailCollection;
use Shopware\Currency\Writer\CurrencyWriter;
use Shopware\Framework\Write\EntityWrittenEvent;
use Shopware\Search\AggregationResult;
use Shopware\Search\Criteria;
use Shopware\Search\UuidSearchResult;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CurrencyRepository
{
    /**
     * @var CurrencyDetailReader
     */
    protected $detailReader;

    /**
     * @var CurrencyBasicReader
     */
    private $basicReader;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var CurrencySearcher
     */
    private $searcher;

    /**
     * @var CurrencyWriter
     */
    private $writer;

    public function __construct(
        CurrencyDetailReader $detailReader,
        CurrencyBasicReader $basicReader,
        EventDispatcherInterface $eventDispatcher,
        CurrencySearcher $searcher,
        CurrencyWriter $writer
    ) {
        $this->detailReader = $detailReader;
        $this->basicReader = $basicReader;
        $this->eventDispatcher = $eventDispatcher;
        $this->searcher = $searcher;
        $this->writer = $writer;
    }

    public function readBasic(array $uuids, TranslationContext $context): CurrencyBasicCollection
    {
        if (empty($uuids)) {
            return new CurrencyBasicCollection();
        }

        $collection = $this->basicReader->readBasic($uuids, $context);

        $this->eventDispatcher->dispatch(
            CurrencyBasicLoadedEvent::NAME,
            new CurrencyBasicLoadedEvent($collection, $context)
        );

        return $collection;
    }

    public function readDetail(array $uuids, TranslationContext $context): CurrencyDetailCollection
    {
        if (empty($uuids)) {
            return new CurrencyDetailCollection();
        }
        $collection = $this->detailReader->readDetail($uuids, $context);

        $this->eventDispatcher->dispatch(
            CurrencyDetailLoadedEvent::NAME,
            new CurrencyDetailLoadedEvent($collection, $context)
        );

        return $collection;
    }

    public function search(Criteria $criteria, TranslationContext $context): CurrencySearchResult
    {
        /** @var CurrencySearchResult $result */
        $result = $this->searcher->search($criteria, $context);

        $this->eventDispatcher->dispatch(
            CurrencyBasicLoadedEvent::NAME,
            new CurrencyBasicLoadedEvent($result, $context)
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

    public function update(array $data, TranslationContext $context): CurrencyWrittenEvent
    {
        $event = $this->writer->update($data, $context);

        $container = new EntityWrittenEvent($event, $context);
        $this->eventDispatcher->dispatch($container::NAME, $container);

        return $event;
    }

    public function upsert(array $data, TranslationContext $context): CurrencyWrittenEvent
    {
        $event = $this->writer->upsert($data, $context);

        $container = new EntityWrittenEvent($event, $context);
        $this->eventDispatcher->dispatch($container::NAME, $container);

        return $event;
    }

    public function create(array $data, TranslationContext $context): CurrencyWrittenEvent
    {
        $event = $this->writer->create($data, $context);

        $container = new EntityWrittenEvent($event, $context);
        $this->eventDispatcher->dispatch($container::NAME, $container);

        return $event;
    }
}
