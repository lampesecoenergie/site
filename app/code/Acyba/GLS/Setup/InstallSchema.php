<?php

namespace Acyba\GLS\Setup;

use \Magento\Eav\Setup\EavSetup;
use \Magento\Eav\Setup\EavSetupFactory;
use \Magento\Framework\Setup\InstallSchemaInterface;
use \Magento\Framework\Setup\ModuleContextInterface;
use \Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {

        $setup->startSetup();

        $databaseConnection = $setup->getConnection();


        $glsAgenciesListTableName = $setup->getTable('gls_agencies_list');

        if ($databaseConnection->isTableExists($glsAgenciesListTableName) == true) {
            $databaseConnection->dropTable($glsAgenciesListTableName);
        }

        if ($databaseConnection->isTableExists($glsAgenciesListTableName) != true) {
            $table = $databaseConnection->newTable($glsAgenciesListTableName)
                ->addColumn(
                    'id_agency_entry',
                    Table::TYPE_INTEGER,
                    10,
                    [
                        'nullable' => false,
                        'primary' => true,
                        'identity' => true,
                    ]
                )
                ->addColumn(
                    'agencycode',
                    Table::TYPE_TEXT,
                    255,
                    ['default' => null]
                )
                ->addColumn(
                    'zipcode_start',
                    Table::TYPE_INTEGER,
                    5,
                    ['default' => null]
                )
                ->addColumn(
                    'zipcode_end',
                    Table::TYPE_INTEGER,
                    5,
                    ['default' => null]
                )
                ->addColumn(
                    'validity_date_start',
                    Table::TYPE_TEXT,
                    20,
                    ['default' => null]
                )
                ->addColumn(
                    'validity_date_end',
                    Table::TYPE_TEXT,
                    20,
                    ['default' => null]
                )
                ->addColumn(
                    'last_import_date',
                    Table::TYPE_TEXT,
                    20,
                    ['default' => null]
                )
                ->addColumn(
                    'last_check_date',
                    Table::TYPE_TEXT,
                    20,
                    ['default' => null]
                )
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');

            $databaseConnection->createTable($table);
        }
        $setup->endSetup();
    }
}
