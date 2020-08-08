<?php
/**
 * Systempay V2-Payment Module version 2.3.2 for Magento 2.x. Support contact : supportvad@lyra-network.com.
 *
 * NOTICE OF LICENSE
 *
 * This source file is licensed under the Open Software License version 3.0
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/osl-3.0.php
 *
 * @category  Payment
 * @package   Systempay
 * @author    Lyra Network (http://www.lyra-network.com/)
 * @copyright 2014-2018 Lyra Network and contributors
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Lyranetwork\Systempay\Block\Payment\Form;

abstract class Systempay extends \Magento\Payment\Block\Form
{

    /**
     *
     * @var \Lyranetwork\Systempay\Helper\Data
     */
    protected $dataHelper;

    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Lyranetwork\Systempay\Helper\Data $dataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Lyranetwork\Systempay\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;

        parent::__construct($context, $data);
    }

    private function checkAndGetLogoUrl($fileName)
    {
        if (! $fileName) {
            return false;
        }

        if ($this->dataHelper->isUploadFileImageExists($fileName)) {
            return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) .
                 'systempay/images/' . $fileName;
        } else {
            return $this->getViewFileUrl('Lyranetwork_Systempay::images/' . $fileName);
        }
    }

    public function getConfigData($name)
    {
        return $this->getMethod()->getConfigData($name);
    }

    /**
     * Render block HTML.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (! $this->dataHelper->isBackend()) {
            $logoURL = $this->checkAndGetLogoUrl($this->getConfigData('module_logo'));

            if ($logoURL) {
                /** @var $logo \Magento\Framework\View\Element\Template */
                $logo = $this->_layout->createBlock(\Magento\Framework\View\Element\Template::class);
                $logo->setTemplate('Lyranetwork_Systempay::payment/logo.phtml');
                $logo->setLogoUrl($logoURL);

                // add logo to the method title
                $this->setMethodLabelAfterHtml($logo->toHtml());
            }
        }

        return parent::_toHtml();
    }
}
