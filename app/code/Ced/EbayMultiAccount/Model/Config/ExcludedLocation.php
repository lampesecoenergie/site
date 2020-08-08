<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_EbayMultiAccount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\EbayMultiAccount\Model\Config;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExcludedLocation implements \Magento\Framework\Option\ArrayInterface
{
	/**
     * Object Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;
    /**
     * @var \Magento\Framework\Filesystem
     */
    public $filesystem;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var \Ced\EbayMultiAccount\Helper\Data
     */
    public $helper;

    /**
     * ExcludedLocation Constructor
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ced\EbayMultiAccount\Helper\Data $data
    )
    {
        $this->objectManager = $objectManager;
        $this->filesystem = $filesystem;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $data;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray($location=null)
    {
        $result = [];
        $locationName = '';
        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $folderPath = $mediaDirectory->getAbsolutePath('ced/ebaymultiaccount/');
        $locationList = $this->objectManager->get('Ced\EbayMultiAccount\Model\Config\Location')->toOptionArray();
        foreach ($locationList as $value) {
            if ($value['value'] == $location) {
                $locationName = $value['label'];
            }
        }
        $path = $folderPath .$locationName. '/ExcludedLocations.json';
        $exLocation = $this->helper->loadFile($path, '', '');
        if ($exLocation) {
            foreach ($exLocation as $key => $value) {
                $result[] = ['label' => $value['Description'], 'value' => $value['Location']];
            }
        }
        return $result;
    }
}
