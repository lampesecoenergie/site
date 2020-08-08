<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.0.103
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Feed\Export\Resolver;

use Magento\Review\Model\Review;

class ReviewResolver extends AbstractResolver
{
    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return [];
    }

    /**
     * Associated product
     *
     * @param Review $review
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct($review)
    {
        return $review->getProductCollection()
            ->addFieldToFilter('entity_id', $review->getEntityPkValue())
            ->getFirstItem();
    }

    /**
     * @param Review $review
     * @return float
     */
    public function getRating($review)
    {
        $product = $this->getProduct($review);
        $review->getEntitySummary($product);
        $ratingSummary = $product->getRatingSummary()->getRatingSummary();

        if ($ratingSummary > 0) {
            return ($ratingSummary/100)*5;
        }

        return 5;
    }
}
