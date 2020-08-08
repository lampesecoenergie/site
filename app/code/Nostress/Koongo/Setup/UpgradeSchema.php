<?php
/**
 * Magento Module developed by NoStress Commerce
 *
 * NOTICE OF LICENSE
 *
 * This program is licensed under the Koongo software licence (by NoStress Commerce). 
 * With the purchase, download of the software or the installation of the software 
 * in your application you accept the licence agreement. The allowed usage is outlined in the
 * Koongo software licence which can be found under https://docs.koongo.com/display/koongo/License+Conditions
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at https://store.koongo.com/.
 *
 * See the Koongo software licence agreement for more details.
 * @copyright Copyright (c) 2017 NoStress Commerce (http://www.nostresscommerce.cz, http://www.koongo.com/)
 *
 */

namespace Nostress\Koongo\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
	public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
	{
		$installer = $setup;

        $installer->startSetup();

		//handle all possible upgrade versions

		//if(!$context->getVersion()) {
			//no previous version found, installation, InstallSchema was just executed
			//be careful, since everything below is true for installation !
		//}

		if (version_compare($context->getVersion(), '2.0.1') < 0) 
		{
			$this->updateToVersion201($installer);
		}
		
		if (version_compare($context->getVersion(), '2.2.3') < 0) 
		{
			$this->updateToVersion223($installer);
        }
        
        if (version_compare($context->getVersion(), '2.2.4') < 0) 
		{
			$this->updateToVersion224($installer);
        }
        
        if (version_compare($context->getVersion(), '2.2.5') < 0) 
		{
			$this->updateToVersion225($installer);
        }
        
        if (version_compare($context->getVersion(), '2.2.6') < 0) 
		{
			$this->updateToVersion226($installer);
		}

		 $installer->endSetup();
	}

	protected function updateToVersion201($installer)
	{
		$table = $installer->getConnection()
		->newTable($installer->getTable('nostress_koongo_taxonomy_category_mapping'))
		->addColumn('entity_id', Table::TYPE_INTEGER, 11,  ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],'Entity Id')
		->addColumn('taxonomy_code', Table::TYPE_TEXT, 255,  ['nullable' => false],'Taxonomy code')
		->addColumn('locale', Table::TYPE_TEXT, 255,  ['nullable' => false, 'default' => 'en_UK' ],'Locale')
		->addColumn('store_id', Table::TYPE_SMALLINT, 5,  ['unsigned' => true, 'nullable' => false, 'default' => '0'],'Store Id')
		->addColumn('config', Table::TYPE_TEXT, null, ['nullable' => false])
		->setComment('Taxonomy categories mapping rules table');
		$installer->getConnection()->createTable($table);
			
		$table = $installer->getConnection()
		->newTable($installer->getTable('nostress_koongo_cache_channelcategory'))
		->addColumn('profile_id', Table::TYPE_INTEGER, 10,  ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],'Profile Id')
		->addColumn('product_id', Table::TYPE_INTEGER, 10,  ['unsigned' => true, 'nullable' => false, 'primary' => true],'Product Id')
		->addColumn('hash', Table::TYPE_TEXT, 255,  ['nullable' => false ],'Taxonomy category path hash')
		->addIndex($installer->getIdxName('cache_channelcategory', ['hash',]), ['hash'])
		->addForeignKey(
				$installer->getFkName(
						'nostress_koongo_cache_channelcategory',
						'profile_id',
						'nostress_koongo_channel_profile',
						'entity_id'
				),
				'profile_id',
				$installer->getTable('nostress_koongo_channel_profile'),
				'entity_id',
				\Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
		)
		->setComment('Cache table for profile channel categories');
		$installer->getConnection()->createTable($table);
	}

	protected function updateToVersion223($installer)
	{
		$table = $installer->getConnection()
            ->newTable($installer->getTable('nostress_koongo_cache_price'))
            ->addColumn('product_id', Table::TYPE_INTEGER, 10,  ['unsigned' => true, 'nullable' => false, 'primary' => true],'Product Id')
			->addColumn('store_id', Table::TYPE_SMALLINT, 5,  ['unsigned' => true, 'nullable' => false, 'primary' => true],'Store Id')
			->addColumn('customer_group_id', Table::TYPE_SMALLINT, 5,  ['unsigned' => true, 'nullable' => false, 'primary' => true],'Customer Group Id')
            ->addColumn('min_price', Table::TYPE_DECIMAL, null, ['precision' => 12,'scale'=> 4, 'nullable' => true, 'default' => null],'Sale or minimal product price')
            ->addColumn('price', Table::TYPE_DECIMAL, null, ['precision' => 12,'scale'=> 4, 'nullable' => true, 'default' => null],'Standard or maximal product price')
            ->addIndex($installer->getIdxName('cache_product', ['min_price',]), ['min_price'])
            ->addIndex($installer->getIdxName('cache_product', ['price',]), ['price'])
            ->setComment('Cache table for product price');
		$installer->getConnection()->createTable($table);
		
		//Remove columns from product catche table
		$connection = $installer->getConnection();
        $data = [
            'nostress_koongo_cache_product' => [
                'min_price',
                'price',                
            ],           
        ];

        foreach ($data as $table => $fields) {
            foreach ($fields as $field) {
                $connection->dropColumn($installer->getTable($table), $field);
            }
        }			
    }
    
    protected function updateToVersion224($installer)
	{
        //Add media gallery cache table
		$table = $installer->getConnection()
            ->newTable($installer->getTable('nostress_koongo_cache_mediagallery'))
            ->addColumn('product_id', Table::TYPE_INTEGER, 10,  ['unsigned' => true, 'nullable' => false, 'primary' => true],'Product Id')
            ->addColumn('store_id', Table::TYPE_SMALLINT, 5,  ['unsigned' => true, 'nullable' => false, 'primary' => true],'Store Id')
            ->addColumn('value_id', Table::TYPE_INTEGER, 10,  ['unsigned' => true, 'nullable' => false, 'primary' => true],'Value Id')            
            ->addColumn('value', Table::TYPE_TEXT, 255, ['nullable' => true, 'default' => null])
            ->addColumn('label', Table::TYPE_TEXT, 255, ['nullable' => true, 'default' => null])            
            ->setComment('Cache table for media gallery');
		$installer->getConnection()->createTable($table);			
    }
    
    protected function updateToVersion225($installer)
	{
        //Add stock status into nostress koongo cache product table
        $installer->getConnection()->addColumn(
            $installer->getTable('nostress_koongo_cache_product'),
            'stock_status',
            [
                'type' => Table::TYPE_SMALLINT,
                'length' => 5,
                'unsigned' => true,
                'nullable' => true,
                'after' => 'qty',
                'comment' => 'Stock status or Is salable from Inventory_stock_X tables'
            ]
        );		
    }
    
    protected function updateToVersion226($installer)
	{
        //Add tier price cache table
		$table = $installer->getConnection()
            ->newTable($installer->getTable('nostress_koongo_cache_pricetier'))
            ->addColumn('product_id', Table::TYPE_INTEGER, 10,  ['unsigned' => true, 'nullable' => false, 'primary' => true],'Product Id')
			->addColumn('store_id', Table::TYPE_SMALLINT, 5,  ['unsigned' => true, 'nullable' => false, 'primary' => true],'Store Id')
            ->addColumn('customer_group_id', Table::TYPE_SMALLINT, 5,  ['unsigned' => true, 'nullable' => false, 'primary' => true],'Customer Group Id')
            ->addColumn('qty', Table::TYPE_INTEGER, 10, ['nullable' => true, 'default' => null, 'primary' => true],'Qty')
            ->addColumn('unit_price', Table::TYPE_DECIMAL, null, ['precision' => 12,'scale'=> 2, 'nullable' => true, 'default' => null],'Unit product price')                                    
            ->addColumn('discount_percent', Table::TYPE_DECIMAL, null, ['precision' => 5,'scale'=> 2, 'nullable' => true, 'default' => null],'Discount percent')            
            ->addIndex($installer->getIdxName('cache_pricetier', ['qty',]), ['qty'])
            ->addIndex($installer->getIdxName('cache_pricetier', ['unit_price',]), ['unit_price'])            
            ->addIndex($installer->getIdxName('cache_pricetier', ['discount_percent',]), ['discount_percent'])
            ->setComment('Cache table for tier price');
        $installer->getConnection()->createTable($table);			                

        //Add tier prices into nostress koongo cache price table
        $installer->getConnection()->addColumn(
            $installer->getTable('nostress_koongo_cache_price'),
            'tier_prices',
            [
                'type' => Table::TYPE_TEXT,
                'length' => null,                
                'nullable' => true,
                'default' => null,
                'after' => 'price',
                'comment' => 'Tier prices'
            ]
        );
    }
}