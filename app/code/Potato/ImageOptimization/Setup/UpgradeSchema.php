<?php

namespace Potato\ImageOptimization\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.3.2', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('potato_image_optimization_image'),
                'error_type',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => null,
                    'nullable' => true,
                    'comment' => 'Error type'
                ]
            );
        }
        $pathIndexName = $setup->getConnection()
            ->getIndexName($setup->getTable('potato_image_optimization_image'), 'path');
        $createPathIndexQuery = sprintf('CREATE INDEX %s ON %s(%s)',
            $pathIndexName, $setup->getTable('potato_image_optimization_image'), 'path(255)');

        if (version_compare($context->getVersion(), '1.3.3', '<')) {
            $setup->getConnection()->rawQuery($createPathIndexQuery);
        }
        if (version_compare($context->getVersion(), '1.4.0', '<')) {
            //fix 'path' column with varchar(255) type
            $columns = $setup->getConnection()->describeTable($setup->getTable('potato_image_optimization_image'));
            foreach ($columns as $column) {
                if ($column['COLUMN_NAME'] !== 'path' || $column['DATA_TYPE'] !== 'varchar') {
                    continue;
                }
                //remove old index
                $setup->getConnection()->dropIndex($setup->getTable('potato_image_optimization_image'),
                    $pathIndexName);

                $setup->getConnection()->changeColumn(
                    $setup->getTable('potato_image_optimization_image'),
                    'path',
                    'path',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => null,
                        'comment' => 'File absolute path'
                    ]
                );
                $setup->getConnection()->rawQuery($createPathIndexQuery);
            }
        }
        if (version_compare($context->getVersion(), '1.5.0', '<')) {
            //remove old setting value
            $oldOptimizationMethodSettingPathList = [
                'potato_image_optimization/general/optimization_method',
                'potato_image_optimization/jpg/optimization_method',
                'potato_image_optimization/png/optimization_method',
                'potato_image_optimization/gif/optimization_method',
            ];
            $settingTableName = $setup->getTable('core_config_data');
            $setup->getConnection()->delete($settingTableName, ['path in (?)' => $oldOptimizationMethodSettingPathList]);
        }
        $setup->endSetup();
    }
}