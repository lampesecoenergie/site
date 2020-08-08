<?php

namespace Ced\Amazon\Setup;

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

    /** @var Migration */
    public $migration;

    /** @var \Ced\Amazon\Model\ResourceModel\Account\Collection */
    public $accounts;

    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $eavAttribute,
        \Ced\Amazon\Setup\Migration $migration,
        \Ced\Amazon\Model\ResourceModel\Account\Collection $collection
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavAttribute = $eavAttribute;
        $this->migration = $migration;
        $this->accounts = $collection;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '0.0.3', '<')) {
            $setup->startSetup();
            /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create();
            $groupName = 'Amazon';
            $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
            $attributeSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);
            $eavSetup->addAttributeGroup($entityTypeId, $attributeSetId, $groupName, 1000);
            $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, $groupName);

            if (!$this->eavAttribute->getIdByCode(
                'catalog_product',
                \Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_PROFILE_ID
            )) {
                $eavSetup->addAttribute(
                    'catalog_product',
                    \Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_PROFILE_ID,
                    [
                        'group' => $groupName,
                        'note' => 'Amazon Profile Id',
                        'input' => 'text',
                        'type' => 'varchar',
                        'label' => 'Amazon Profile Id ',
                        'backend' => '',
                        'visible' => 1,
                        'required' => 0,
                        'sort_order' => 1,
                        'user_defined' => 1,
                        'comparable' => 0,
                        'visible_on_front' => 0,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    ]
                );
            }

            // Migrate config and update orders
            try {
                $accountId = $this->migration->migrateAccount();
                $this->migration->updateOrders($accountId);
            } catch (\Exception $e) {
                // Add log
            }

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), '0.1.0', '<')) {
            // Updating cedcommerce
            /** @var \Ced\Amazon\Model\Account $account */
            foreach ($this->accounts->getItems() as $account) {
                if (\Ced\Amazon\Model\Account::isCedcommerce(
                    $account->getData(\Ced\Amazon\Model\Account::COLUMN_ACCESS_KEY_ID)
                )) {
                    $account->setData(\Ced\Amazon\Model\Account::COLUMN_CEDCOMMERCE, 1);
                } else {
                    $account->setData(\Ced\Amazon\Model\Account::COLUMN_CEDCOMMERCE, 0);
                }
            }

            $this->accounts->save();
        }
    }
}
