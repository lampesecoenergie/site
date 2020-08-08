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

/**
 * Installs DB schema for Koongo connector
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */ 

namespace Nostress\Koongo\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

		/**
		DELETE FROM setup_module WHERE `setup_module`.`module` = 'Nostress_Koongo';
		DROP TABLE nostress_koongo_cache_product;
		DROP TABLE nostress_koongo_cache_tax;
		DROP TABLE nostress_koongo_cache_categorypath;
		DROP TABLE nostress_koongo_cache_weee;
		DROP TABLE nostress_koongo_cache_profilecategory;
		DROP TABLE nostress_koongo_cache_channelcategory;
		DROP TABLE nostress_koongo_taxonomy_category_mapping;
		DROP TABLE nostress_koongo_taxonomy_setup;
		DROP TABLE nostress_koongo_cron;
		DROP TABLE nostress_koongo_channel_profile;
		DROP TABLE nostress_koongo_channel_feed;
		DROP TABLE nostress_koongo_taxonomy_category;
		 
		*/
        
        $table = $installer->getConnection()
            ->newTable($installer->getTable('nostress_koongo_cache_product'))
            ->addColumn('product_id', Table::TYPE_INTEGER, 10,  ['unsigned' => true, 'nullable' => false, 'primary' => true],'Product Id')
            ->addColumn('store_id', Table::TYPE_SMALLINT, 5,  ['unsigned' => true, 'nullable' => false, 'primary' => true],'Store Id')
            ->addColumn('min_price', Table::TYPE_DECIMAL, null, ['precision' => 12,'scale'=> 4, 'nullable' => true, 'default' => null],'Sale or minimal product price')
            ->addColumn('price', Table::TYPE_DECIMAL, null, ['precision' => 12,'scale'=> 4, 'nullable' => true, 'default' => null],'Standard or maximal product price')
            ->addColumn('qty', Table::TYPE_INTEGER, 10, ['nullable' => true, 'default' => null],'Qty')
            ->addColumn('main_category_id', Table::TYPE_INTEGER, 10,  ['unsigned' => true, 'nullable' => true, 'default' => null],'Category Id')
            ->addColumn('main_category_max_level', Table::TYPE_SMALLINT, 5,  ['nullable' => true, 'default' => '-1'],'Category max Level')
            ->addColumn('media_gallery', Table::TYPE_TEXT, null, ['nullable' => true, 'default' => null])
            ->addColumn('categories', Table::TYPE_TEXT, null, ['nullable' => true, 'default' => null])
            ->addColumn('category_ids', Table::TYPE_TEXT, 255, ['nullable' => true, 'default' => null])                        
            ->addIndex($installer->getIdxName('cache_product', ['qty',]), ['qty'])
            ->addIndex($installer->getIdxName('cache_product', ['min_price',]), ['min_price'])
            ->addIndex($installer->getIdxName('cache_product', ['price',]), ['price'])
            ->addIndex($installer->getIdxName('cache_product', ['main_category_id',]), ['main_category_id'])
            ->addIndex($installer->getIdxName('cache_product', ['main_category_max_level',]), ['main_category_max_level'])
            ->setComment('Cache table for product attributes');
        $installer->getConnection()->createTable($table);
        
        $table = $installer->getConnection()
	        ->newTable($installer->getTable('nostress_koongo_cache_tax'))
	        ->addColumn('tax_class_id', Table::TYPE_SMALLINT, 5,  ['unsigned' => true, 'nullable' => false, 'primary' => true],'Tax Class Id')
	        ->addColumn('store_id', Table::TYPE_SMALLINT, 5,  ['unsigned' => true, 'nullable' => false, 'primary' => true],'Store Id')
	        ->addColumn('tax_percent', Table::TYPE_DECIMAL, null, ['precision' => 12,'scale'=> 4, 'nullable' => true, 'default' => null],'Tax percent')
	        ->setComment('Cache table for product tax percent');
        $installer->getConnection()->createTable($table);                
        
        $table = $installer->getConnection()
        	->newTable($installer->getTable('nostress_koongo_cache_categorypath'))
        	->addColumn('category_id', Table::TYPE_INTEGER, 10,  ['unsigned' => true, 'nullable' => false, 'primary' => true],'Category Id')
        	->addColumn('store_id', Table::TYPE_SMALLINT, 5,  ['unsigned' => true, 'nullable' => false, 'primary' => true],'Store Id')
        	->addColumn('category_path', Table::TYPE_TEXT, null, ['nullable' => true])
        	->addColumn('category_root_name', Table::TYPE_TEXT, 255, ['nullable' => true, 'default' => null])
        	->addColumn('category_root_id', Table::TYPE_INTEGER, 10,  ['unsigned' => true, 'nullable' => true, 'default' => null],'Root Category Id')
        	->addColumn('ids_path', Table::TYPE_TEXT, 255, ['nullable' => false])
        	->addColumn('level', Table::TYPE_SMALLINT, 5,  ['nullable' => false],'Category Level')        	
        ->setComment('Cache table for category path');
        $installer->getConnection()->createTable($table);
        
        $table = $installer->getConnection()
        ->newTable($installer->getTable('nostress_koongo_cache_profilecategory'))
        ->addColumn('profile_id', Table::TYPE_INTEGER, 10,  ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],'Profile Id')
        ->addColumn('product_id', Table::TYPE_INTEGER, 10,  ['unsigned' => true, 'nullable' => false, 'primary' => true],'Product Id')
        ->addColumn('main_category_id', Table::TYPE_INTEGER, 10,  ['unsigned' => true, 'nullable' => true, 'default' => null],'Category Id')
        ->addColumn('main_category_max_level', Table::TYPE_SMALLINT, 5,  ['nullable' => true, 'default' => '-1'],'Category max Level')        
        ->addColumn('categories', Table::TYPE_TEXT, null, ['nullable' => true, 'default' => null])
        ->addColumn('category_ids', Table::TYPE_TEXT, 255, ['nullable' => true, 'default' => null])            
        ->addIndex($installer->getIdxName('cache_profilecategory', ['main_category_id',]), ['main_category_id'])
        ->addIndex($installer->getIdxName('cache_profilecategory', ['main_category_max_level',]), ['main_category_max_level'])
        ->addForeignKey(
        		$installer->getFkName(
        				'nostress_koongo_cache_profilecategory',
        				'profile_id',
        				'nostress_koongo_channel_profile',
        				'entity_id'
        		),
        		'profile_id',
        		$installer->getTable('nostress_koongo_channel_profile'),
        		'entity_id',
        		\Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )
        ->setComment('Cache table for profile product categories');
        $installer->getConnection()->createTable($table);
        
        $table = $installer->getConnection()
        	->newTable($installer->getTable('nostress_koongo_cache_weee'))
        	->addColumn('product_id', Table::TYPE_INTEGER, 10,  ['unsigned' => true, 'nullable' => false, 'primary' => true],'Product Id')
        	->addColumn('website_id', Table::TYPE_SMALLINT, 5,  ['unsigned' => true, 'nullable' => false, 'primary' => true],'Website Id')
        	->addColumn('total', Table::TYPE_DECIMAL, null, ['precision' => 12,'scale'=> 2, 'nullable' => false, 'default' => '0.00'],'Total product fixed tax')        	        
        	->setComment('Cache table for fixed product tax');
        $installer->getConnection()->createTable($table);
            
        $table = $installer->getConnection()
	        ->newTable($installer->getTable('nostress_koongo_channel_profile'))
	        ->addColumn('entity_id', Table::TYPE_INTEGER, 10,  ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],'Profile Id')
	        ->addColumn('store_id', Table::TYPE_SMALLINT, 5,  ['unsigned' => true, 'nullable' => false, 'default' => '0'],'Store Id')
	        ->addColumn('name', Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => ''])
	        ->addColumn('filename', Table::TYPE_TEXT, 255, ['nullable' => true, 'default' => null])
	        ->addColumn('url', Table::TYPE_TEXT, 255, ['nullable' => true, 'default' => null])
	        ->addColumn('feed_code', Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => ''])
	        ->addColumn('config', Table::TYPE_TEXT, null, ['nullable' => false])
	        ->addColumn('status', Table::TYPE_SMALLINT, 5, ['default' => 0],"0 = NEW, 1 = RUNNING, 2 = INTERRUPTED, 3 = ERROR, 4 = FINISHED , 5 = ENABLED, 6 = DISABLED")
	        ->addColumn('message', Table::TYPE_TEXT, null, ['nullable' => true])
	        ->addColumn('created_time', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],'Creation Time')
	        ->addColumn('update_time',Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],'Update Time')	        	        
	        ->addColumn('last_run_time', Table::TYPE_DATETIME, null, ['nullable' => true, 'default' => null])                       
        	->setComment('Channel profiles table.');
        $installer->getConnection()->createTable($table);
        
        $table = $installer->getConnection()
	        ->newTable($installer->getTable('nostress_koongo_channel_feed'))
	        ->addColumn('entity_id', Table::TYPE_INTEGER, 11,  ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],'Entity Id')
	        ->addColumn('code', Table::TYPE_TEXT, 255, ['nullable' => false])
	        ->addColumn('link', Table::TYPE_TEXT, 255, ['nullable' => false])
	        ->addColumn('channel_code', Table::TYPE_TEXT, 255, ['nullable' => true, 'default' => null])
	        ->addColumn('enabled', Table::TYPE_BOOLEAN, 1, ['nullable' => false, 'default' => '1'])
	        ->addColumn('type', Table::TYPE_TEXT, 255, ['nullable' => true, 'default' => null])
	        ->addColumn('country', Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => 'INTERNATIONAL'])
	        ->addColumn('file_type', Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => 'xml'])
	        ->addColumn('taxonomy_code', Table::TYPE_TEXT, 255, ['nullable' => true, 'default' => null])
	        ->addColumn('layout', Table::TYPE_TEXT, '2M', ['nullable' => false])
	        ->addIndex($installer->getIdxName('feed', ['code',]), ['code'])
	        ->addIndex($installer->getIdxName('feed', ['link',]), ['link'])
	        ->addIndex($installer->getIdxName('feed', ['channel_code',]), ['channel_code'])
	        ->addIndex($installer->getIdxName('feed', ['type',]), ['type'])
	        ->addIndex($installer->getIdxName('feed', ['file_type',]), ['file_type'])
        	->setComment('Feed layouts table');
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
	        ->newTable($installer->getTable('nostress_koongo_cron'))
	        ->addColumn('entity_id', Table::TYPE_INTEGER, 10,  ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],'Entity Id')
	        ->addColumn('profile_id', Table::TYPE_INTEGER, 10,  ['unsigned' => true, 'nullable' => false],'Profile Id')
	        ->addColumn('day_of_week', Table::TYPE_SMALLINT, 5, ['default' => '1'],"Day of week '1','2','3','4','5','6','7'")
	        ->addColumn('time', Table::TYPE_INTEGER, null, ['nullable' => false])
	        ->addForeignKey(
	        		$installer->getFkName('nostress_koongo_cron', 'profile_id', 'nostress_koongo_channel_profile', 'entity_id'),
	        		'profile_id',
	        		$installer->getTable('nostress_koongo_channel_profile'),
	        		'entity_id',
	        		Table::ACTION_CASCADE
	        )	        
			->setComment('Cron for scheduled profiles execution');
        $installer->getConnection()->createTable($table);
        
        $table = $installer->getConnection()
	        ->newTable($installer->getTable('nostress_koongo_taxonomy_category'))
	        ->addColumn('entity_id', Table::TYPE_INTEGER, 11,  ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],'Entity Id')
	        ->addColumn('taxonomy_code', Table::TYPE_TEXT, 255,  ['nullable' => false],'Taxonomy code')
	        ->addColumn('locale', Table::TYPE_TEXT, 255,  ['nullable' => false, 'default' => 'en_UK' ],'Locale')
	        ->addColumn('hash', Table::TYPE_TEXT, 255,  ['nullable' => false ],'Taxonomy category path hash')
	        ->addColumn('name', Table::TYPE_TEXT, 255,  ['nullable' => false, 'default' => ''],'Taxonomy category name')
	        ->addColumn('id', Table::TYPE_BIGINT, 20,  ['nullable' => true, 'default' => '-1'],'Taxonomy category id')
	        ->addColumn('path', Table::TYPE_TEXT, null,  ['nullable' => false ],'Taxonomy category path')
	        ->addColumn('ids_path', Table::TYPE_TEXT, 255,  ['nullable' => false ],'Taxonomy category ids path')
	        ->addColumn('level', Table::TYPE_SMALLINT, 5,  ['nullable' => false, 'default' => '-1'],'Taxonomy category Level')
	        ->addColumn('parent_name', Table::TYPE_TEXT, 255,  ['nullable' => true, 'default' => ''],'Taxonomy parent category name')
	        ->addColumn('parent_id', Table::TYPE_BIGINT, 20,  ['nullable' => true, 'default' => '-1'],'Taxonomy parent category id')
	        ->addColumn('code1', Table::TYPE_TEXT, 255,  ['nullable' => true ],'Taxonomy category code1')
	        ->addColumn('code2', Table::TYPE_TEXT, 255,  ['nullable' => true ],'Taxonomy category code2')
	        ->addIndex($installer->getIdxName('taxonomy_category', ['hash',]), ['hash'])
	        ->setComment('Taxonomy categories table');
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
	        ->newTable($installer->getTable('nostress_koongo_taxonomy_setup'))
	        ->addColumn('entity_id', Table::TYPE_INTEGER, 10,  ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],'Entity Id')
	        ->addColumn('name', Table::TYPE_TEXT, 255,  ['nullable' => false],'Taxonomy name')
	        ->addColumn('code', Table::TYPE_TEXT, 255,  ['nullable' => false],'Taxonomy code')
	        ->addColumn('type', Table::TYPE_TEXT, 255,  ['nullable' => true, 'default' => null],'Taxonomy type')
	        ->addColumn('setup', Table::TYPE_TEXT, null,  ['nullable' => false],'Taxonomy setup')
	        ->setComment('Taxonomy categories setup');
        $installer->getConnection()->createTable($table);
        
        $installer->endSetup();
    }
}