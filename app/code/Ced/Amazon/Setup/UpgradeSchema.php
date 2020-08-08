<?php

namespace Ced\Amazon\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 * @package Ced\Amazon\Setup
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function upgrade(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->startSetup();
        if (version_compare($context->getVersion(), '0.0.3', '<')) {
            /**
             * Creating `ced_amazon_account` table
             */
            if (!$installer->getConnection()->isTableExists($installer->getTable(\Ced\Amazon\Model\Account::NAME))) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable(\Ced\Amazon\Model\Account::NAME))
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
                        'Id'
                    )
                    ->addColumn(
                        'name',
                        Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => false,
                            'default' => ''
                        ],
                        'Name'
                    )
                    ->addColumn(
                        'mode',
                        Table::TYPE_TEXT,
                        25,
                        [
                            'nullable' => true,
                        ],
                        'Mode'
                    )
                    ->addColumn(
                        'seller_id',
                        Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => true
                        ],
                        'Seller Id'
                    )
                    ->addIndex(
                        $installer->getIdxName($installer->getTable(\Ced\Amazon\Model\Account::NAME), ['seller_id']),
                        ['seller_id']
                    )
                    ->addColumn(
                        'marketplace',
                        Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => true
                        ],
                        'Marketplace'
                    )
                    ->addColumn(
                        'aws_access_key_id',
                        Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => true
                        ],
                        'AWS Acess Key Id'
                    )
                    ->addColumn(
                        'aws_auth_id',
                        Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => true
                        ],
                        'AWS Auth Id'
                    )
                    ->addColumn(
                        'secret_key',
                        Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => true
                        ],
                        'Secret Key'
                    )
                    ->addColumn(
                        'active',
                        Table::TYPE_BOOLEAN,
                        null,
                        [
                            'nullable' => true,
                            'default' => 0
                        ],
                        'Active'
                    )
                    ->addColumn(
                        'status',
                        Table::TYPE_TEXT,
                        50,
                        [
                            'nullable' => true,
                            'default' => 'ADDED'
                        ],
                        'Status'
                    )
                    ->addColumn(
                        'notes',
                        Table::TYPE_TEXT,
                        2000,
                        [
                            'nullable' => true,
                            'default' => ''
                        ],
                        'Notes'
                    )
                    ->addIndex(
                        $installer->getIdxName(
                            $installer->getTable(\Ced\Amazon\Model\Account::NAME),
                            ['name', 'status'],
                            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                        ),
                        [
                            'name',   // filed or column name
                            'status',   // filed or column name
                        ],
                        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT]
                    )
                    ->addColumn(
                        'store_id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        null,
                        ['unsigned' => true, 'default' => '0'],
                        'Store Id'
                    )
                    ->addForeignKey(
                        $installer->getFkName(
                            $installer->getTable(\Ced\Amazon\Model\Account::NAME),
                            'store_id',
                            'store',
                            'store_id'
                        ),
                        'store_id',
                        $installer->getTable('store'),
                        'store_id',
                        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                    )
                    ->setComment('Amazon Account');
                $installer->getConnection()->createTable($table);
            }

            /**
             * Creating `ced_amazon_report` table
             */
            if (!$installer->getConnection()->isTableExists($installer->getTable(\Ced\Amazon\Model\Report::NAME))) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable(\Ced\Amazon\Model\Report::NAME))
                    ->addColumn(
                        \Ced\Amazon\Model\Report::COLUMN_ID,
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'unsigned' => true,
                            'nullable' => false,
                            'primary' => true
                        ],
                        'Id'
                    )
                    ->addColumn(
                        \Ced\Amazon\Model\Report::COLUMN_REQUEST_ID,
                        Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => false,
                        ],
                        'Request Id'
                    )
                    ->addColumn(
                        \Ced\Amazon\Model\Report::COLUMN_REPORT_ID,
                        Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => true,
                        ],
                        'Report Id'
                    )
                    ->addColumn(
                        \Ced\Amazon\Model\Report::COLUMN_ACCOUNT_ID,
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'unsigned' => true,
                            'nullable' => false,
                        ],
                        'Account Id'
                    )
                    ->addColumn(
                        \Ced\Amazon\Model\Report::COLUMN_TYPE,
                        Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => true
                        ],
                        'Type'
                    )
                    ->addColumn(
                        \Ced\Amazon\Model\Report::COLUMN_STATUS,
                        Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => true
                        ],
                        'Status'
                    )
                    ->addColumn(
                        \Ced\Amazon\Model\Report::COLUMN_REPORT_FILE,
                        Table::TYPE_TEXT,
                        1000,
                        [
                            'nullable' => true
                        ],
                        'Report File'
                    )
                    ->addColumn(
                        \Ced\Amazon\Model\Report::COLUMN_SPECIFICS,
                        Table::TYPE_TEXT,
                        '2M',
                        [
                            'nullable' => true
                        ],
                        'Specifics'
                    )
                    ->addColumn(
                        \Ced\Amazon\Model\Report::COLUMN_CREATED_AT,
                        \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                        null,
                        [
                            'nullable' => false,
                            'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
                        ],
                        'Created At'
                    )
                    ->addColumn(
                        \Ced\Amazon\Model\Report::COLUMN_EXECUTED_AT,
                        Table::TYPE_DATETIME,
                        null,
                        [
                            'nullable' => true
                        ],
                        'Executed At'
                    )
                    ->setComment('Amazon Report');
                $installer->getConnection()->createTable($table);
            }

            /**
             * Updating `ced_amazon_order` table
             */
            if ($installer->getConnection()->isTableExists($installer->getTable(\Ced\Amazon\Model\Order::NAME))) {
                /**
                 * Adding column 'marketplace_id'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(\Ced\Amazon\Model\Order::NAME),
                    \Ced\Amazon\Model\Order::COLUMN_MARKETPLACE_ID
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(\Ced\Amazon\Model\Order::NAME),
                        \Ced\Amazon\Model\Order::COLUMN_MARKETPLACE_ID,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'nullable' => false,
                            'length' => 255,
                            'unsigned' => true,
                            'comment' => 'Marketplace Id',
                            'after' => \Ced\Amazon\Model\Order::COLUMN_PO_ID
                        ]
                    );
                }

                /**
                 * Adding column 'account_id'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(\Ced\Amazon\Model\Order::NAME),
                    \Ced\Amazon\Model\Order::COLUMN_ACCOUNT_ID
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(\Ced\Amazon\Model\Order::NAME),
                        \Ced\Amazon\Model\Order::COLUMN_ACCOUNT_ID,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            'nullable' => false,
                            'length' => null,
                            'unsigned' => true,
                            'comment' => 'Account Id',
                            'after' => \Ced\Amazon\Model\Order::COLUMN_MARKETPLACE_ID
                        ]
                    );

                    /**
                     * Adding foreign key
                     */
                    $installer->getConnection()->addForeignKey(
                        $installer->getFkName(
                            $installer->getTable(\Ced\Amazon\Model\Order::NAME),
                            \Ced\Amazon\Model\Order::COLUMN_ACCOUNT_ID,
                            $installer->getTable(\Ced\Amazon\Model\Account::NAME),
                            \Ced\Amazon\Model\Account::ID_FIELD_NAME
                        ),
                        $installer->getTable(\Ced\Amazon\Model\Order::NAME),
                        \Ced\Amazon\Model\Order::COLUMN_ACCOUNT_ID,
                        $installer->getTable(\Ced\Amazon\Model\Account::NAME),
                        \Ced\Amazon\Model\Account::ID_FIELD_NAME,
                        Table::ACTION_CASCADE
                    );
                }

                /**
                 * Adding column 'reason'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(\Ced\Amazon\Model\Order::NAME),
                    \Ced\Amazon\Model\Order::COLUMN_FAILURE_REASON
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(\Ced\Amazon\Model\Order::NAME),
                        \Ced\Amazon\Model\Order::COLUMN_FAILURE_REASON,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'nullable' => false,
                            'length' => null,
                            'unsigned' => true,
                            'comment' => 'Failure Reason',
                            'after' => \Ced\Amazon\Model\Order::COLUMN_ADJUSTMENT_DATA
                        ]
                    );
                }
            }

            /**
             * Updating `ced_amazon_profile` table
             */
            if ($installer->getConnection()->isTableExists($installer->getTable(\Ced\Amazon\Model\Profile::NAME))) {
                /**
                 * Adding column 'marketplace'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(\Ced\Amazon\Model\Profile::NAME),
                    \Ced\Amazon\Model\Profile::COLUMN_MARKETPLACE
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(\Ced\Amazon\Model\Profile::NAME),
                        \Ced\Amazon\Model\Profile::COLUMN_MARKETPLACE,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'nullable' => true,
                            'length' => 1000,
                            'unsigned' => true,
                            'comment' => 'Marketplace',
                            'after' => \Ced\Amazon\Model\Profile::COLUMN_OPTIONAL_ATTRIBUTES
                        ]
                    );
                }

                /**
                 * Adding column 'query'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(\Ced\Amazon\Model\Profile::NAME),
                    \Ced\Amazon\Model\Profile::COLUMN_QUERY
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(\Ced\Amazon\Model\Profile::NAME),
                        \Ced\Amazon\Model\Profile::COLUMN_QUERY,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'nullable' => true,
                            'unsigned' => true,
                            'comment' => 'Query',
                            'after' => \Ced\Amazon\Model\Profile::COLUMN_MARKETPLACE
                        ]
                    );
                }

                /**
                 * Adding column 'account_id'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(\Ced\Amazon\Model\Profile::NAME),
                    \Ced\Amazon\Model\Profile::COLUMN_ACCOUNT_ID
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(\Ced\Amazon\Model\Profile::NAME),
                        \Ced\Amazon\Model\Profile::COLUMN_ACCOUNT_ID,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            'nullable' => false,
                            'length' => null,
                            'unsigned' => true,
                            'comment' => 'Account Id',
                            'after' => \Ced\Amazon\Model\Profile::COLUMN_QUERY
                        ]
                    );

                    /**
                     * Adding foreign key
                     */
                    $installer->getConnection()->addForeignKey(
                        $installer->getFkName(
                            $installer->getTable(\Ced\Amazon\Model\Profile::NAME),
                            \Ced\Amazon\Model\Profile::COLUMN_ACCOUNT_ID,
                            $installer->getTable(\Ced\Amazon\Model\Account::NAME),
                            \Ced\Amazon\Model\Account::ID_FIELD_NAME
                        ),
                        $installer->getTable(\Ced\Amazon\Model\Profile::NAME),
                        \Ced\Amazon\Model\Profile::COLUMN_ACCOUNT_ID,
                        $installer->getTable(\Ced\Amazon\Model\Account::NAME),
                        \Ced\Amazon\Model\Account::ID_FIELD_NAME,
                        Table::ACTION_CASCADE
                    );
                }

                /**
                 * Adding column 'store_id'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(\Ced\Amazon\Model\Profile::NAME),
                    \Ced\Amazon\Model\Profile::COLUMN_STORE_ID
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(\Ced\Amazon\Model\Profile::NAME),
                        \Ced\Amazon\Model\Profile::COLUMN_STORE_ID,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                            'nullable' => false,
                            'length' => null,
                            'unsigned' => true,
                            'default' => 0,
                            'comment' => 'Store Id',
                            'after' => \Ced\Amazon\Model\Profile::COLUMN_ACCOUNT_ID
                        ]
                    );
                    /**
                     * Adding foreign key
                     */
                    $installer->getConnection()->addForeignKey(
                        $installer->getFkName(
                            $installer->getTable(\Ced\Amazon\Model\Profile::NAME),
                            \Ced\Amazon\Model\Profile::COLUMN_STORE_ID,
                            $installer->getTable('store'),
                            'store_id'
                        ),
                        $installer->getTable(\Ced\Amazon\Model\Profile::NAME),
                        \Ced\Amazon\Model\Profile::COLUMN_STORE_ID,
                        $installer->getTable('store'),
                        'store_id',
                        Table::ACTION_CASCADE
                    );
                }

                /**
                 * Removing column 'profile_code'
                 */
                if ($installer->getConnection()->tableColumnExists(
                    $installer->getTable(\Ced\Amazon\Model\Profile::NAME),
                    'profile_code'
                )) {
                    $installer->getConnection()->dropColumn(
                        $installer->getTable(\Ced\Amazon\Model\Profile::NAME),
                        'profile_code'
                    );
                    $installer->getConnection()->dropIndex(
                        $installer->getTable(\Ced\Amazon\Model\Profile::NAME),
                        $installer->getIdxName(
                            $installer->getTable(\Ced\Amazon\Model\Profile::NAME),
                            ['profile_code'],
                            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                        )
                    );
                }
            }

            /*
             * Updating `ced_amazon_feed` table
             */
            if ($installer->getConnection()->isTableExists($installer->getTable(\Ced\Amazon\Model\Feed::NAME))) {
                /**
                 * Adding column 'specifics'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(\Ced\Amazon\Model\Feed::NAME),
                    \Ced\Amazon\Model\Feed::COLUMN_SPECIFICS
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(\Ced\Amazon\Model\Feed::NAME),
                        \Ced\Amazon\Model\Feed::COLUMN_SPECIFICS,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'nullable' => true,
                            'length' => "2M",
                            'comment' => 'Specifics',
                            'after' => \Ced\Amazon\Model\Feed::COLUMN_RESPONSE_FILE
                        ]
                    );
                }
            }

            /*
             * Updating `ced_amazon_queue` table
             */
            if ($installer->getConnection()->isTableExists($installer->getTable(\Ced\Amazon\Model\Queue::NAME))) {
                /**
                 * Adding column 'specifics'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(\Ced\Amazon\Model\Queue::NAME),
                    \Ced\Amazon\Model\Queue::COLUMN_SPECIFICS
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(\Ced\Amazon\Model\Queue::NAME),
                        \Ced\Amazon\Model\Queue::COLUMN_SPECIFICS,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'nullable' => true,
                            'length' => "2M",
                            'comment' => 'Specifics',
                            'after' => \Ced\Amazon\Model\Queue::COLUMN_DEPENDS
                        ]
                    );
                }

                /**
                 * Adding column 'account_id'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(\Ced\Amazon\Model\Queue::NAME),
                    \Ced\Amazon\Model\Queue::COLUMN_ACCOUNT_ID
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(\Ced\Amazon\Model\Queue::NAME),
                        \Ced\Amazon\Model\Queue::COLUMN_ACCOUNT_ID,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            'nullable' => false,
                            'length' => null,
                            'unsigned' => true,
                            'comment' => 'Account Id',
                            'after' => \Ced\Amazon\Model\Queue::COLUMN_ID
                        ]
                    );
                }

                /**
                 * Adding foreign key
                 */
                $installer->getConnection()->addForeignKey(
                    $installer->getFkName(
                        $installer->getTable(\Ced\Amazon\Model\Queue::NAME),
                        \Ced\Amazon\Model\Queue::COLUMN_ACCOUNT_ID,
                        $installer->getTable(\Ced\Amazon\Model\Account::NAME),
                        \Ced\Amazon\Model\Account::ID_FIELD_NAME
                    ),
                    $installer->getTable(\Ced\Amazon\Model\Queue::NAME),
                    \Ced\Amazon\Model\Queue::COLUMN_ACCOUNT_ID,
                    $installer->getTable(\Ced\Amazon\Model\Account::NAME),
                    \Ced\Amazon\Model\Account::ID_FIELD_NAME,
                    Table::ACTION_CASCADE
                );

                /**
                 * Adding column 'marketplace'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(\Ced\Amazon\Model\Queue::NAME),
                    \Ced\Amazon\Model\Queue::COLUMN_MARKETPLACE
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(\Ced\Amazon\Model\Queue::NAME),
                        \Ced\Amazon\Model\Queue::COLUMN_MARKETPLACE,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'nullable' => false,
                            'length' => 255,
                            'unsigned' => true,
                            'comment' => 'Marketplace',
                            'after' => \Ced\Amazon\Model\Queue::COLUMN_ACCOUNT_ID
                        ]
                    );
                }

                /**
                 * Altering column 'created_at'
                 */
                if ($installer->getConnection()->tableColumnExists(
                    $installer->getTable(\Ced\Amazon\Model\Queue::NAME),
                    \Ced\Amazon\Model\Queue::COLUMN_CREATED_AT
                )) {
                    $installer->getConnection()->changeColumn(
                        $installer->getTable(\Ced\Amazon\Model\Queue::NAME),
                        \Ced\Amazon\Model\Queue::COLUMN_CREATED_AT,
                        \Ced\Amazon\Model\Queue::COLUMN_CREATED_AT,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                            'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
                            'nullable' => false,
                            'length' => null,
                            'comment' => 'Created At',
                            'after' => \Ced\Amazon\Model\Queue::COLUMN_SPECIFICS
                        ]
                    );
                }

                /**
                 * Altering column 'executed_at'
                 */
                if ($installer->getConnection()->tableColumnExists(
                    $installer->getTable(\Ced\Amazon\Model\Queue::NAME),
                    \Ced\Amazon\Model\Queue::COLUMN_EXECUTED_AT
                )) {
                    $installer->getConnection()->changeColumn(
                        $installer->getTable(\Ced\Amazon\Model\Queue::NAME),
                        \Ced\Amazon\Model\Queue::COLUMN_EXECUTED_AT,
                        \Ced\Amazon\Model\Queue::COLUMN_EXECUTED_AT,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                            'default' => null,
                            'nullable' => true,
                            'length' => null,
                            'comment' => 'Executed At',
                            'after' => \Ced\Amazon\Model\Queue::COLUMN_CREATED_AT
                        ]
                    );
                }
            }

            /**
             * Updating `ced_amazon_feed` table
             */
            if ($installer->getConnection()->isTableExists($installer->getTable(\Ced\Amazon\Model\Feed::NAME))) {
                /**
                 * Adding column 'account_id'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(\Ced\Amazon\Model\Feed::NAME),
                    \Ced\Amazon\Model\Feed::COLUMN_ACCOUNT_ID
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(\Ced\Amazon\Model\Feed::NAME),
                        \Ced\Amazon\Model\Feed::COLUMN_ACCOUNT_ID,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            'nullable' => false,
                            'length' => null,
                            'unsigned' => true,
                            'comment' => 'Account Id',
                            'after' => \Ced\Amazon\Model\Feed::COLUMN_ID
                        ]
                    );
                }

                /**
                 * Adding foreign key
                 */
                $installer->getConnection()->addForeignKey(
                    $installer->getFkName(
                        $installer->getTable(\Ced\Amazon\Model\Feed::NAME),
                        \Ced\Amazon\Model\Feed::COLUMN_ACCOUNT_ID,
                        $installer->getTable(\Ced\Amazon\Model\Account::NAME),
                        \Ced\Amazon\Model\Account::ID_FIELD_NAME
                    ),
                    $installer->getTable(\Ced\Amazon\Model\Feed::NAME),
                    \Ced\Amazon\Model\Feed::COLUMN_ACCOUNT_ID,
                    $installer->getTable(\Ced\Amazon\Model\Account::NAME),
                    \Ced\Amazon\Model\Account::ID_FIELD_NAME,
                    Table::ACTION_CASCADE
                );
            }

            /**
             * Droping `ced_amazon_log` table
             */
            if ($installer->getConnection()->isTableExists($installer->getTable(\Ced\Amazon\Model\Logs::NAME))) {
                $installer->getConnection()->dropTable($installer->getTable(\Ced\Amazon\Model\Logs::NAME));
            }

            /**
             * Droping `amazon_failed_order` table
             */
            if ($installer->getConnection()->isTableExists(
                $installer->getTable("amazon_failed_order")
            )) {
                $installer->getConnection()->dropTable(
                    $installer->getTable("amazon_failed_order")
                );
            }
        }

        if (version_compare($context->getVersion(), '0.0.4', '<')) {
            /**
             * Updating `ced_amazon_account` table
             */
            if ($installer->getConnection()->isTableExists($installer->getTable(\Ced\Amazon\Model\Account::NAME))) {
                /**
                 * Adding column 'channel'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(\Ced\Amazon\Model\Account::NAME),
                    \Ced\Amazon\Model\Account::COLUMN_CHANNEL
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(\Ced\Amazon\Model\Account::NAME),
                        \Ced\Amazon\Model\Account::COLUMN_CHANNEL,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'nullable' => false,
                            'length' => 50,
                            'unsigned' => true,
                            'comment' => 'Channel',
                            'after' => \Ced\Amazon\Model\Account::COLUMN_STORE_ID
                        ]
                    );
                }
            }
        }

        if (version_compare($context->getVersion(), '0.0.5', '<')) {
            /**
             * Updating `ced_amazon_account` table
             */
            if ($installer->getConnection()->isTableExists($installer->getTable(\Ced\Amazon\Model\Account::NAME))) {
                /**
                 * Adding column 'shipping_method'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(\Ced\Amazon\Model\Account::NAME),
                    \Ced\Amazon\Model\Account::COLUMN_SHIPPING_METHOD
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(\Ced\Amazon\Model\Account::NAME),
                        \Ced\Amazon\Model\Account::COLUMN_SHIPPING_METHOD,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'nullable' => false,
                            'length' => 255,
                            'unsigned' => true,
                            'comment' => 'Shipping Method',
                            'default' => 'shipbyamazon_shipbyamazon',
                            'after' => \Ced\Amazon\Model\Account::COLUMN_STORE_ID
                        ]
                    );
                }

                /**
                 * Adding column 'payment_method'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(\Ced\Amazon\Model\Account::NAME),
                    \Ced\Amazon\Model\Account::COLUMN_PAYMENT_METHOD
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(\Ced\Amazon\Model\Account::NAME),
                        \Ced\Amazon\Model\Account::COLUMN_PAYMENT_METHOD,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'nullable' => false,
                            'length' => 255,
                            'unsigned' => true,
                            'comment' => 'Payment Method',
                            'default' => 'paybyamazon',
                            'after' => \Ced\Amazon\Model\Account::COLUMN_STORE_ID
                        ]
                    );
                }
            }
        }

        if (version_compare($context->getVersion(), '0.0.9', '<')) {
            /**
             * Updating `ced_amazon_profile` table
             */
            if ($installer->getConnection()->isTableExists($installer->getTable(\Ced\Amazon\Model\Profile::NAME))) {
                /**
                 * Adding column 'barcode_exemption'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(\Ced\Amazon\Model\Profile::NAME),
                    \Ced\Amazon\Model\Profile::COLUMN_BARCODE_EXCEMPTION
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(\Ced\Amazon\Model\Profile::NAME),
                        \Ced\Amazon\Model\Profile::COLUMN_BARCODE_EXCEMPTION,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                            'nullable' => false,
                            'comment' => 'Barcode Exemption',
                            'default' => '0',
                            'after' => \Ced\Amazon\Model\Profile::COLUMN_STORE_ID
                        ]
                    );
                }
            }
        }

        if (version_compare($context->getVersion(), '0.1.0', '<')) {
            // adding column cedcommerce
            $tableName = $installer->getTable(\Ced\Amazon\Model\Account::NAME);
            $connection = $installer->getConnection();

            if (!$connection->tableColumnExists($tableName, \Ced\Amazon\Model\Account::COLUMN_CEDCOMMERCE)) {
                $connection->addColumn(
                    $tableName,
                    \Ced\Amazon\Model\Account::COLUMN_CEDCOMMERCE,
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                        'nullable' => true,
                        'comment' => 'Authorize via Cedcommerce',
                        'default' => 0,
                        'after' => \Ced\Amazon\Model\Account::COLUMN_STATUS
                    ]
                );
            }
        }

        if (version_compare($context->getVersion(), '0.1.4', '<')) {
            /**
             * Updating `ced_amazon_account` table
             */
            if ($installer->getConnection()->isTableExists($installer->getTable(\Ced\Amazon\Model\Account::NAME))) {
                /**
                 * Adding column 'multi_store'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(\Ced\Amazon\Model\Account::NAME),
                    \Ced\Amazon\Model\Account::COLUMN_MULTI_STORE
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(\Ced\Amazon\Model\Account::NAME),
                        \Ced\Amazon\Model\Account::COLUMN_MULTI_STORE,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                            'nullable' => true,
                            'comment' => 'Multi Store',
                            'default' => 0,
                            'after' => \Ced\Amazon\Model\Account::COLUMN_STORE_ID
                        ]
                    );
                }

                /**
                 * Adding column 'multi_store_values'
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(\Ced\Amazon\Model\Account::NAME),
                    \Ced\Amazon\Model\Account::COLUMN_MULTI_STORE_VALUES
                )) {
                    $installer->getConnection()->addColumn(
                        $installer->getTable(\Ced\Amazon\Model\Account::NAME),
                        \Ced\Amazon\Model\Account::COLUMN_MULTI_STORE_VALUES,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'nullable' => false,
                            'length' => 500,
                            'unsigned' => true,
                            'comment' => 'Multi Store Values',
                            'after' => \Ced\Amazon\Model\Account::COLUMN_STORE_ID
                        ]
                    );
                }
            }
        }

        if (version_compare($context->getVersion(), '0.1.8', '<')) {
            if ($installer->getConnection()->isTableExists($installer->getTable(\Ced\Amazon\Model\Order::NAME))) {
                /**
                 * Updating `ced_amazon_order` table
                 */
                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(\Ced\Amazon\Model\Order::NAME),
                    \Ced\Amazon\Model\Order::COLUMN_FULFILLMENT_CHANNEL
                )) {
                    /**
                     * Adding columns 'fulfillment_channel'
                     */
                    $installer->getConnection()->addColumn(
                        $installer->getTable(\Ced\Amazon\Model\Order::NAME),
                        \Ced\Amazon\Model\Order::COLUMN_FULFILLMENT_CHANNEL,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'nullable' => false,
                            'length' => 255,
                            'unsigned' => true,
                            'comment' => 'Fulfillment Channel',
                        ]
                    );
                }

                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(\Ced\Amazon\Model\Order::NAME),
                    \Ced\Amazon\Model\Order::COLUMN_SALES_CHANNEL
                )) {
                    /**
                     * Adding columns 'sales_channel'
                     */
                    $installer->getConnection()->addColumn(
                        $installer->getTable(\Ced\Amazon\Model\Order::NAME),
                        \Ced\Amazon\Model\Order::COLUMN_SALES_CHANNEL,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'nullable' => false,
                            'length' => 255,
                            'unsigned' => true,
                            'comment' => 'Sales Channel',
                        ]
                    );
                }

                if ($installer->getConnection()->tableColumnExists(
                    $installer->getTable(\Ced\Amazon\Model\Order::NAME),
                    'order_place_date'
                )) {
                    /**
                     * Changing column 'order_place_date' to 'purchase_date'
                     */
                    $installer->getConnection()->changeColumn(
                        $installer->getTable(\Ced\Amazon\Model\Order::NAME),
                        'order_place_date',
                        \Ced\Amazon\Model\Order::COLUMN_PO_DATE,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                            'nullable' => true,
                            'comment' => 'Purchase Date',
                        ]
                    );
                }

                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(\Ced\Amazon\Model\Order::NAME),
                    \Ced\Amazon\Model\Order::COLUMN_LAST_UPDATE_DATE
                )) {
                    /**
                     * Adding columns 'fulfillment_channel'
                     */
                    $installer->getConnection()->addColumn(
                        $installer->getTable(\Ced\Amazon\Model\Order::NAME),
                        \Ced\Amazon\Model\Order::COLUMN_LAST_UPDATE_DATE,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                            'nullable' => true,
                            'comment' => 'Last Update Date In Amazon',
                        ]
                    );
                }

                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(\Ced\Amazon\Model\Order::NAME),
                    \Ced\Amazon\Model\Order::COLUMN_CREATED_AT
                )) {
                    /**
                     * Adding columns 'created at'
                     */
                    $installer->getConnection()->addColumn(
                        $installer->getTable(\Ced\Amazon\Model\Order::NAME),
                        \Ced\Amazon\Model\Order::COLUMN_CREATED_AT,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                            'nullable' => false,
                            'size' => null,
                            'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
                            'comment' => 'Created At',
                        ]
                    );
                }

                if (!$installer->getConnection()->tableColumnExists(
                    $installer->getTable(\Ced\Amazon\Model\Order::NAME),
                    \Ced\Amazon\Model\Order::COLUMN_UPDATED_AT
                )) {
                    /**
                     * Adding columns 'updated at'
                     */
                    $installer->getConnection()->addColumn(
                        $installer->getTable(\Ced\Amazon\Model\Order::NAME),
                        \Ced\Amazon\Model\Order::COLUMN_UPDATED_AT,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                            'nullable' => false,
                            'size' => null,
                            'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE,
                            'comment' => 'Updated At',
                        ]
                    );
                }

                /**
                 * Creating `ced_amazon_order_items` table
                 */
                if (!$installer->getConnection()->isTableExists($installer->getTable(
                    \Ced\Amazon\Model\Order\Item::NAME
                ))) {
                    try {
                        $table = $installer->getConnection()
                            ->newTable($installer->getTable(\Ced\Amazon\Model\Order\Item::NAME))
                            ->addColumn(
                                \Ced\Amazon\Model\Order\Item::COLUMN_ID,
                                Table::TYPE_INTEGER,
                                null,
                                [
                                    'identity' => true,
                                    'unsigned' => true,
                                    'nullable' => false,
                                    'primary' => true
                                ],
                                'Id'
                            )
                            ->addColumn(
                                \Ced\Amazon\Model\Order\Item::COLUMN_ASIN,
                                Table::TYPE_TEXT,
                                255,
                                [
                                    'nullable' => false,
                                ],
                                'ASIN'
                            )
                            ->addColumn(
                                \Ced\Amazon\Model\Order\Item::COLUMN_SKU,
                                Table::TYPE_TEXT,
                                255,
                                [
                                    'nullable' => false,
                                ],
                                'Seller SKU'
                            )
                            ->addColumn(
                                \Ced\Amazon\Model\Order\Item::COLUMN_ORDER_ITEM_ID,
                                Table::TYPE_TEXT,
                                null,
                                [
                                    'nullable' => false,
                                ],
                                'Order Item Id'
                            )
                            ->addColumn(
                                \Ced\Amazon\Model\Order\Item::COLUMN_ORDER_ID,
                                Table::TYPE_TEXT,
                                100,
                                [
                                    'nullable' => true
                                ],
                                'Amazon order Id'
                            )
                            ->addColumn(
                                \Ced\Amazon\Model\Order\Item::COLUMN_MAGENTO_ORDER_ITEM_ID,
                                Table::TYPE_INTEGER,
                                10,
                                [
                                    'nullable' => true,
                                    'unsigned' => true,
                                ],
                                'Magento Order Item Id'
                            )
                            ->addColumn(
                                \Ced\Amazon\Model\Order\Item::COLUMN_CUSTOMIZED_URL,
                                Table::TYPE_TEXT,
                                1000,
                                [
                                    'nullable' => true
                                ],
                                'Customized Zip Url'
                            )
                            ->addColumn(
                                \Ced\Amazon\Model\Order\Item::COLUMN_CUSTOMIZED_DATA,
                                Table::TYPE_TEXT,
                                '1000',
                                [
                                    'nullable' => true
                                ],
                                'Json Data From Zip'
                            )
                            ->addColumn(
                                \Ced\Amazon\Model\Order\Item::COLUMN_QTY_ORDERED,
                                Table::TYPE_INTEGER,
                                '10',
                                [
                                    'nullable' => false
                                ],
                                'Qunatity Ordered'
                            )
                            ->addColumn(
                                \Ced\Amazon\Model\Order\Item::COLUMN_QTY_SHIPPED,
                                Table::TYPE_INTEGER,
                                '10',
                                [
                                    'nullable' => true
                                ],
                                'Quantity shipped'
                            )
                            ->addColumn(
                                \Ced\Amazon\Model\Order\Item::COLUMN_CREATED_AT,
                                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                                null,
                                [
                                    'nullable' => false,
                                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
                                ],
                                'Created At'
                            )
                            ->addColumn(
                                \Ced\Amazon\Model\Order\Item::COLUMN_UPDATED_AT,
                                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                                null,
                                [
                                    'nullable' => false,
                                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE
                                ],
                                'Updated At'
                            )
                            ->addForeignKey(
                                $installer->getFkName(
                                    \Ced\Amazon\Model\Order\Item::NAME,
                                    \Ced\Amazon\Model\Order\Item::COLUMN_ORDER_ID,
                                    $installer->getTable(\Ced\Amazon\Model\Order::NAME),
                                    \Ced\Amazon\Model\Order::COLUMN_PO_ID
                                ),
                                \Ced\Amazon\Model\Order\Item::COLUMN_ORDER_ID,
                                $installer->getTable(\Ced\Amazon\Model\Order::NAME),
                                \Ced\Amazon\Model\Order::COLUMN_PO_ID,
                                Table::ACTION_CASCADE
                            )
                            ->addForeignKey(
                                $installer->getFkName(
                                    \Ced\Amazon\Model\Order\Item::NAME,
                                    \Ced\Amazon\Model\Order\Item::COLUMN_MAGENTO_ORDER_ITEM_ID,
                                    $installer->getTable('sales_order_item'),
                                    'item_id'
                                ),
                                \Ced\Amazon\Model\Order\Item::COLUMN_MAGENTO_ORDER_ITEM_ID,
                                $installer->getTable('sales_order_item'),
                                'item_id',
                                Table::ACTION_SET_NULL
                            )
                            ->setComment('Amazon Order Items');
                        $installer->getConnection()->createTable($table);
                    } catch (\Zend_Db_Exception $exception) {
                        throwException($exception);
                    }
                }
            }
        }

        $installer->endSetup();
    }
}
