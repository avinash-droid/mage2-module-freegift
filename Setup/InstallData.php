<?php

namespace Avinash\FreeGift\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $attributeGroup = "Free Gift Offer Group";
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'free_gift_offer',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => __('Free Gift Offer'),
                'group' => $attributeGroup,
                'input' => 'select',
                'class' => '',
                'source' => \Avinash\FreeGift\Model\Config\Source\FreeGiftOfferOptions::class,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => 1,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => 'simple,grouped,configurable,downloadable,virtual,bundle'
            ]
        );
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'free_on_amount',
            [
                'type' => 'text',
                'backend' => '',
                'frontend' => '',
                'label' => __('Offer as Free Gift above cart value of'),
                'group' => $attributeGroup,
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => 'simple,grouped,configurable,downloadable,virtual,bundle'
            ]
        );
    }
}
