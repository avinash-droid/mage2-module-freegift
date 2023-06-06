<?php

namespace Avinash\FreeGift\Model\ResourceModel\Order\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OriginalCollection;
use Psr\Log\LoggerInterface as Logger;

/**
 * Order grid extended collection
 */
class Collection extends OriginalCollection
{
    protected $helper;

    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'sales_order_grid',
        $resourceModel = \Magento\Sales\Model\ResourceModel\Order::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    protected function _renderFiltersBefore()
    {
        $salesOrderTable = $this->getTable('sales_order');
        $catalogProductEntityTable = $this->getTable('catalog_product_entity');
        $this->getSelect()
            ->joinLeft($salesOrderTable, 'main_table.entity_id = sales_order.entity_id', ['free_gift_product'])
            ->joinLeft(
                $catalogProductEntityTable,
                'sales_order.free_gift_product = catalog_product_entity.entity_id',
                ['sku as free_gift_product_sku']
            );
        parent::_renderFiltersBefore();
    }
}
