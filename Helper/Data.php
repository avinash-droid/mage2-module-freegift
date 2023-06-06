<?php

namespace Avinash\FreeGift\Helper;

use Magento\Checkout\Model\Session;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Avinash\FreeGift\Model\Config\Source\FreeGiftOfferOptions;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Serialize\SerializerInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    private $_serializer;

    protected $_session;
    protected $_productRepository;
    protected $_searchCriteriaBuilder;
    protected $_sortOrderBuilder;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Session $session,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        SerializerInterface $serializer
    ) {
        $this->_session = $session;
        $this->_productRepository = $productRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_sortOrderBuilder = $sortOrderBuilder;
        $this->_serializer = $serializer;
        parent::__construct($context);
    }

    public function applyFreeGift()
    {
        $quote = $this->_session->getQuote();

        if (!$this->quoteHasFreeGiftOfferProduct($quote)) {
            $this->_logger->info('No FreeGift offer product');
            return;
        }

        $this->addFreeGiftProduct($quote);
    }

    public function removeFreeGift()
    {
        $quote = $this->_session->getQuote();

        $freeProductId = $quote->getFreeGiftProduct();
        if (!$freeProductId) {
            $this->_logger->debug("No Free gift to remove.");
            return;
        }

        $quoteItems = $quote->getAllItems();
        foreach ($quoteItems as $quoteItem) {
            if ($quoteItem->getProductId() != $freeProductId) {
                continue;
            }
            if ($quoteItem->getQty() == 1) {
                $quoteItem->getQuote()->removeItem($quoteItem->getItemId());
                break;
            }
            if ($quoteItem->getQty() > 1) {
                $quoteItem->setData('qty', ($quoteItem->getQty() - 1))->save();
                break;
            }
        }
        $quote->setFreeGiftProduct(0)->save();
        $this->_logger->debug("Free gift removed.");
    }

    protected function quoteHasFreeGiftOfferProduct(\Magento\Quote\Model\Quote $quote)
    {
        $quoteItems = $quote->getAllItems();
        $quoteProductIds = array_unique(array_filter(array_map(fn ($item) => $item->getProductId(), $quoteItems)));

        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter('entity_id', $quoteProductIds, 'in')
            ->addFilter('free_gift_offer', FreeGiftOfferOptions::OFFERS_PRODUCT, 'eq')
            ->create();
        $products = $this->_productRepository->getList($searchCriteria)->getItems();
        return !empty($products);
    }

    protected function addFreeGiftProduct(\Magento\Quote\Model\Quote $quote)
    {
        if ($quote->getFreeGiftProduct() != 0) {
            $this->_logger->debug("Free gift already added.");
            return;
        }

        $totals = $quote->getTotals();
        $subtotal = $totals['subtotal']['value'];

        $sortOrder = $this->_sortOrderBuilder->setField('price')->setDirection('DESC')->create();

        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter('free_gift_offer', FreeGiftOfferOptions::PRODUCT_FREEGIFT, 'eq')
            ->addFilter('free_on_amount', (float) $subtotal, 'lteq')
            ->setSortOrders([$sortOrder])
            ->create();
        $products = $this->_productRepository->getList($searchCriteria)->getItems();

        if (empty($products)) {
            $this->_logger->debug("No free product found for current cart total");
            return;
        }

        $freeProduct = array_shift($products);

        $quote->setFreeGiftProduct($freeProduct->getId())->save();
        $this->_logger->debug("Setting gift product id to " . $freeProduct->getId());

        // $quoteItems = $quote->getAllItems();
        // $quoteProductIds = array_unique(array_filter(array_map(fn ($item) => $item->getProductId(), $quoteItems)));
        // if (in_array($freeProduct->getId(), $quoteProductIds)) {
        //     $this->_logger->debug("Free product already in cart");
        //     // increase qty by 1
        //     // Adjust free product discount if needed
        // }

        $quoteItem = $quote->addProduct($freeProduct, 1);
        // $quoteItem->setCustomPrice(0);
        // $quoteItem->setOriginalCustomPrice(0);
        // $quoteItem->setBasePrice($freeProduct->getPrice());
        $quoteItem->setDiscountAmount($freeProduct->getPrice());
        $quoteItem->setBaseDiscountAmount($freeProduct->getPrice());
        $quoteItem->getProduct()->setIsSuperMode(true);

        $additionalOptions = array();
        if ($additionalOption = $quoteItem->getOptionByCode('additional_options')) {
            $additionalOptions = $this->_serializer->unserialize($additionalOption->getValue());
        }
        $additionalOptions[] = [
            'label' => 'Offer',
            'value' => "Free Gift"
        ];
        $quoteItem->addOption(array(
            'product_id' => $quoteItem->getProductId(),
            'code' => 'additional_options',
            'value' => $this->_serializer->serialize($additionalOptions)
        ));

        $quoteItem->save();
    }
}
