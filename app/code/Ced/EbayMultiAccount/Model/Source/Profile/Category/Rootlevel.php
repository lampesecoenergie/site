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
 * @package     Ced_EbayMultiAccount
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */


namespace Ced\EbayMultiAccount\Model\Source\Profile\Category;

class Rootlevel implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Objet Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * @var \Magento\Framework\Registry
     */
    public $_coreRegistry;

    /**
     * Constructor
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry
    ) {
        $this->objectManager = $objectManager;
        $this->_coreRegistry = $registry;
    }

    /**
     * To Array
     * @return string|[]
     */
    public function toOptionArray()
    {
        $currentAccount = false;
        if($this->_coreRegistry->registry('ebay_account'))
            $currentAccount = $this->_coreRegistry->registry('ebay_account');
        if(isset($currentAccount) && $currentAccount->getId()) {
            $location = trim($currentAccount->getAccountLocation());
        } else {
            $location = trim($this->objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('ebaymultiaccount_config/ebaymultiaccount_setting/location'));
        }
        $mediaDirectory = $this->objectManager->get('\Magento\Framework\Filesystem')->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $folderPath = $mediaDirectory->getAbsolutePath('ced/ebaymultiaccount/');
        $locationList = $this->objectManager->get('Ced\EbayMultiAccount\Model\Config\Location')->toOptionArray();
        foreach ($locationList as $value) {
            if ($value['value'] == $location) {
                $locationName = $value['label'];
            }
        }
        $path = $folderPath .$locationName. '/categoryLevel-1.json';
        $rootlevel = $this->objectManager->get('Ced\EbayMultiAccount\Helper\Data')->loadFile($path, '', '');
        $options = [];
        if (isset($rootlevel['CategoryArray']['Category'])) {
            foreach ($rootlevel['CategoryArray']['Category'] as $value) {
                $options[]=[
                    'value'=>$value['CategoryID'],
                    'label'=>$value['CategoryName']
                ];
            }
        }
        return $options;
    }

}