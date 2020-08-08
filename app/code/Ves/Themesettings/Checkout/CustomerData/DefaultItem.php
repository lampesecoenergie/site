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
namespace Ves\Themesettings\Checkout\CustomerData;

/**
 * Default item
 */
class DefaultItem extends \Magento\Checkout\CustomerData\DefaultItem
{
    /**
     * {@inheritdoc}
     */
    protected function doGetItemData()
    {
        $imageHelper = $this->imageHelper->init($this->getProductForThumbnail(), 'mini_cart_product_thumbnail');
        return [
			'options'                       => $this->getOptionList(),
			'qty'                           => $this->item->getQty() * 1,
			'item_id'                       => $this->item->getId(),
			'configure_url'                 => $this->getConfigureUrl(),
			'is_visible_in_site_visibility' => $this->item->getProduct()->isVisibleInSiteVisibility(),
			'product_name'                  => $this->item->getProduct()->getName(),
			'product_sku'                   => $this->item->getProduct()->getSku(),
			'product_id'					=> $this->item->getProduct()->getId(),
			'product_url'                   => $this->getProductUrl(),
			'product_has_url'               => $this->hasProductUrl(),
			'product_price'                 => $this->checkoutHelper->formatPrice($this->item->getCalculationPrice()),
			'product_price_value'           => $this->item->getCalculationPrice(),
			'product_image'                 => [
				'src'    => $imageHelper->getUrl(),
				'alt'    => $imageHelper->getLabel(),
				'width'  => $imageHelper->getWidth(),
				'height' => $imageHelper->getHeight(),
            ],
            'canApplyMsrp' => $this->msrpHelper->isShowBeforeOrderConfirm($this->item->getProduct())
                && $this->msrpHelper->isMinimalPriceLessMsrp($this->item->getProduct()),
        ];
    }
}