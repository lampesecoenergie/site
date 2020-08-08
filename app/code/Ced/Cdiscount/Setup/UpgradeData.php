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
 * @package   Ced_Cdiscount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Cdiscount\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Upgrade Data script
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpgradeData implements UpgradeDataInterface
{

    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    private $eavAttribute;

    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $eavAttribute
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavAttribute = $eavAttribute;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '0.0.2', '<')) {
            $eavSetup = $this->eavSetupFactory->create();
            $groupName = 'Cdiscount';
            $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
            $attributeSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);
            $eavSetup->addAttributeGroup($entityTypeId, $attributeSetId, $groupName, 1000);
            $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, $groupName);

            if (!$this->eavAttribute->getIdByCode('catalog_product', 'cdiscount_profile_id')) {
                $eavSetup->addAttribute(
                    'catalog_product',
                    'cdiscount_profile_id',
                    [
                        'group' => $groupName,
                        'note' => 'Cdiscount Profile Id',
                        'input' => 'text',
                        'type' => 'varchar',
                        'label' => 'Cdiscount Profile Id ',
                        'backend' => '',
                        'visible' => 1,
                        'required' => 0,
                        'sort_order' => 1,
                        'user_defined' => 1,
                        'comparable' => 0,
                        'visible_on_front' => 0,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    ]
                );
            }

            if (!$this->eavAttribute->getIdByCode('catalog_product', 'cdiscount_feed_product')) {
                $eavSetup->addAttribute(
                    'catalog_product',
                    'cdiscount_feed_product',
                    [
                        'group' => $groupName,
                        'note' => 'Cdiscount Feed Products',
                        'input' => 'text',
                        'type' => 'varchar',
                        'label' => 'Cdiscount Feed Products ',
                        'backend' => '',
                        'visible' => false,
                        'required' => 0,
                        'sort_order' => 1,
                        'user_defined' => 1,
                        'comparable' => 0,
                        'visible_on_front' => 0,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    ]
                );
            }

            $setup->endSetup();
        }
    }

}
