<?php

namespace BoostMyShop\AdminLogger\Setup;

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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getConnection()
            ->newTable($installer->getTable('bms_adminlogger'))
            ->addColumn('al_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Log id')
            ->addColumn('al_created_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Created at')
            ->addColumn('al_user', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 40, [], 'User')
            ->addColumn('al_object_type', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 100, [], 'Object type')
            ->addColumn('al_object_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Object id')
            ->addColumn('al_details', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 800, [], 'Details')
            ->addIndex(
                $installer->getIdxName('bms_adminloggerobject_id', ['al_object_id']),
                ['al_object_id']
            )
            ->setComment('Admin logger logs');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();

    }
}
