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
 * @category  Ced
 * @package   Ced_RueDuCommerce
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\RueDuCommerce\Setup;

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
     *
     * @param EavSetupFactory                                    $eavSetupFactory
     * @param \Magento\Framework\ObjectManagerInterface          $objectManager
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
     * @param ModuleContextInterface   $context
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /**
 		 * @var EavSetup $eavSetup 
		 */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        /**
         * Add attributes to the eav/attribute
         */
        $groupName = 'RueDuCommerce Marketplace';
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);
        $eavSetup->addAttributeGroup($entityTypeId, $attributeSetId, $groupName, 1000);
        $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, $groupName);

        if (!$this->eavAttribute->getIdByCode('catalog_product', 'rueducommerce_state')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'rueducommerce_state',
                [
                    'group' => 'RueDuCommerce Marketplace',
                    'note' => 'Please Select State',
                    'input' => 'select',
                    'type' => 'varchar',
                    'label' => 'State',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 1,
                    'user_defined' => 1,
                    'source' => 'Ced\RueDuCommerce\Model\Source\State',
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }

        if (!$this->eavAttribute->getIdByCode('catalog_product', 'logistic_class')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'logistic_class',
                [
                    'group' => 'RueDuCommerce Marketplace',
                    'note' => 'Please Select Logistic class',
                    'input' => 'select',
                    'type' => 'varchar',
                    'label' => 'Logistic Class',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 1,
                    'user_defined' => 1,
                    'source' => 'Ced\RueDuCommerce\Model\Source\LogisticClass',
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }

        if (!$this->eavAttribute->getIdByCode('catalog_product', 'barcode_ean')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'barcode_ean',
                [
                    'group' => 'RueDuCommerce Marketplace',
                    'input' => 'text',
                    'type' => 'text',
                    'label' => 'Barcode',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 2,
                    'user_defined' => 1,
                    'searchable' => 1,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }

        if (!$this->eavAttribute->getIdByCode('catalog_product', 'brand')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'brand',
                [
                    'group' => 'RueDuCommerce Marketplace',
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
                    'searchable' => 1,
                    'filterable' => 0,
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }

        if (!$this->eavAttribute->getIdByCode('catalog_product', 'rueducommerce_product_status')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'rueducommerce_product_status',
                [
                    'group' => 'RueDuCommerce Marketplace',
                    'note' => 'product status on RueDuCommerce',
                    'input' => 'select',
                    'source' => 'Ced\RueDuCommerce\Model\Source\Product\Status',
                    'type' => 'varchar',
                    'label' => 'RueDuCommerce Product Status',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 12,
                    'user_defined' => 1,
                    'searchable' => 1,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }

        if (!$this->eavAttribute->getIdByCode('catalog_product', 'package_length')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'package_length',
                [
                    'group' => 'RueDuCommerce Marketplace',
                    'note' => "Please enter package length. \n
                    Use only 'C.M.",
                    'input' => 'text',
                    'type' => 'text',
                    'label' => 'Package Length (cm)',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 14,
                    'user_defined' => 1,
                    'searchable' => 1,
                    'filterable' => 0,
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }

        if (!$this->eavAttribute->getIdByCode('catalog_product', 'package_width')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'package_width',
                [
                    'group' => 'RueDuCommerce Marketplace',
                    'note' => "Please enter package width. \n
                    Use only 'C.M.",
                    'input' => 'text',
                    'type' => 'text',
                    'label' => 'Package Width (cm)',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 14,
                    'user_defined' => 1,
                    'searchable' => 1,
                    'filterable' => 0,
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }

        if (!$this->eavAttribute->getIdByCode('catalog_product', 'package_height')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'package_height',
                [
                    'group' => 'RueDuCommerce Marketplace',
                    'note' => "Please enter package height. \n
                    Use only 'C.M.",
                    'input' => 'text',
                    'type' => 'text',
                    'label' => 'Package Height (cm)',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 14,
                    'user_defined' => 1,
                    'searchable' => 1,
                    'filterable' => 0,
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }


        if (!$this->eavAttribute->getIdByCode('catalog_product', 'rueducommerce_club_eligibe')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'rueducommerce_club_eligibe',
                [
                    'group' => 'RueDuCommerce Marketplace',
                    'note' => "Please enter tax class",
                    'input' => 'select',
                    'type' => 'varchar',
                    'label' => 'RueDuCommerce Club Eligibe',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 14,
                    'user_defined' => 1,
                    'searchable' => 1,
                    'filterable' => 0,
                    'source' => 'Ced\RueDuCommerce\Model\Source\ClubEligibe',
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }

        if (!$this->eavAttribute->getIdByCode('catalog_product', 'rueducommerce_validation_errors')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'rueducommerce_validation_errors',
                [
                    'group' => 'RueDuCommerce Marketplace',
                    'note' => "RueDuCommerce Validation Errors",
                    'input' => 'text',
                    'type' => 'text',
                    'label' => 'RueDuCommerce Validation Errors',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 14,
                    'user_defined' => 1,
                    'searchable' => 1,
                    'filterable' => 0,
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }


        if (!$this->eavAttribute->getIdByCode('catalog_product', 'rueducommerce_feed_errors')) {
            $eavSetup->addAttribute(
                'catalog_product',
                'rueducommerce_feed_errors',
                [
                    'group' => 'RueDuCommerce Marketplace',
                    'note' => "RueDuCommerce Feed Errors",
                    'input' => 'text',
                    'type' => 'text',
                    'label' => 'RueDuCommerce Feed Errors',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 14,
                    'user_defined' => 1,
                    'searchable' => 1,
                    'filterable' => 0,
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }
    }
}
