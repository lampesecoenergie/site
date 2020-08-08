<?php

namespace Potato\Crawler\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $table = $installer->getConnection()->newTable($installer->getTable('po_crawler_counter'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => 1, 'primary' => 1, 'identity' => 1, 'nullable' => 0]
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => 1]
            )
            ->addColumn(
                'date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                []
            )
        ;
        $installer->getConnection()->createTable($table);
        
        $table = $installer->getConnection()->newTable($installer->getTable('po_crawler_popularity'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => 1, 'primary' => 1, 'identity' => 1, 'nullable' => 0]
            )
            ->addColumn(
                'url',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                []
            )
            ->addColumn(
                'view',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => 1]
            )
            ->addIndex(
                'url_2',
                ['url'],
                ['type' => 'unique']
            )
            ->addIndex(
                'url',
                ['url'],
                ['type' => 'index']
            )
        ;
        $installer->getConnection()->createTable($table);
        
        $table = $installer->getConnection()->newTable($installer->getTable('po_crawler_queue'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => 1, 'primary' => 1, 'identity' => 1, 'nullable' => 0]
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => 1]
            )
            ->addColumn(
                'url',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                []
            )
            ->addColumn(
                'customer_group_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                []
            )
            ->addColumn(
                'useragent',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                []
            )
            ->addColumn(
                'currency',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                3,
                []
            )
            ->addColumn(
                'priority',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => 1]
            )
            ->addIndex(
                'po_crawler_queue_unique_index',
                ['store_id','url','customer_group_id','useragent','currency'],
                ['type' => 'unique']
            )
            ->addIndex(
                'url',
                ['url'],
                ['type' => 'index']
            )
        ;
        $installer->getConnection()->createTable($table);
        

        $installer->endSetup();
    }
}