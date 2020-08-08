<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.0.103
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Feed\Setup\UpgradeSchema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema105 implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->getConnection()->changeColumn(
            $setup->getTable('mst_feed_mapping_category'),
            'mapping_serialized',
            'mapping_serialized',
            [
                'type'     => Table::TYPE_TEXT,
                'length'   => 16777217,
                'nullable' => false,
            ]
        );

        $setup->getConnection()->changeColumn(
            $setup->getTable('mst_feed_dynamic_variable'),
            'php_code',
            'php_code',
            [
                'type'     => Table::TYPE_TEXT,
                'length'   => 16777217,
                'nullable' => true,
            ]
        );
    }
}
