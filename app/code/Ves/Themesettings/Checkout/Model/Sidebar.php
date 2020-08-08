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
namespace Ves\Themesettings\Checkout\Model;

use Magento\Checkout\Helper\Data as HelperData;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Checkout\CustomerData\ItemPoolInterface;

/**
 * Default item
 */
class Sidebar extends \Magento\Checkout\Model\Sidebar
{

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Url
     */
    protected $catalogUrl;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var \Magento\Quote\Model\Quote|null
     */
    protected $quote = null;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    /**
     * @var Customer Cart
     */
    protected $cutomerCart;

    /**
     * @var ItemPoolInterface
     */
    protected $itemPoolInterface;

    /**
     * @var int|float
     */
    protected $summeryCount;

    /**
     * @param Cart $cart
     * @param HelperData $helperData
     * @param ResolverInterface $resolver
     * @codeCoverageIgnore
     */
    public function __construct(
    	Cart $cart,
    	HelperData $helperData,
    	ResolverInterface $resolver,
    	\Magento\Checkout\CustomerData\Cart $cutomerCart,
    	\Magento\Checkout\Model\Session $checkoutSession,
    	\Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
    	\Magento\Checkout\Model\Cart $checkoutCart,
    	ItemPoolInterface $itemPoolInterface
    	) {
    	parent::__construct($cart, $helperData, $resolver);
    	$this->cutomerCart = $cutomerCart;
    	$this->catalogUrl = $catalogUrl;
    	$this->checkoutSession = $checkoutSession;
    	$this->itemPoolInterface = $itemPoolInterface;
    	$this->checkoutCart = $checkoutCart;
    }

    /**
     * Get shopping cart items qty based on configuration (summary qty or items qty)
     *
     * @return int|float
     */
    protected function getSummaryCount()
    {
        if (!$this->summeryCount) {
            $this->summeryCount = $this->checkoutCart->getSummaryQty() ?: 0;
        }
        return $this->summeryCount;
    }

    /**
     * Get array of last added items
     *
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    protected function getRecentItems()
    {
        $items = [];
        if (!$this->getSummaryCount()) {
            return $items;
        }

        foreach (array_reverse($this->getAllQuoteItems()) as $item) {
            /* @var $item \Magento\Quote\Model\Quote\Item */
            if (!$item->getProduct()->isVisibleInSiteVisibility()) {
                $product =  $item->getOptionByCode('product_type') !== null
                    ? $item->getOptionByCode('product_type')->getProduct()
                    : $item->getProduct();

                $products = $this->catalogUrl->getRewriteByProductStore([$product->getId() => $item->getStoreId()]);
                if (!isset($products[$product->getId()])) {
                    continue;
                }
                $item['product_id'] = $item->getProduct()->getId();
                $urlDataObject = new \Magento\Framework\DataObject($products[$product->getId()]);
                $item->getProduct()->setUrlDataObject($urlDataObject);
            }
            $items[] = $this->itemPoolInterface->getItemData($item);
        }
        return $items;
    }

    /**
     * Get active quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
    }

    /**
     * Return customer quote items
     *
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    protected function getAllQuoteItems()
    {
        return $this->getQuote()->getAllVisibleItems();
    }

    /**
     * Compile response data
     *
     * @param string $error
     * @return array
     */
    public function getResponseData($error = '')
    {
    	if (empty($error)) {
    		$response = [
    		'success' => true,
    		'cart' => $this->getRecentItems()
    		];
    	} else {
    		$response = [
    		'success' => false,
    		'error_message' => $error,
    		];
    	}
    	return $response;
    }
}