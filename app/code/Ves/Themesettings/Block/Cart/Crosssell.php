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
namespace Ves\Themesettings\Block\Cart;

use Magento\CatalogInventory\Helper\Stock as StockHelper;

/**
 * Cart crosssell list
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Crosssell extends \Magento\Checkout\Block\Cart\Crosssell
{
	public function getItems()
	{
		$items = $this->getData('items');
		if ($items === null) {
			$items = [];
			$ninProductIds = $this->_getCartProductIds();
			if ($ninProductIds) {
				$lastAdded = (int)$this->_getLastAddedProductId();
				if ($lastAdded) {
					$collection = $this->_getCollection()->addProductFilter($lastAdded);
					if (!empty($ninProductIds)) {
						$collection->addExcludeProductFilter($ninProductIds);
					}
					$collection->setPositionOrder()->load();

					foreach ($collection as $item) {
						$ninProductIds[] = $item->getId();
						$items[] = $item;
					}
				}

				if (count($items) < $this->_maxItemCount) {
					$filterProductIds = array_merge(
						$this->_getCartProductIds(),
						$this->_itemRelationsList->getRelatedProductIds($this->getQuote()->getAllItems())
						);
					$collection = $this->_getCollection()->addProductFilter(
						$filterProductIds
						)->addExcludeProductFilter(
						$ninProductIds
						)->setPageSize(9999)->setGroupBy()->setPositionOrder()->load();
						foreach ($collection as $item) {
							$items[] = $item;
						}
					}
				}

				$this->setData('items', $items);
			}
			return $items;
		}
	}