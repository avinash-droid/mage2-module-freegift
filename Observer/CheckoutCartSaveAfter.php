<?php

namespace Avinash\FreeGift\Observer;

/**
 * CheckoutCartSaveAfter class
 *
 * Observer for event checkout_cart_save_after
 */
class CheckoutCartSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * For debugging
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * FreeGift helper
     *
     * @var \Avinash\FreeGift\Helper\Data
     */
    protected $helper;

    /**
     * Class constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Avinash\FreeGift\Helper\Data $helper
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Avinash\FreeGift\Helper\Data $helper
    ) {
        $this->logger = $logger;
        $this->helper = $helper;
    }

    /**
     * Applies or removes freegift depending on if cart rule is applied
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Checkout\Model\Cart $data */
        $cart = $observer->getData('cart');
        $quote = $cart->getQuote();

        $appliedRules = $quote->getAppliedRuleIds();
        $oldAppliedRules = $quote->getOrigData('applied_rule_ids');

        if (!empty($appliedRules) && empty($oldAppliedRules)) {
            $this->helper->removeFreeGift();
        } elseif (empty($appliedRules)) {
            $this->helper->applyFreeGift();
        }
    }
}
