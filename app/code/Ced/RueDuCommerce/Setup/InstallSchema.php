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
 * @package   Ced_RueDuCommerce
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\RueDuCommerce\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 *
 * @package Ced\RueDuCommerce\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'rueducommerce_profile'
         */
        $table = $installer->getConnection()->newTable($installer->getTable('rueducommerce_profile'))
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
                'profile_categories',
                Table::TYPE_TEXT,
                500,
                [
                    'nullable' => true,
                ],
                'Profile Categories'
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
                'profile_logistic_class',
                Table::TYPE_TEXT,
                '2M',
                [
                    'nullable' => true,
                ],
                'Profile Logistic Class'
            )
            ->addColumn(
                'profile_tax_au',
                Table::TYPE_TEXT,
                '2M',
                [
                    'nullable' => true,
                ],
                'Profile Tax Au'
            )
            ->addColumn(
                'profile_offer_state',
                Table::TYPE_TEXT,
                '2M',
                [
                    'nullable' => true,
                ],
                'Profile OfferState'
            )
            ->addColumn(
                'profile_rueducommerce_club_eligible',
                Table::TYPE_TEXT,
                '2M',
                [
                    'nullable' => true,
                ],
                'Profile RueDuCommerce Club Eligible'
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
                    'rueducommerce_profile',
                    ['profile_code'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['profile_code'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )->setComment('Profile Table')->setOption('type', 'InnoDB')->setOption('charset', 'utf8');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'rueducommerce_profile'
         */
        $table = $installer->getConnection()->newTable($installer->getTable('rueducommerce_profile_products'))
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
                'rueducommerce_item_id',
                Table::TYPE_BIGINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'RueDuCommerce Item ID'
            )
            ->addForeignKey(
                $setup->getFkName('rueducommerce_profile_products', 'profile_id', 'rueducommerce_profile', 'id'),
                'profile_id',
                $setup->getTable('rueducommerce_profile'),
                'id',
                Table::ACTION_CASCADE
            )
            ->addIndex(
                $setup->getIdxName(
                    'rueducommerce_profile_products',
                    ['profile_id', 'product_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['profile_id', 'product_id'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $setup->getIdxName(
                    'rueducommerce_profile_products',
                    ['product_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['product_id'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )->setComment('Profile Products Table')->setOption('type', 'InnoDB')->setOption('charset', 'utf8');

        $installer->getConnection()->createTable($table);

        /**
         * rueducommerce_feeds
         */
        $table = $installer->getConnection()->newTable($installer->getTable('rueducommerce_feeds'))
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
                'transform_lines_in_error',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => true
                ],
                'Transform Lines In Error'
            )
            ->addColumn(
                'transform_lines_in_error',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => true
                ],
                'Transform Lines In Error'
            )
            ->addColumn(
                'transform_lines_in_success',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => true
                ],
                'Transform Lines In success'
            )
            ->addColumn(
                'transform_lines_read',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => true
                ],
                'Transform Lines Read'
            )
            ->addColumn(
                'transform_lines_with_warning',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => true
                ],
                'Transform Lines Warning'
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
            )->setComment('RueDuCommerce Feeds')->setOption('type', 'InnoDB')->setOption('charset', 'utf8');

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'rueducommerce_orders'
         */
        $table = $installer->getConnection()->newTable($installer->getTable('rueducommerce_orders'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'rueducommerce_order_id',
                Table::TYPE_TEXT,
                50,
                ['nullable' => false, 'default' => ''],
                'RueDuCommerce Order Id'
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
                30,
                ['nullable' => true, 'default' => ''],
                'RueDuCommerce Order Status'
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
            ->setComment('RueDuCommerce Orders')->setOption('type', 'InnoDB')->setOption('charset', 'utf8');

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'rueducommerce_failed_order'
         */
        $table = $installer->getConnection()->newTable($installer->getTable('rueducommerce_failed_orders'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'rueducommerce_order_id',
                Table::TYPE_TEXT,
                50,
                ['nullable' => false, 'default' => ''],
                'RueDuCommerce Order Id'
            )
            ->addColumn(
                'reason',
                Table::TYPE_TEXT,
                null,
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
                'RueDuCommerce Order Status'
            )->setComment('RueDuCommerce Failed Orders')->setOption('type', 'InnoDB')->setOption('charset', 'utf8');

        $installer->getConnection()->createTable($table);
    }
}
