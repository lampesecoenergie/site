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
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Feed
 * @package Ced\Amazon\Model\ResourceModel
 * @method load(\Ced\Amazon\Api\Data\FeedInterface $object, $value, $field = null)
 * @method save(\Ced\Amazon\Api\Data\FeedInterface $object)
 */
class Feed extends AbstractDb
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(\Ced\Amazon\Model\Feed::NAME, \Ced\Amazon\Model\Feed::COLUMN_ID);
    }
}
