<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2017 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class UpgradeSchema
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var string $salesConnection
     */
    private static $salesConnection = 'sales';

    /**
     * @var string $checkoutConnection
     */
    private static $checkoutConnection = 'checkout';

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var \Magento\Setup\Module\Setup $installer */
        $installer = $setup;
        $installer->startSetup();

        $salesConnection    = $installer->getConnection(self::$salesConnection);
        $checkoutConnection = $installer->getConnection(self::$checkoutConnection);

        if (version_compare($context->getVersion(), '1.0.0', '<=')) {
            /* Order address */
            $salesConnection->addColumn(
                $installer->getTable('sales_order_address'),
                'mondialrelay_pickup_id',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 6,
                    'nullable' => true,
                    'comment' => 'Mondial Relay Pickup Id'
                ]
            );
            $salesConnection->addColumn(
                $installer->getTable('sales_order_address'),
                'mondialrelay_code',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 4,
                    'nullable' => true,
                    'comment' => 'Mondial Relay Code'
                ]
            );

            /* Add Monaco and Dom-Tom */
            $bind = [
                ['country_id' => 'FR', 'code' => 'OM', 'default_name' => 'Outre-Mer'],
                ['country_id' => 'FR', 'code' => '98', 'default_name' => 'Monaco']
            ];
            foreach ($bind as $data) {
                $installer->getConnection()->insert(
                    $installer->getTable('directory_country_region'),
                    $data
                );
            }
        }

        if (version_compare($context->getVersion(), '1.1.0', '<=')) {
            $tableName = $installer->getTable('quote_mondialrelay_pickup');

            if (!$checkoutConnection->isTableExists($tableName)) {
                $table = $checkoutConnection
                    ->newTable($tableName)
                    ->addColumn(
                        'quote_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Quote Id'
                    )->addColumn(
                        'pickup_id',
                        Table::TYPE_TEXT,
                        10,
                        [],
                        'Pickup Id'
                    )->addColumn(
                        'country_id',
                        Table::TYPE_TEXT,
                        2,
                        [],
                        'Country Id'
                    )->addIndex(
                        $installer->getIdxName(
                            'quote_mondialrelay_pickup',
                            ['quote_id'],
                            AdapterInterface::INDEX_TYPE_UNIQUE
                        ),
                        ['quote_id'],
                        ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                    )->addForeignKey(
                        $installer->getFkName('quote_mondialrelay_pickup', 'quote_id', 'quote', 'entity_id'),
                        'quote_id',
                        $installer->getTable('quote'),
                        'entity_id',
                        Table::ACTION_CASCADE
                    )->setComment(
                        'Quote Mondial Relay Pickup Data'
                    );

                $checkoutConnection->createTable($table);
            }
        }

        if (version_compare($context->getVersion(), '1.1.1', '<=')) {
            $checkoutConnection->addColumn(
                $installer->getTable('quote_mondialrelay_pickup'),
                'code',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 4,
                    'nullable' => true,
                    'comment' => 'Mondial Relay Code'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.1.2', '<=')) {
            $salesConnection->addColumn(
                $installer->getTable('sales_order'),
                'mondialrelay_packaging_weight',
                [
                    'type' => Table::TYPE_FLOAT,
                    'nullable' => true,
                    'default' => 0,
                    'comment' => 'Mondial Relay Packaging Weight'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.1.3', '<')) {
            $checkoutConnection->addColumn(
                $installer->getTable('quote_mondialrelay_pickup'),
                'company',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Pickup Company'
                ]
            );
            $checkoutConnection->addColumn(
                $installer->getTable('quote_mondialrelay_pickup'),
                'street',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Pickup Street'
                ]
            );
            $checkoutConnection->addColumn(
                $installer->getTable('quote_mondialrelay_pickup'),
                'postcode',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Pickup Postcode'
                ]
            );
            $checkoutConnection->addColumn(
                $installer->getTable('quote_mondialrelay_pickup'),
                'city',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Pickup City'
                ]
            );
        }

        $installer->endSetup();
    }
}
