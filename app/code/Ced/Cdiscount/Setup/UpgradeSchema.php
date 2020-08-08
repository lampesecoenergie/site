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

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '0.0.4', '<')) {
            // Get module table
            $tableName = $setup->getTable('cdiscount_product_change');
            if ($setup->getConnection()->isTableExists($tableName)) {
                $setup->getConnection()->dropTable($tableName);
            }
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
                        'product_id',
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'unsigned' => true,
                            'nullable' => false
                        ],
                        'Product Id'
                    )
                    ->addColumn(
                        'action',
                        Table::TYPE_TEXT,
                        50,
                        ['nullable' => true, 'default' => ''],
                        'Action'
                    )
                    ->addColumn(
                        'type',
                        Table::TYPE_TEXT,
                        50,
                        ['nullable' => true, 'default' => ''],
                        'Type'
                    )
                    ->setComment('Cdiscount Product Change')
                    ->setOption('type', 'InnoDB')
                    ->setOption('charset', 'utf8');
                $setup->getConnection()->createTable($table);
            }

            $tableName = $setup->getTable('cdiscount_categories');
            $connection = $setup->getConnection();

            if (!$connection->tableColumnExists($tableName, 'is_variant_allowed')) {
                $connection->addColumn(
                    $tableName,
                    'is_variant_allowed',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'Variant Allowed',
                        'after' => 'code'
                    ]
                );
            }

            if (!$connection->tableColumnExists($tableName, 'is_simple_allowed')) {
                $connection->addColumn(
                    $tableName,
                    'is_simple_allowed',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'Simple Allowed',
                        'after' => 'is_variant_allowed'
                    ]
                );
            }

            $tableName = $setup->getTable('cdiscount_profile');
            $connection = $setup->getConnection();

            if (!$connection->tableColumnExists($tableName, 'profile_products_filters')) {
                $connection->addColumn(
                    $tableName,
                    'profile_products_filters',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'Products Filters',
                        'after' => 'profile_optional_attributes'
                    ]
                );
            }

            // Check if the table already exists
            if ($setup->getConnection()->isTableExists('ced_logs') != true) {
                // Create ced_logs table
                $table = $setup->getConnection()
                    ->newTable('ced_logs')
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
                    ->setComment('Cedcommerce Logs');
                $setup->getConnection()->createTable($table);
            }
        }

        if (version_compare($context->getVersion(), '0.0.5', '<')) {
            $tableName = $setup->getTable('cdiscount_categories');
            $connection = $setup->getConnection();

            if (!$connection->tableColumnExists($tableName, 'ean_optional')) {
                $connection->addColumn(
                    $tableName,
                    'ean_optional',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'Ean Allowed',
                        'after' => 'is_variant_allowed'
                    ]
                );
            }
        }
        if (version_compare($context->getVersion(), '0.0.6', '<')) {
            $tableName = $setup->getTable('cdiscount_feeds');
            $connection = $setup->getConnection();

            if (!$connection->tableColumnExists($tableName, 'sync_url')) {
                $connection->addColumn(
                    $tableName,
                    'sync_url',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'Sync Url',
                        'after' => 'product_ids'
                    ]
                );
            }
        }

        if (version_compare($context->getVersion(), '0.0.6', '<')) {
            $tableName = $setup->getTable('cdiscount_feeds');
            $connection = $setup->getConnection();

            if (!$connection->tableColumnExists($tableName, 'unique_name')) {
                $connection->addColumn(
                    $tableName,
                    'unique_name',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Unique Name',
                    ]
                );

                $connection->modifyColumn(
                    $tableName,
                    'unique_name',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Unique Name',
                    ]
                )->addIndex(
                    $tableName,
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE,
                    'unique_name'
                );
            }

            $tableName = $setup->getTable('cdiscount_sizes');

            if ($setup->getConnection()->isTableExists($tableName) != true) {
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
                    )->addColumn(
                        'size',
                        Table::TYPE_TEXT,
                        null,
                        [
                            'nullable' => true
                        ],
                        'Size'
                    )
                    ->setComment('Cdiscount Size');
                $setup->getConnection()->createTable($table);
            }

            $tableName = $setup->getTable('cdiscount_attributes');

            if ($setup->getConnection()->isTableExists($tableName) != true) {
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
                    )->addColumn(
                        'attribute_name',
                        Table::TYPE_TEXT,
                        '125',
                        [
                            'nullable' => true
                        ],
                        'Attribute Name'
                    )->addColumn(
                        'attribute_mappings',
                        Table::TYPE_TEXT,
                        null,
                        [
                            'nullable' => true
                        ],
                        'Attribute Mappings'
                    )
                    ->setComment('Cdiscount Attributes');
                $setup->getConnection()->createTable($table);
            }
        }

        if (version_compare($context->getVersion(), '0.0.7', '<')) {
            $tableName = $setup->getTable('cdiscount_profile');
            $connection = $setup->getConnection();
            if ($connection->isTableExists($tableName)) {
                if (!$connection->tableColumnExists($tableName, 'product_state')) {
                    $connection->addColumn(
                        $tableName,
                        'product_state',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'nullable' => true,
                            'comment' => 'Product Status',
                            'after' => 'profile_status'
                        ]
                    );
                }
            }
        }

        $setup->endSetup();
    }
}