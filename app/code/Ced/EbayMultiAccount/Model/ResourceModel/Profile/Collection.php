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
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\EbayMultiAccount\Model\ResourceModel\Profile;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection

{
    /**
     * @return void
     */

    public function _construct()
    {
        $this->_init(
            'Ced\EbayMultiAccount\Model\Profile',
            'Ced\EbayMultiAccount\Model\ResourceModel\Profile'
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

}