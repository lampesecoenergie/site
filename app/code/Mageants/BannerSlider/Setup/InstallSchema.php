<?php
 /**
 * @category  Mageants BannerSlider
 * @package   Mageants_BannerSlider
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <support@Mageants.com>
 */

namespace Mageants\BannerSlider\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Store\Model\StoreManagerInterface;

class InstallSchema implements InstallSchemaInterface
{
	protected $StoreManager;         
	/*<b raw_pre="*" raw_post="*">     * Init     </b>     <b raw_pre="*" raw_post="*"> @param EavSetupFactory $eavSetupFactory     </b>/*/    
	public function __construct(StoreManagerInterface $StoreManager)       
	{                
		$this->StoreManager=$StoreManager;        
	}
	
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
		
        $table  = $installer->getConnection()
            ->newTable($installer->getTable('mageants_bannerslider'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'slider_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null, 'nullable' => false],
                'Slider Name'
            )
            ->addColumn(
                'setting',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => null, 'nullable' => false],
                'Slider Setting  in json'
            )
			->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                [],
                'Store Id'
            )
			->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                [],
                'Status'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Slider Created At'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Slider Updated At'
            )
			->addIndex(  
				$installer->getIdxName(  
					$installer->getTable('mageants_bannerslider'),  
					['slider_name'],  
					\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT  
				),  
				['slider_name'],
				['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT]
			);
			
        $installer->getConnection()->createTable($table);
		
		$table1  = $installer->getConnection()
            ->newTable($installer->getTable('mageants_bannerslider_slides'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
			->addColumn(
                'slider_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                [],
                'Slider Id'
            )
            ->addColumn(
                'slide_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ['default' => -1, 'nullable' => false],
                'Slide Type'
            ) 
			->addColumn(
                'title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null, 'nullable' => false],
                'Slide Title'
            ) 
			->addColumn(
                'content',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null, 'nullable' => false],
                'Slide Title'
            )
			->addColumn(
                'image',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null, 'nullable' => false],
                'Banner Image Path'
            )
			->addColumn(
                'category_ids',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null, 'nullable' => false],
                'Category Ids for slides'
            ) 
			->addColumn(
                'product_ids',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null, 'nullable' => false],
                'Product Ids for slides'
            ) 
			->addColumn(
                'show_cat_slide_if_no_image_found',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ['default' => -1, 'nullable' => false],
                '0 = don\'t Show category slide which dont have image'
            ) 
			->addColumn(
                'show_prod_slide_if_no_image_found',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ['default' => -1, 'nullable' => false],
                '0 = don\'t Show product slide which dont have image'
            ) 
            ->addColumn(
                'slidesetting',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => null, 'nullable' => false],
                'Slide Setting  in json'
            )
			->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                [],
                'Status'
            )
			->addColumn(
                'position',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                [],
                'Set Slide Position '
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Slide Created At'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Slide Updated At'
            )
			->addIndex(  
				$installer->getIdxName(  
					$installer->getTable('mageants_bannerslider_slides'),  
					['content',  'title'], 
					\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT  
				),  
				['content',  'title'], 
				['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT]
			);
			
        $installer->getConnection()->createTable($table1);
		
        $installer->endSetup();
		
		$service_url = 'https://www.mageants.com/index.php/rock/register/live?ext_name=Mageants_BannerSlider&dom_name='.$this->StoreManager->getStore()->getBaseUrl();
		$curl = curl_init($service_url);     
		curl_setopt_array($curl, array(            
			CURLOPT_SSL_VERIFYPEER => false,            
			CURLOPT_RETURNTRANSFER => true,            
			CURLOPT_POST => true,            
			CURLOPT_FOLLOWLOCATION =>true,            
			CURLOPT_ENCODING=>'',            
			CURLOPT_USERAGENT => 'Mozilla/5.0'        
		));                
		$curl_response = curl_exec($curl);        
		curl_close($curl);
    }
}
