<?php

namespace Avinash\FreeGift\Observer;

use Magento\Quote\Model\QuoteFactory;

class SalesOrderSaveBefore implements \Magento\Framework\Event\ObserverInterface
{
    protected $quoteFactory;

    public function __construct(QuoteFactory $quoteFactory)
    {
        $this->quoteFactory = $quoteFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getData('order');

        $quote = $this->quoteFactory->create()->load($order->getQuoteId());
        $order->setFreeGiftProduct($quote->getFreeGiftProduct());

        $quoteItems = [];
        // Map Quote Item with Quote Item Id
        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            $quoteItems[$quoteItem->getId()] = $quoteItem;
        }

        foreach ($order->getAllVisibleItems() as $orderItem) {
            if (!key_exists($orderItem->getQuoteItemId(), $quoteItems)) {
                continue;
            }
            $quoteItem = $quoteItems[$orderItem->getQuoteItemId()];
            $additionalOptions = $quoteItem->getOptionByCode('additional_options');

            if (isset($additionalOptions) && $additionalOptions->getValue()) {
                // Get Order Item's other options
                $options = $orderItem->getProductOptions();
                // Set additional options to Order Item
                $options['additional_options'] = json_decode($additionalOptions->getValue());
                $orderItem->setProductOptions($options);
            }
        }
    }
}
