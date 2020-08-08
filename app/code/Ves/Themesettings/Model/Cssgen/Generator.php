<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Themesettings
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */
namespace Ves\Themesettings\Model\Cssgen;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Directory\Helper\Data;

class Generator{

	/**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
	protected $_storeManager;

	/**
     * @var \Magento\Framework\Message\ManagerInterface
     */
	private $messageManager;

	/**
     * @var \Magento\Framework\Registry
     */
	protected $_coreRegistry = null;

	/**
     * @var \Magento\Framework\View\Element\BlockFactory
     */
	protected $_blockFactory;

	/**
	 * @var \Magento\Theme\Model\Theme
	 */
	protected $_themeModel;

	/**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
	protected $_scopeConfig;

	/**
     * @var \Ves\Themesettings\Helper\Data
     */
	protected $_vesHelper;

	public function __construct(
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\View\Element\BlockFactory $blockFactory,
		\Magento\Framework\Registry $registry,
		\Magento\Framework\Filesystem $filesystem,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Theme\Model\Theme $themeModel,
		\Ves\Themesettings\Helper\Data $vesHelper
		)
	{
		$this->_storeManager = $storeManager;
		$this->_blockFactory = $blockFactory;
		$this->_coreRegistry = $registry;
		$this->_filesystem = $filesystem;
		$this->messageManager = $messageManager;
		$this->_themeModel = $themeModel;
		$this->_vesHelper = $vesHelper;
		$this->_scopeConfig = $scopeConfig;
	}

	public function generateCss($websiteCode, $storeCode){
		if($websiteCode){
			$website = $this->_storeManager->getWebsite($websiteCode);
			$this->_generateWebsiteCss($website);
		}
		if($storeCode){
			$this->_generateStoreCss($storeCode);
		}
		if(!$websiteCode && !$storeCode){
			$websites = $this->_storeManager->getWebsites();
			foreach ($websites as $website) {
				$this->_generateWebsiteCss($website); 
			}
		}
	}

	protected function _generateWebsiteCss($website){
		foreach ($website->getStoreCodes() as $storeCode){
			$this->_generateStoreCss($storeCode);
		}
	}

	protected function _generateStoreCss($storeCode){
		$store = $this->_storeManager->getStore($storeCode);
		$storeId = $store->getId();

		if(!empty($this->_vesHelper->getVesTheme($storeId))){
			$this->_coreRegistry->register('ves_store', $store);
			$cssBlockHtml = $this->_blockFactory->createBlock('Ves\Themesettings\Block\ThemesettingsDesign')->setTemplate("Ves_Themesettings::themesettings_styles.phtml")->toHtml();
			$cssBlockHtml = $this->_compressCssCode($cssBlockHtml);

			$themeId =  $this->_scopeConfig->getValue(
				\Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID,
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE,
				$store);
			$theme = $this->_themeModel->load($themeId);
			try{
				if (empty($cssBlockHtml)) {
					throw new Exception( __("The system has an issue when create css file") ); 
				}
				$localeCode = $this->_scopeConfig->getValue(
					Data::XML_PATH_DEFAULT_LOCALE,
					\Magento\Store\Model\ScopeInterface::SCOPE_STORE,
					$store
					);

				$enableCssMinify = $this->_scopeConfig->getValue(
					\Magento\Config\Model\Config\Backend\Admin\Custom::XML_PATH_DEV_CSS_MINIFY_FILES,
					\Magento\Store\Model\ScopeInterface::SCOPE_STORE,
					$store
					);

				// pub/static/frontend
				$dir = $this->_filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);

				$fileName = $theme->getFullPath() . DIRECTORY_SEPARATOR . $localeCode . DIRECTORY_SEPARATOR . 'Ves_Themesettings/css/style-' . $store->getCode() . ($enableCssMinify?'.min':'') . '.css';

				$fileName2 = $theme->getFullPath() . DIRECTORY_SEPARATOR . $localeCode . DIRECTORY_SEPARATOR . 'Ves_Themesettings/css/style-' . $store->getCode() . '.css';

				$dir->writeFile($fileName, $cssBlockHtml);
				$dir->writeFile($fileName2, $cssBlockHtml);
				$this->messageManager->addSuccess(__('The %1 file updated successfully.', $dir->getAbsolutePath($fileName)));
				$this->messageManager->addSuccess(__('The %1 file updated successfully.', $dir->getAbsolutePath($fileName2)));

				// app/design/frontend
				$themeDir = $this->_filesystem->getDirectoryWrite(DirectoryList::APP);
				$fileName = 'design' . DIRECTORY_SEPARATOR . $theme->getFullPath() . DIRECTORY_SEPARATOR . 'Ves_Themesettings/web/css/style-'.$store->getCode().'.css';
				$themeDir->writeFile($fileName, $cssBlockHtml);

			}catch (\Exception $e){
				$this->messageManager->addError(__('The system has an issue when create css file'). '<br/>Message: ' . $e->getMessage());
			}
			$this->_coreRegistry->unregister('ves_store');
		}
	}

	private function _compressCssCode( $input_text = "") {
        $output = str_replace(array("\r\n", "\r"), "\n", $input_text);
        $lines = explode("\n", $input_text);
        $new_lines = array();

        foreach ($lines as $i => $line) {
            if(!empty($line))
                $new_lines[] = trim($line);
        }
        return implode($new_lines);
    }
}