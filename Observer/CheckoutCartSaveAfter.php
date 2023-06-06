<?php

namespace Avinash\FreeGift\Observer;

class CheckoutCartSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    protected $logger;
    protected $helper;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Avinash\FreeGift\Helper\Data $helper
    ) {
        $this->logger = $logger;
        $this->helper = $helper;
    }

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
