<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{

    /**
     * @var EavSetupFactory
     */
    public $eavSetupFactory;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    public $eavAttribute;

    /**
     * InstallData constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $eavAttribute
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $eavAttribute
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->objectManager = $objectManager;
        $this->eavAttribute = $eavAttribute;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        /**
         * Add attributes to the eav/attribute
         */
        $groupName = 'Amazon';
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);
        $eavSetup->addAttributeGroup($entityTypeId, $attributeSetId, $groupName, 1000);
        $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, $groupName);

        /*if (!$this->eavAttribute->getIdByCode('catalog_product', 'barcode_type')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'barcode_type',
                [
                    'group' => 'Amazon',
                    'note' => 'Please Select type of barcode',
                    'input' => 'select',
                    'type' => 'varchar',
                    'label' => 'Barcode',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 1,
                    'user_defined' => 1,
                    'source' => 'Ced\Amazon\Model\Source\Barcode',
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }*/

        /*if (!$this->eavAttribute->getIdByCode('catalog_product', 'barcode')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'barcode',
                [
                    'group' => 'Amazon',
                    'input' => 'text',
                    'type' => 'text',
                    'label' => 'Barcode Value',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 2,
                    'user_defined' => 1,
                    'searchable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }*/

        /*if (!$this->eavAttribute->getIdByCode('catalog_product', 'brand')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'brand',
                [
                    'group' => 'Amazon',
                    'note' => '1 to 50 characters',
                    'frontend_class' => 'validate-length maximum-length-50',
                    'input' => 'text',
                    'type' => 'text',
                    'label' => 'Brand',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 3,
                    'user_defined' => 1,
                    'searchable' => 0,
                    'filterable' => 0,
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }*/

        /*if (!$this->eavAttribute->getIdByCode('catalog_product', 'mpn')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'mpn',
                [
                    'group' => 'Amazon',
                    'note' => '1 to 50 characters',
                    'frontend_class' => 'validate-length maximum-length-50',
                    'input' => 'text',
                    'type' => 'text',
                    'label' => 'MPN',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 3,
                    'user_defined' => 1,
                    'searchable' => 0,
                    'filterable' => 0,
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }*/

        if (!$this->eavAttribute->getIdByCode('catalog_product', 'bullets')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'bullets',
                [
                    'group' => 'Amazon',
                    'note' => "Please enter product feature description.Add each feature seperated by '||'.\n Example 
                    : 'This is first one.||This is second one.' and so on. Each bullet can contain maximum of 
                    500 characters. and maximum 5 bullets are allowed.",
                    'input' => 'textarea',
                    'type' => 'text',
                    'label' => 'Bullets',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 10,
                    'user_defined' => 1,
                    'searchable' => 0,
                    'filterable' => 0,
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }

        /*if (!$this->eavAttribute->getIdByCode('catalog_product', 'condition')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'condition',
                [
                    'group' => 'Amazon',
                    'note' => 'Please Select type of condition type',
                    'input' => 'select',
                    'type' => 'varchar',
                    'label' => 'Condition Type',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 1,
                    'user_defined' => 1,
                    'source' => 'Ced\Amazon\Model\Source\Condition',
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }*/
        
        if (!$this->eavAttribute->getIdByCode('catalog_product', 'amazon_product_status')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'amazon_product_status',
                [
                    'group' => 'Amazon',
                    'note' => 'Product status on Amazon Marketplace',
                    'input' => 'text',
                    'type' => 'text',
                    'label' => 'Amazon Status',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 12,
                    'user_defined' => 1,
                    'searchable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }

        if (!$this->eavAttribute->getIdByCode('catalog_product', 'item_dimensions')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'item_dimensions',
                [
                    'group' => 'Amazon',
                    'note' => "Please enter product dimensions. \n
                    Use only '11x11x11' format for Length x Width x Height respectively. All of them must be in C.M.",
                    'input' => 'text',
                    'type' => 'text',
                    'label' => 'Item Dimensions',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 14,
                    'user_defined' => 1,
                    'searchable' => 0,
                    'filterable' => 0,
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }

        if (!$this->eavAttribute->getIdByCode('catalog_product', 'amazon_validation_errors')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'amazon_validation_errors',
                [
                    'group' => 'Amazon',
                    'note' => "Amazon Validation Errors",
                    'input' => 'text',
                    'type' => 'text',
                    'label' => 'Validation Errors',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 14,
                    'user_defined' => 1,
                    'searchable' => 0,
                    'filterable' => 0,
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }

        if (!$this->eavAttribute->getIdByCode('catalog_product', 'amazon_feed_errors')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'amazon_feed_errors',
                [
                    'group' => 'Amazon',
                    'note' => "Amazon Feed Errors",
                    'input' => 'text',
                    'type' => 'text',
                    'label' => 'Feed Errors',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 14,
                    'user_defined' => 1,
                    'searchable' => 0,
                    'filterable' => 0,
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }
    }
}
