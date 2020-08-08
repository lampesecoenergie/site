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
 * @category  Ced
 * @package   Ced_Cdiscount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 *
 * @package Ced\Cdiscount\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'cdiscount_profile'
         */
        $table = $installer->getConnection()->newTable($installer->getTable('cdiscount_profile'))
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
                'model_name',
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => true,
                ],
                'Model Name'
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
                    'cdiscount_profile',
                    ['profile_code'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['profile_code'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )->setComment('Profile Table')->setOption('type', 'InnoDB')->setOption('charset', 'utf8');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'cdiscount_profile'
         */
        $table = $installer->getConnection()->newTable($installer->getTable('cdiscount_profile_products'))
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
                'cdiscount_item_id',
                Table::TYPE_BIGINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Cdiscount Item ID'
            )
            ->addForeignKey(
                $setup->getFkName('cdiscount_profile_products', 'profile_id', 'cdiscount_profile', 'id'),
                'profile_id',
                $setup->getTable('cdiscount_profile'),
                'id',
                Table::ACTION_CASCADE
            )
            ->addIndex(
                $setup->getIdxName(
                    'cdiscount_profile_products',
                    ['profile_id', 'product_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['profile_id', 'product_id'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $setup->getIdxName(
                    'cdiscount_profile_products',
                    ['product_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['product_id'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )->setComment('Profile Products Table')->setOption('type', 'InnoDB')->setOption('charset', 'utf8');
        $installer->getConnection()->createTable($table);

        /**
         * cdiscount_feeds
         */
        $table = $installer->getConnection()->newTable($installer->getTable('cdiscount_feeds'))
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
                'feed_response',
                Table::TYPE_TEXT,
                '2M',
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
            )->setComment('Cdiscount Feeds')->setOption('type', 'InnoDB')->setOption('charset', 'utf8');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'cdiscount_orders'
         */
        $table = $installer->getConnection()->newTable($installer->getTable('cdiscount_orders'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'cdiscount_order_id',
                Table::TYPE_TEXT,
                50,
                ['nullable' => false, 'default' => ''],
                'Cdiscount Order Id'
            )
            ->addColumn(
                'magento_order_id',
                Table::TYPE_TEXT,
                50,
                ['nullable' => false, 'default' => ''],
                'Magento Order Id'
            )
            ->addColumn(
                'increment_id',
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
                20,
                ['nullable' => true, 'default' => ''],
                'Cdiscount Order Status'
            )
            ->addColumn(
                'order_data',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Order Data'
            )
            ->addColumn(
                'order_items',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Order Data'
            )
            ->addColumn(
                'shipments',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => ''],
                'Order Shipment Data'
            )
            ->addColumn(
                'cancellations',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => ''],
                'Order Cancellation Data'
            )
            ->setComment('Cdiscount Orders')->setOption('type', 'InnoDB')->setOption('charset', 'utf8');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'cdiscount_failed_order'
         */
        $table = $installer->getConnection()->newTable($installer->getTable('cdiscount_failed_orders'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'cdiscount_order_id',
                Table::TYPE_TEXT,
                50,
                ['nullable' => false, 'default' => ''],
                'Cdiscount Order Id'
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
            )->addColumn(
                'order_data',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Order Data'
            )->addColumn(
                'order_items',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Order Items'
            )->addColumn(
                'cancellations',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Cancellations'
            )->addColumn(
                'shipments',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Shipments'
            )
            ->addColumn(
                'status',
                Table::TYPE_TEXT,
                20,
                ['nullable' => true, 'default' => ''],
                'Cdiscount Order Status'
            )->setComment('Cdiscount Failed Orders')->setOption('type', 'InnoDB')->setOption('charset', 'utf8');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'cdiscount_failed_order'
         */
        $table = $installer->getConnection()->newTable($installer->getTable('cdiscount_categories'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => ''],
                'Cdiscount Category Name'
            )->addColumn(
                'path',
                Table::TYPE_TEXT,
                '1M',
                ['nullable' => true, 'default' => ''],
                'Cdiscount Category Path'
            )
            ->addColumn(
                'code',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => ''],
                'Cdiscount Category Value'
            )->addColumn(
                'is_variant_allowed',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => ''],
                'Cdiscount Variation Allowed'
            )->addColumn(
                'is_simple_allowed',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => ''],
                'Cdiscount Simple Allowed'
            )->setComment('Cdiscount Categories')->setOption('type', 'InnoDB')->setOption('charset', 'utf8');
        $installer->getConnection()->createTable($table);
    }
}
