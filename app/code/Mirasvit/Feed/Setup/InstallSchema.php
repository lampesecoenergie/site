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



namespace Mirasvit\Feed\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
        $table = $installer->getConnection()->newTable(
            $installer->getTable('mst_feed_custom_attribute')
        )->addColumn(
            'attribute_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Attribute Id'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Name'
        )->addColumn(
            'code',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Code'
        )->addColumn(
            'conditions_serialized',
            Table::TYPE_TEXT,
            '64K',
            ['unsigned' => false, 'nullable' => false],
            'Conditions Serialized'
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('mst_feed_feed')
        )->addColumn(
            'feed_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Feed Id'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Name'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            5,
            ['unsigned' => true, 'nullable' => false],
            'Store Id'
        )->addColumn(
            'filename',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Filename'
        )->addColumn(
            'type',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Type'
        )->addColumn(
            'format_serialized',
            Table::TYPE_TEXT,
            '64K',
            ['unsigned' => false, 'nullable' => true],
            'Format'
        )->addColumn(
            'is_active',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => 0],
            'Is Active'
        )->addColumn(
            'generated_at',
            Table::TYPE_DATETIME,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Generated At'
        )->addColumn(
            'generated_cnt',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Generated Cnt'
        )->addColumn(
            'generated_time',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Generated Time'
        )->addColumn(
            'cron',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => 0],
            'Cron'
        )->addColumn(
            'cron_day',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => true],
            'Cron Day'
        )->addColumn(
            'cron_time',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => true],
            'Cron Time'
        )->addColumn(
            'ftp',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => 0],
            'Ftp'
        )->addColumn(
            'ftp_protocol',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => true],
            'Ftp Protocol'
        )->addColumn(
            'ftp_host',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => true],
            'Ftp Host'
        )->addColumn(
            'ftp_user',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => true],
            'Ftp User'
        )->addColumn(
            'ftp_password',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => true],
            'Ftp Password'
        )->addColumn(
            'ftp_path',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => true],
            'Ftp Path'
        )->addColumn(
            'ftp_passive_mode',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Ftp Passive Mode'
        )->addColumn(
            'uploaded_at',
            Table::TYPE_DATETIME,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Uploaded At'
        )->addColumn(
            'created_at',
            Table::TYPE_DATETIME,
            null,
            ['unsigned' => false, 'nullable' => false],
            'Created At'
        )->addColumn(
            'updated_at',
            Table::TYPE_DATETIME,
            null,
            ['unsigned' => false, 'nullable' => false],
            'Updated At'
        )->addColumn(
            'ga_source',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => true],
            'Ga Source'
        )->addColumn(
            'ga_medium',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => true],
            'Ga Medium'
        )->addColumn(
            'ga_name',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => true],
            'Ga Name'
        )->addColumn(
            'ga_term',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => true],
            'Ga Term'
        )->addColumn(
            'ga_content',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => true],
            'Ga Content'
        )->addColumn(
            'notification_emails',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => true],
            'Notification Emails'
        )->addColumn(
            'notification_events',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => true],
            'Notifications Events'
        )->addColumn(
            'export_only_new',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Export only new'
        )->addColumn(
            'report_enabled',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Report Enabled?'
        )->addColumn(
            'allowed_chars',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => true],
            'Allowed Chars'
        )->addColumn(
            'ignored_chars',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => true],
            'Ignored Chars'
        )->addColumn(
            'archivation',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => true],
            'Archivation'
        )->addIndex(
            $installer->getIdxName('mst_feed_feed', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName(
                'mst_feed_feed',
                'store_id',
                'store',
                'store_id'
            ),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('mst_feed_feed_history')
        )->addColumn(
            'history_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            'History Id'
        )->addColumn(
            'feed_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false],
            'Feed Id'
        )->addColumn(
            'type',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => true],
            'Type'
        )->addColumn(
            'title',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Title'
        )->addColumn(
            'message',
            Table::TYPE_TEXT,
            '64K',
            ['unsigned' => false, 'nullable' => true],
            'Message'
        )->addColumn(
            'created_at',
            Table::TYPE_DATETIME,
            null,
            ['unsigned' => false, 'nullable' => false],
            'Created At'
        )->addColumn(
            'updated_at',
            Table::TYPE_DATETIME,
            null,
            ['unsigned' => false, 'nullable' => false],
            'Updated At'
        )->addIndex(
            $installer->getIdxName('mst_feed_feed_history', ['feed_id']),
            ['feed_id']
        )->addForeignKey(
            $installer->getFkName(
                'mst_feed_feed_history',
                'feed_id',
                'mst_feed_feed',
                'feed_id'
            ),
            'feed_id',
            $installer->getTable('mst_feed_feed'),
            'feed_id',
            Table::ACTION_CASCADE
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('mst_feed_feed_product')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Id'
        )->addColumn(
            'feed_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false],
            'Feed Id'
        )->addColumn(
            'product_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Product Id'
        )->addColumn(
            'is_new',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Is New'
        )->addIndex(
            $installer->getIdxName('mst_feed_feed_product', ['product_id']),
            ['product_id']
        )->addForeignKey(
            $installer->getFkName(
                'mst_feed_feed_product',
                'feed_id',
                'mst_feed_feed',
                'feed_id'
            ),
            'feed_id',
            $installer->getTable('mst_feed_feed'),
            'feed_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'mst_feed_feed_product',
                'product_id',
                'catalog_product_entity',
                'entity_id'
            ),
            'product_id',
            $installer->getTable('catalog_product_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        )->addIndex(
            $installer->getIdxName(
                'mst_feed_feed_product',
                ['feed_id', 'product_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['feed_id', 'product_id'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('mst_feed_mapping_category')
        )->addColumn(
            'mapping_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Mapping Id'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Name'
        )->addColumn(
            'type',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Type'
        )->addColumn(
            'mapping_serialized',
            Table::TYPE_TEXT,
            '64K',
            ['unsigned' => false, 'nullable' => false],
            'Mapping Serialized'
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('mst_feed_rule')
        )->addColumn(
            'rule_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Rule Id'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Name'
        )->addColumn(
            'type',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Type'
        )->addColumn(
            'conditions_serialized',
            Table::TYPE_TEXT,
            '64K',
            ['unsigned' => false, 'nullable' => false],
            'Conditions Serialized'
        )->addColumn(
            'actions_serialized',
            Table::TYPE_TEXT,
            '64K',
            ['unsigned' => false, 'nullable' => false],
            'Actions Serialized'
        )->addColumn(
            'is_active',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => 0],
            'Is Active'
        )->addColumn(
            'created_at',
            Table::TYPE_DATETIME,
            null,
            ['unsigned' => false, 'nullable' => false],
            'Created At'
        )->addColumn(
            'updated_at',
            Table::TYPE_DATETIME,
            null,
            ['unsigned' => false, 'nullable' => false],
            'Updated At'
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('mst_feed_rule_feed')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Id'
        )->addColumn(
            'rule_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 0],
            'Rule Id'
        )->addColumn(
            'feed_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => 0],
            'Feed Id'
        )->addIndex(
            $installer->getIdxName('mst_feed_rule_feed', ['feed_id']),
            ['feed_id']
        )->addForeignKey(
            $installer->getFkName(
                'mst_feed_rule_feed',
                'feed_id',
                'mst_feed_feed',
                'feed_id'
            ),
            'feed_id',
            $installer->getTable('mst_feed_feed'),
            'feed_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'mst_feed_rule_feed',
                'rule_id',
                'mst_feed_rule',
                'rule_id'
            ),
            'rule_id',
            $installer->getTable('mst_feed_rule'),
            'rule_id',
            Table::ACTION_CASCADE
        )->addIndex(
            $installer->getIdxName(
                'mst_feed_rule_feed',
                ['rule_id', 'feed_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['rule_id', 'feed_id'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('mst_feed_rule_product')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Id'
        )->addColumn(
            'rule_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 0],
            'Rule Id'
        )->addColumn(
            'product_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 0],
            'Product Id'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            5,
            ['unsigned' => true, 'nullable' => false, 'default' => 0],
            'Store Id'
        )->addIndex(
            $installer->getIdxName('mst_feed_rule_product', ['store_id']),
            ['store_id']
        )->addIndex(
            $installer->getIdxName('mst_feed_rule_product', ['product_id']),
            ['product_id']
        )->addForeignKey(
            $installer->getFkName(
                'mst_feed_rule_product',
                'rule_id',
                'mst_feed_rule',
                'rule_id'
            ),
            'rule_id',
            $installer->getTable('mst_feed_rule'),
            'rule_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'mst_feed_rule_product',
                'store_id',
                'store',
                'store_id'
            ),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        )->addIndex(
            $installer->getIdxName(
                'mst_feed_rule_product',
                ['rule_id', 'product_id', 'store_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['rule_id', 'product_id', 'store_id'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('mst_feed_template')
        )->addColumn(
            'template_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Template Id'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Name'
        )->addColumn(
            'type',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Type'
        )->addColumn(
            'format_serialized',
            Table::TYPE_TEXT,
            '64K',
            ['unsigned' => false, 'nullable' => true],
            'Format'
        )->addColumn(
            'created_at',
            Table::TYPE_DATETIME,
            null,
            ['unsigned' => false, 'nullable' => false],
            'Created At'
        )->addColumn(
            'updated_at',
            Table::TYPE_DATETIME,
            null,
            ['unsigned' => false, 'nullable' => false],
            'Updated At'
        );
        $installer->getConnection()->createTable($table);
    }
}
