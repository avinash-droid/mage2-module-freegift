<?php

namespace Avinash\FreeGift\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class FreeGiftOfferOptions extends AbstractSource
{
    const DISABLED = 1;
    const OFFERS_PRODUCT = 2;
    const PRODUCT_FREEGIFT = 3;

    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (null === $this->_options) {
            $this->_options = [
                ['value' => self::DISABLED, 'label' => __('Disabled')],
                ['value' => self::OFFERS_PRODUCT, 'label' => __('Offer gifts on this product')],
                ['value' => self::PRODUCT_FREEGIFT, 'label' => __('Offer this product as a gift')],
            ];
        }
        return $this->_options;
    }
}
