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
use Mirasvit\Feed\Api\Data\ValidationInterface;

class UpgradeSchema104 implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $table = $setup->getConnection()->newTable(
            $setup->getTable(ValidationInterface::TABLE_NAME)
        )->addColumn(
            ValidationInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Validation Id'
        )->addColumn(
            ValidationInterface::FEED_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false],
            'Feed ID'
        )->addColumn(
            ValidationInterface::LINE_NUM,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false],
            'Line Number'
        )->addColumn(
            ValidationInterface::ENTITY_ID,
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => true],
            'Entity ID'
        )->addColumn(
            ValidationInterface::VALIDATOR,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Validator'
        )->addColumn(
            ValidationInterface::ATTRIBUTE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Attribute'
        )->addColumn(
            ValidationInterface::VALUE,
            Table::TYPE_TEXT,
            '64K',
            ['nullable' => true],
            'Value'
        )->addIndex(
            $setup->getIdxName(ValidationInterface::TABLE_NAME, [ValidationInterface::LINE_NUM]),
            ValidationInterface::LINE_NUM
        )->addIndex(
            $setup->getIdxName(ValidationInterface::TABLE_NAME, [ValidationInterface::ENTITY_ID]),
            ValidationInterface::ENTITY_ID
        )->addIndex(
            $setup->getIdxName(ValidationInterface::TABLE_NAME, [ValidationInterface::ATTRIBUTE]),
            ValidationInterface::ATTRIBUTE
        )->addIndex(
            $setup->getIdxName(ValidationInterface::TABLE_NAME, [ValidationInterface::VALIDATOR]),
            ValidationInterface::VALIDATOR
        )->addForeignKey(
            $setup->getFkName(
                ValidationInterface::TABLE_NAME,
                ValidationInterface::FEED_ID,
                'mst_feed_feed',
                'feed_id'
            ),
            ValidationInterface::FEED_ID,
            $setup->getTable('mst_feed_feed'),
            'feed_id',
            Table::ACTION_CASCADE
        );

        $setup->getConnection()->createTable($table);
    }
}
