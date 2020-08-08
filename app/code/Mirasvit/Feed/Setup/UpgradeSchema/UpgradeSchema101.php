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

class UpgradeSchema101 implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->getConnection()->dropTable($setup->getTable('mst_feed_report'));

        $table = $setup->getConnection()->newTable(
            $setup->getTable('mst_feed_report')
        )->addColumn(
            'row_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Row Id'
        )->addColumn(
            'session',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Session'
        )->addColumn(
            'feed_id',
            Table::TYPE_INTEGER,
            11,
            ['nullable' => false],
            'Feed Id'
        )->addColumn(
            'product_id',
            Table::TYPE_INTEGER,
            11,
            ['nullable' => true],
            'Product Id'
        )->addColumn(
            'order_id',
            Table::TYPE_INTEGER,
            11,
            ['nullable' => true],
            'Order Id'
        )->addColumn(
            'is_click',
            Table::TYPE_INTEGER,
            1,
            ['nullable' => false, 'default' => 0],
            'Is Click?'
        )->addColumn(
            'subtotal',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => true],
            'Order subtotal (for product)'
        )->addColumn(
            'store_id',
            Table::TYPE_INTEGER,
            11,
            ['nullable' => false],
            'Store Id'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Created At'
        )->addIndex(
            $setup->getIdxName('mst_feed_report', ['product_id']),
            ['product_id']
        )->addIndex(
            $setup->getIdxName('mst_feed_report', ['feed_id']),
            ['feed_id']
        )->addIndex(
            $setup->getIdxName('mst_feed_report', ['session']),
            ['session']
        )->addIndex(
            $setup->getIdxName('mst_feed_report', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $setup->getFkName(
                'mst_feed_report',
                'feed_id',
                'mst_feed_feed',
                'feed_id'
            ),
            'feed_id',
            $setup->getTable('mst_feed_feed'),
            'feed_id',
            Table::ACTION_CASCADE
        )->setComment('Feed Report');

        $setup->getConnection()->createTable($table);
    }
}
