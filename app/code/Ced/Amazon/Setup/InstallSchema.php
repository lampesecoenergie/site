<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @package Ced\Amazon\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (!$installer->getConnection()->isTableExists($installer->getTable(\Ced\Amazon\Model\Profile::NAME))) {

            /**
             * Create table 'amazon_profile'
             */
            $table = $installer->getConnection()->newTable($installer->getTable(\Ced\Amazon\Model\Profile::NAME))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )->addColumn(
                    'profile_code',
                    Table::TYPE_TEXT,
                    50,
                    [
                        'nullable' => false,
                        'default' => null
                    ],
                    'Profile Code'
                )
                ->addColumn(
                    'profile_status',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => true,
                        'default' => 1
                    ],
                    'Profile Status'
                )
                ->addColumn(
                    'profile_name',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => false,
                    ],
                    'Profile Name'
                )
                ->addColumn(
                    'profile_category',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => true,
                    ],
                    'Profile Category'
                )
                ->addColumn(
                    'profile_sub_category',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => true,
                    ],
                    'Profile Category'
                )
                ->addColumn(
                    'profile_required_attributes',
                    Table::TYPE_TEXT,
                    '2M',
                    [
                        'nullable' => true
                    ],
                    'Profile Required Attributes'
                )
                ->addColumn(
                    'profile_optional_attributes',
                    Table::TYPE_TEXT,
                    '2M',
                    [
                        'nullable' => true,
                    ],
                    'Profile Optional Attributes'
                )
                ->addColumn(
                    'magento_category',
                    Table::TYPE_TEXT,
                    200,
                    [
                        'nullable' => true,
                    ],
                    'Magento Category'
                )
                ->addIndex(
                    $setup->getIdxName(
                        $setup->getTable(\Ced\Amazon\Model\Profile::NAME),
                        ['profile_code'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['profile_code'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )->setComment('Profile Table');
            $installer->getConnection()->createTable($table);
        }

        //@TODO: REVIEW: Remove
        if (!$installer->getConnection()->isTableExists(
            $installer->getTable(\Ced\Amazon\Model\Profileproducts::NAME)
        )
        ) {
            /**
             * Create table 'amazon_profile'
             */
            $table = $installer->getConnection()->newTable(
                $installer->getTable(\Ced\Amazon\Model\Profileproducts::NAME)
            )
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )->addColumn(
                    'profile_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Profile Id'
                )->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Product ID'
                )->addColumn(
                    'amazon_item_id',
                    Table::TYPE_BIGINT,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Amazon Item ID'
                )
                ->addForeignKey(
                    $setup->getFkName(
                        \Ced\Amazon\Model\Profileproducts::NAME,
                        'profile_id',
                        $setup->getTable(\Ced\Amazon\Model\Profile::NAME),
                        'id'
                    ),
                    'profile_id',
                    $setup->getTable(\Ced\Amazon\Model\Profile::NAME),
                    'id',
                    Table::ACTION_CASCADE
                )
                ->addIndex(
                    $setup->getIdxName(
                        $setup->getTable(\Ced\Amazon\Model\Profileproducts::NAME),
                        ['profile_id', 'product_id'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['profile_id', 'product_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->setComment('Profile Products');

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->getConnection()->isTableExists($installer->getTable(\Ced\Amazon\Model\Feed::NAME))) {

            /**
             * amazon_feeds
             */
            $table = $installer->getConnection()->newTable($installer->getTable(\Ced\Amazon\Model\Feed::NAME))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'feed_id',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => true
                    ],
                    'Feed Id'
                )
                ->addIndex(
                    $installer->getIdxName($setup->getTable(\Ced\Amazon\Model\Feed::NAME), ['feed_id']),
                    ['feed_id']
                )
                ->addColumn(
                    'type',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => true,
                    ],
                    'Type'
                )
                ->addColumn(
                    'operation_type',
                    Table::TYPE_TEXT,
                    20,
                    [
                        'nullable' => true,
                    ],
                    'Operation Type'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    20,
                    [
                        'nullable' => true
                    ],
                    'Status'
                )
                ->addColumn(
                    'feed_file',
                    Table::TYPE_TEXT,
                    500,
                    [
                        'nullable' => true,
                    ],
                    'Feed File Path'
                )
                ->addColumn(
                    'response_file',
                    Table::TYPE_TEXT,
                    500,
                    [
                        'nullable' => true,
                    ],
                    'Response File Path'
                )
                ->addColumn(
                    'feed_created_date',
                    Table::TYPE_DATE,
                    null,
                    [
                        'nullable' => false
                    ],
                    'Feed Created Date'
                )
                ->addColumn(
                    'feed_executed_date',
                    Table::TYPE_DATE,
                    null,
                    [
                        'nullable' => false
                    ],
                    'Feed Executed Date'
                )
                ->addColumn(
                    'product_ids',
                    Table::TYPE_TEXT,
                    '2M',
                    [
                        'nullable' => true
                    ],
                    'Product IDs'
                )->setComment('Amazon Feeds');

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->getConnection()->isTableExists($installer->getTable(\Ced\Amazon\Model\Queue::NAME))) {
            /**
             * amazon_queue
             */
            $table = $installer->getConnection()->newTable($installer->getTable(\Ced\Amazon\Model\Queue::NAME))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'type',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => true,
                    ],
                    'Type'
                )
                ->addColumn(
                    'operation_type',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => true,
                    ],
                    'Operation Type'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    20,
                    [
                        'nullable' => true
                    ],
                    'Status'
                )
                ->addColumn(
                    'priorty',
                    Table::TYPE_TEXT,
                    50,
                    [
                        'nullable' => true,
                    ],
                    'Priorty'
                )
                ->addColumn(
                    'product_ids',
                    Table::TYPE_TEXT,
                    '2M',
                    [
                        'nullable' => true
                    ],
                    'Product IDs'
                )
                ->addColumn(
                    'depends',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => true
                    ],
                    'Depends on Feed Id'
                )
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    [
                        'nullable' => false,
                        'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
                    ],
                    'Created At'
                )
                ->addColumn(
                    'executed_at',
                    Table::TYPE_DATETIME,
                    null,
                    [
                        'nullable' => true
                    ],
                    'Executed At'
                )
                ->setComment('Amazon Queue');

            $installer->getConnection()->createTable($table);
        }

        // Creating `ced_amazon_order` table
        if (!$installer->getConnection()->isTableExists($installer->getTable(\Ced\Amazon\Model\Order::NAME))) {
            /**
             * Create table 'ced_amazon_order'
             */
            $table = $installer->getConnection()->newTable($installer->getTable(\Ced\Amazon\Model\Order::NAME))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )
                ->addColumn(
                    'amazon_order_id',
                    Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false,
                    ],
                    'Amazon Order Id'
                )
                ->addColumn(
                    'magento_order_id',
                    Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => true,
                        'default' => ''
                    ],
                    'Magento Order Id'
                )
                ->addColumn(
                    'magento_increment_id',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'default' => ''],
                    'Magento Increament Id'
                )
                ->addColumn(
                    'order_place_date',
                    Table::TYPE_DATE,
                    null,
                    ['nullable' => false],
                    'Order Place Date'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'Amazon Order Status'
                )
                ->addColumn(
                    'order_data',
                    Table::TYPE_TEXT,
                    '2M',
                    ['nullable' => true],
                    'Order Data'
                )
                ->addColumn(
                    'order_items',
                    Table::TYPE_TEXT,
                    '2M',
                    ['nullable' => true],
                    'Order Items'
                )
                ->addColumn(
                    'fulfillments',
                    Table::TYPE_TEXT,
                    '2M',
                    ['nullable' => true],
                    'Order Fulfillments'
                )
                ->addColumn(
                    'adjustments',
                    Table::TYPE_TEXT,
                    '2M',
                    ['nullable' => true],
                    'Order Adjustments'
                )
                ->addIndex(
                    $setup->getIdxName(
                        $setup->getTable(\Ced\Amazon\Model\Order::NAME),
                        [\Ced\Amazon\Model\Order::COLUMN_PO_ID],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    [\Ced\Amazon\Model\Order::COLUMN_PO_ID],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->setComment('Amazon Order');

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->getConnection()->isTableExists($installer->getTable("amazon_failed_order"))) {
            /**
             * Create table 'amazon_failed_order'
             */
            $table = $installer->getConnection()->newTable($installer->getTable("amazon_failed_order"))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )
                ->addColumn(
                    'amazon_order_id',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => false, 'default' => ''],
                    'Amazon Order Id'
                )
                ->addColumn(
                    'reason',
                    Table::TYPE_TEXT,
                    100,
                    ['nullable' => true, 'default' => ''],
                    'Failed Reason'
                )
                ->addColumn(
                    'order_date',
                    Table::TYPE_DATE,
                    null,
                    ['nullable' => false],
                    'Order Place Date'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    20,
                    ['nullable' => true, 'default' => ''],
                    'Amazon Order Status'
                )->setComment('Amazon Failed Orders');

            $installer->getConnection()->createTable($table);
        }

        // Get `ced_amazon_log` table
        $tableName = $setup->getTable(\Ced\Amazon\Model\Logs::NAME);
        // Check if the table already exists
        if (!$setup->getConnection()->isTableExists($tableName)) {
            $table = $setup->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'message',
                    Table::TYPE_TEXT,
                    null,
                    [
                        'nullable' => true
                    ],
                    'Message'
                )
                ->addColumn(
                    'context',
                    Table::TYPE_TEXT,
                    '2M',
                    [
                        'nullable' => true
                    ],
                    'Context'
                )
                ->addColumn(
                    'level',
                    Table::TYPE_TEXT,
                    null,
                    [
                        'nullable' => true
                    ],
                    'Level'
                )
                ->addColumn(
                    'level_name',
                    Table::TYPE_TEXT,
                    null,
                    [
                        'nullable' => true
                    ],
                    'Level Name'
                )
                ->addColumn(
                    'channel',
                    Table::TYPE_TEXT,
                    null,
                    [
                        'nullable' => true
                    ],
                    'Channel'
                )
                ->addColumn(
                    'datetime',
                    Table::TYPE_DATETIME,
                    null,
                    [
                        'nullable' => true
                    ],
                    'Date'
                )
                ->setComment('Amazon Logs');
            $setup->getConnection()->createTable($table);
        }
    }
}
