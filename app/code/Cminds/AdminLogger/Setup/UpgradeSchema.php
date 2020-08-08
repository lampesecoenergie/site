<?php

namespace Cminds\AdminLogger\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class UpgradeSchema
 *
 * @package Cminds\AdminLogger\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        /**
         * Alter table 'cminds_adminlogger_action_history'
         */
        $setup->startSetup();
        $setup->getConnection()->addColumn(
            $setup->getTable('cminds_adminlogger_action_history'),
            'admin_name',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Unregister User',
                'after' => 'action_type'
            ]
        );
        $setup->getConnection()->dropForeignKey(
            $setup->getTable('cminds_adminlogger_action_history'),
            $setup->getFkName(
				'cminds_adminlogger_action_history',
                'admin_id',
				'admin_user',
				'user_id'
            )
        );
        $setup->endSetup();
    }
}
