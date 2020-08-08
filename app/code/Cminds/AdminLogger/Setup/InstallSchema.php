<?php

namespace Cminds\AdminLogger\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 *
 * @package Cminds\AdminLogger\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        /**
         * Create table 'cminds_adminlogger_action_history'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable('cminds_adminlogger_action_history'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ],
                'Action history id'
            )
            ->addColumn(
                'admin_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Admin id'
            )
            ->addColumn(
                'action_type',
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ],
                'Action type'
            )
            ->addColumn(
                'reference_value',
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ],
                'Reference value'
            )
            ->addColumn(
                'ip',
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ],
                'Ip address'
            )
            ->addColumn(
                'browser_agent',
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ],
                'Browser agent'
            )
            ->addColumn(
                'old_value',
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true
                ],
                'Old value'
            )
            ->addColumn(
                'new_value',
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true
                ],
                'New value'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => Table::TIMESTAMP_INIT
                ],
                'Created At Date'
            )
            ->addForeignKey(
                $setup->getFkName(
                    'cminds_adminlogger_action_history',
                    'admin_id',
                    'admin_user',
                    'user_id'
                ),
                'admin_id',
                $setup->getTable('admin_user'),
                'user_id',
                Table::ACTION_CASCADE
            )
            ->setComment("Cminds AdminLogger table");

        $setup->getConnection()->createTable($table);
    }
}
