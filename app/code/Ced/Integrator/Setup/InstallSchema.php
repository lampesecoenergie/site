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
 * @package     Ced_Integrator
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Integrator\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     *
     * {@inheritdoc} @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        // Get module table
        $tableName = $setup->getTable(\Ced\Integrator\Model\Log::TABLE_NAME);
        // Check if the table already exists
        if ($setup->getConnection()->isTableExists($tableName) != true) {
            // Create tutorial_simplenews table
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
                ->setComment('Integrator Log');
            $setup->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
