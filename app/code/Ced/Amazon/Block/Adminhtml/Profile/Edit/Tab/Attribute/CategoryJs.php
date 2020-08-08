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
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Block\Adminhtml\Profile\Edit\Tab\Attribute;

use Magento\Backend\Block\Widget;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * @deprecated
 * Rolesedit Tab Display Block.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class CategoryJs extends Widget implements RendererInterface
{
    public $_template = 'profile/category.phtml';

    public $_profile;

    public $_coreRegistry;

    public $amazon;

    /**
     * CategoryJs constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Amazon\Sdk\Product\Category\Collection $amazon
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Amazon\Sdk\Product\Category\Collection $amazon,
        $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_profile = $this->_coreRegistry->registry('current_profile');
        $this->amazon = $amazon;
        parent::__construct($context, $data);
    }

    /**
     * Get current level of walmart category
     * @param integer $level
     * @return string
     */
    public function getLevel($level)
    {
        $option = [];
        /*$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $location = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getValue('amazon/settings/location');
        $locationList = $objectManager->get('Ced\Amazon\Model\Config\Location')->toOptionArray();
        foreach ($locationList as $value) {
            if ($value['value'] == $location) {
                $locationName = $value['label'];
            }
        }
        $mediaDirectory = $objectManager->get('\Magento\Framework\Filesystem')
            ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::APP);
        $folderPath = $mediaDirectory->getAbsolutePath('code/Ced/Amazon/Setup/json/');
        $path = $folderPath . $locationName . '/categoryLevel-' . $level . '.json';
        $rootlevel = $objectManager->get('Ced\Amazon\Helper\Data')->loadFile($path);
        $options = isset($rootlevel['CategoryArray']['Category']) ? $rootlevel['CategoryArray']['Category'] : [];
        foreach ($options as $value) {
            if ($value['CategoryLevel'] == $level) {
                $option[] = $value;
            }
        }*/
        return $option;
    }

    /**
     * Render form element as HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }
}
