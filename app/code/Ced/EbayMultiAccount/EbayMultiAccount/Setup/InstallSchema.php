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
 * @package     Ced_EbayMultiAccount
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */


namespace Ced\EbayMultiAccount\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class InstallSchema
 * @package Ced\EbayMultiAccount\Setup
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

        $tableName = $installer->getTable('ebaymultiaccount_profile');
        if ($setup->getConnection()->isTableExists($tableName) != true) {
            /**
             * Create table 'ebaymultiaccount_profile'
             */
            $table = $installer->getConnection()->newTable($tableName)
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )->addColumn(
                'profile_code',
                Table::TYPE_TEXT,
                50,
                ['nullable' => false, 'default' => ''],
                'Profile Code'
            )
            ->addColumn(
                'profile_status',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true, 'default' => 1],
                'Profile Status'
            )
            ->addColumn(
                'profile_name',
                Table::TYPE_TEXT,
                50,
                ['nullable' => false, 'default' => ''],
                'Profile Name'
            )
            ->addColumn(
                'profile_category',
                Table::TYPE_TEXT,
                500,
                ['nullable' => true, 'default' => ''],
                'Profile Category'
            )
            ->addColumn(
                'profile_cat_attribute',
                Table::TYPE_TEXT,
                500,
                ['nullable' => true, 'default' => ''],
                'Profile Category Attribute'
            )
            ->addColumn(
                'profile_req_opt_attribute',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => ''],
                'Profile Required And Optional Attribute'
            )
            ->addColumn(
                'profile_cat_feature',
                Table::TYPE_TEXT,
                50,
                ['nullable' => true, 'default' => ''],
                'Profile Category Feature'
            )
            ->addColumn(
                'account_configuration_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => true],
                'Account Configuration'
            )
            ->addColumn(
                'account_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => true],
                'Account Id'
            )->addIndex(
                $setup->getIdxName(
                    'ebaymultiaccount_profile',
                    ['profile_code'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['profile_code'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )->setComment('Profile Table')->setOption('type', 'InnoDB')->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('ebaymultiaccount_orders');
        if ($setup->getConnection()->isTableExists($tableName) != true) {
            /**
             * Create table 'ebaymultiaccount_orders'
             */
            $table = $installer->getConnection()->newTable($tableName)
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'ebaymultiaccount_order_id',
                Table::TYPE_TEXT,
                50,
                ['nullable' => false, 'default' => ''],
                'eBay Order Id'
            )
            ->addColumn(
                'magento_order_id',
                Table::TYPE_TEXT,
                50,
                ['nullable' => false, 'default' => ''],
                'Magento Order Id'
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
                100,
                ['nullable' => true, 'default' => ''],
                'eBay Order Status'
            )
            ->addColumn(
                'order_data',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => ''],
                'Order Data'
            )
            ->addColumn(
                'shipment_data',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => ''],
                'Order Shipment Data'
            )
            ->addColumn(
                'ebaymultiaccount_record_no',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => '', 'unsigned' => true],
                'eBay Record Number'
            )
            ->addColumn(
                'magento_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => true, 'default' => 0, 'unsigned' => true],
                'Magento Order Id'
            )
            ->addColumn(
                'failed_order_reason',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => '', 'unsigned' => true],
                'Failed Order Reason'
            )
            ->addColumn(
                'account_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => true],
                'Account Id'
            )->setComment('eBay Orders')->setOption('type', 'InnoDB')->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }

        $tableName = $setup->getTable('ebaymultiaccount_product_change');
        if ($setup->getConnection()->isTableExists($tableName) != true) {
            /**
             * Create table 'ebaymultiaccount_product_change'
             */
            $table = $setup->getConnection()->newTable($tableName)
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
                    'product_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Profile Status'
                )
                ->addColumn(
                    'old_value',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'Old Value'
                )
                ->addColumn(
                    'new_value',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'New Value'
                )
                ->addColumn(
                    'action',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'Action'
                )
                ->addColumn(
                    'cron_type',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'Cron type'
                )
                ->setComment('eBay Product Change')->setOption('type', 'InnoDB')->setOption('charset', 'utf8');

            $setup->getConnection()->createTable($table);
        }

        if (!$setup->getConnection()->isTableExists($setup->getTable('ebaymultiaccount_job_scheduler'))) {
            $table = $setup->getConnection()
                ->newTable($setup->getTable('ebaymultiaccount_job_scheduler'))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                        'auto_increment' => true,
                    ],
                    'Id'
                )
                ->addColumn(
                    'product_ids',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                        'default' => null
                    ),
                    'Product Ids'
                )
                ->addColumn(
                    'start_time',
                    Table::TYPE_DATETIME,
                    null,
                    array(
                        'nullable' => false,
                    ),
                    'Start Time'
                )
                ->addColumn(
                    'finish_time',
                    Table::TYPE_DATETIME,
                    null,
                    array(
                        'nullable' => false,
                    ),
                    'Finish Time'
                )
                ->addColumn(
                    'cron_status',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => true,
                        'default' => null
                    ),
                    'Cron Status'
                )
                ->addColumn(
                    'scheduler_type',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => true,
                        'default' => null
                    ),
                    'Scheduler Type'
                )
                ->addColumn(
                    'feed_file_path',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                        'default' => null
                    ),
                    'Path Of Feed File'
                )
                ->addColumn(
                    'error',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => true,
                        'default' => null
                    ),
                    'Errors'
                )
                ->addColumn(
                    'threshold_limit',
                    Table::TYPE_INTEGER,
                    null,
                    array(
                        'nullable'  => false,
                        'comment' => 'Threshold Limit'
                    ),
                    'Threshold Limit'
                )
                ->addColumn(
                    'account_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true],
                    'Account Id'
                );
            $setup->getConnection()->createTable($table);
            $setup->endSetup();
        }

        if (!$setup->getConnection()->isTableExists($setup->getTable('ebaymultiaccount_job_feed_details'))) {
            $setup->startSetup();
            $table = $setup->getConnection()
                ->newTable($setup->getTable('ebaymultiaccount_job_feed_details'))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    array(
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                        'auto_increment' => true,
                    ),
                    'Id'
                )
                ->addColumn(
                    'feed_file_path',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                        'default' => null
                    ),
                    'Feed File Path'
                )
                ->addColumn(
                    'report_feed_file_path',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable'  => false,
                        'default' => null,
                    ),
                    'Report Feed File Path'
                )
                ->addColumn(
                    'product_ids',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                        'default' => null
                    ),
                    'Product Ids'
                )
                ->addColumn(
                    'unprocessed_product_ids',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                        'default' => null
                    ),
                    'Unprocessed Product Ids'
                )
                ->addColumn(
                    'job_id',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => true,
                        'default' => null
                    ),
                    'Job ID'
                )
                ->addColumn(
                    'job_reference_id',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => true,
                        'default' => null
                    ),
                    'Job Reference ID'
                )
                ->addColumn(
                    'download_job_id',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable'  => false,
                        'default' => null,
                    ),
                    'Download Job Id'
                )
                ->addColumn(
                    'report_file_reference_id',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable'  => false,
                        'default' => null,
                    ),
                    'Report File Reference Id'
                )
                ->addColumn(
                    'job_status',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => true,
                        'default' => null
                    ),
                    'Job Status'
                )
                ->addColumn(
                    'job_type',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => true,
                        'default' => null
                    ),
                    'Job Type'
                )
                ->addColumn(
                    'start_time',
                    Table::TYPE_DATETIME,
                    null,
                    array(
                        'nullable' => true,
                        'default' => null
                    ),
                    'Start Time'
                )
                ->addColumn(
                    'completion_time',
                    Table::TYPE_DATETIME,
                    null,
                    array(
                        'nullable' => true,
                        'default' => null
                    ),
                    'Completion Time'
                )
                ->addColumn(
                    'job_complete_percentage',
                    Table::TYPE_TEXT,
                    10,
                    array(
                        'nullable'  => false,
                        'default' => null,
                    ),
                    'Job Complete Percentage'
                )
                ->addColumn(
                    'response',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => true,
                        'default' => null
                    ),
                    'Response'
                )
                ->addColumn(
                    'threshold_limit',
                    Table::TYPE_INTEGER,
                    null,
                    array(
                        'nullable'  => false,
                        'comment' => 'Threshold Limit'
                    ),
                    'Threshold Limit'
                )
                ->addColumn(
                    'account_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true],
                    'Account Id'
                );
            $setup->getConnection()->createTable($table);
            $setup->endSetup();
        }

        if (!$setup->getConnection()->isTableExists($setup->getTable('ebaymultiaccount_feeds'))) {
            $setup->startSetup();
            $table = $setup->getConnection()->newTable($setup->getTable('ebaymultiaccount_feeds'))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    array(
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                        'auto_increment' => true,
                    ),
                    'Id'
                )
                ->addColumn(
                    'feed_type',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                        'default' => null
                    ),
                    'Feed Type'
                )
                ->addColumn(
                    'feed_source',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                        'default' => null
                    ),
                    'Feed Source'
                )
                ->addColumn(
                    'feed_date',
                    Table::TYPE_DATETIME,
                    null,
                    array(
                        'nullable' => true,
                        'default' => null
                    ),
                    'Feed Date'
                )
                ->addColumn(
                    'feed_file',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                        'default' => null
                    ),
                    'Upload File Path'
                )
                ->addColumn(
                    'feed_errors',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => true,
                        'default' => null
                    ),
                    'Feed Errors'
                )
                ->addColumn(
                    'account_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true],
                    'Account Id'
                );
            $setup->getConnection()->createTable($table);
            $setup->endSetup();
        }

        if (!$setup->getConnection()->isTableExists($setup->getTable('ebaymultiaccount_accounts'))) {
            $setup->startSetup();
            $table = $setup->getConnection()->newTable($setup->getTable('ebaymultiaccount_accounts'))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    array(
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                        'auto_increment' => true,
                    ),
                    'Id'
                )
                ->addColumn(
                    'account_code',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'default' => ''],
                    'Account Code'
                )
                ->addColumn(
                    'account_env',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => ''],
                    'Account Environment'
                )
                ->addColumn(
                    'account_location',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'Account Location'
                )
                ->addColumn(
                    'account_store',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'Account Store'
                )
                ->addColumn(
                    'account_status',
                    Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => true,],
                    'Account Status'
                )
                ->addColumn(
                    'account_token',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'Account Token'
                );
            $setup->getConnection()->createTable($table);
            $setup->endSetup();
        }

        if (!$setup->getConnection()->isTableExists($setup->getTable('ebaymultiaccount_configuration'))) {
            $setup->startSetup();
            $table = $setup->getConnection()->newTable($setup->getTable('ebaymultiaccount_configuration'))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    array(
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                        'auto_increment' => true,
                    ),
                    'Id'
                )
                ->addColumn(
                    'config_name',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'default' => ''],
                    'Configuration Name'
                )
                ->addColumn(
                    'account_location',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true],
                    'Account Location'
                )
                ->addColumn(
                    'shipping_details',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'Account Sipping Details'
                )
                ->addColumn(
                    'payment_details',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'Account Payment Details'
                )
                ->addColumn(
                    'return_policy',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'Account Return Policy'
                )
                ->addColumn(
                    'product_setting',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'Account Product Setting'
                )
                ->addColumn(
                    'order_setting',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'Account Order Setting'
                )
                ->addColumn(
                    'attribute_mapping',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'Account Attribute Mapping'
                );
            $setup->getConnection()->createTable($table);
            $setup->endSetup();
        }
    }
}
