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
 * @package     Ced_Integrator
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

/**
 * Config backend model for version display.
 */
namespace Ced\Integrator\Block\Cedcommerce;

class Store extends \Magento\Config\Block\System\Config\Form\Field
{

    public $cedCommerceStoreUrl;

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return '<div>
                    <div>
                        <div id="' . $element->getId() . '">
					        <iframe 
					        src="' . $this->getCedCommerceStoreUrl() . '" 
					        name="cedcommerce_store"
					        id="cedcommerce_store"
					        style="width:100%; height:1200px; border:0; margin:0; overflow:hidden"
					        marginheight="0"
					        marginwidth="0"
					        noscroll></iframe>
				        </div>
				    <input type="hidden" class=" input-text" value="" name="cedcommerce_extensions" id="cedcommerce_extensions" />
				</div>
				</div>
				';
    }

    /**
     * Retrieve feed url
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCedCommerceStoreUrl()
    {
        if (empty($this->cedCommerceStoreUrl)) {
            $this->cedCommerceStoreUrl =
                $this->_storeManager->getStore()->isCurrentlySecure() ?
                    'https://cedcommerce.com/store/' : 'http://cedcommerce.com/store/';
        }

        return $this->cedCommerceStoreUrl;
    }
}
