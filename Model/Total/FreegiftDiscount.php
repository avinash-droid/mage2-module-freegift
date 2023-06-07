<?php

namespace Avinash\FreeGift\Model\Total;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * FreegiftDiscount class
 *
 * Calculate FreeGift discount
 */
class FreegiftDiscount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * Currency convertion
     *
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * Product repo
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * For debugging
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Class construtor
     *
     * @param PriceCurrencyInterface $priceCurrency
     * @param ProductRepositoryInterface $productRepository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        ProductRepositoryInterface $productRepository,
        \Psr\Log\LoggerInterface $logger,
    ) {
        $this->_priceCurrency = $priceCurrency;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
    }

    /**
     * Calculates freegift discount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return FreegiftDiscount
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        if (!$quote->getFreeGiftProduct()) {
            return $this;
        }

        $freegiftDiscount = $this->productRepository->getById($quote->getFreeGiftProduct())->getPrice();
        $this->logger->debug('Adding discount amount of ' . $freegiftDiscount);

        $discount =  $this->_priceCurrency->convert($freegiftDiscount);
        $total->setTotalAmount('freegift_discount', -$discount);
        $total->setBaseTotalAmount('freegift_discount', -$freegiftDiscount);
        $quote->setFreegiftDiscount(-$discount);

        return $this;
    }

    /**
     * Gets FreeGift discount details
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        if (!$quote->getFreeGiftProduct()) {
            return [];
        }

        $freegiftDiscount = $this->productRepository->getById($quote->getFreeGiftProduct())->getPrice();
        $freegiftDiscount =  $this->_priceCurrency->convert($freegiftDiscount);
        return [
            'code' => 'freegift_discount',
            'title' => $this->getLabel(),
            'value' => $freegiftDiscount
        ];
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return __('Freegift Discount');
    }
}
