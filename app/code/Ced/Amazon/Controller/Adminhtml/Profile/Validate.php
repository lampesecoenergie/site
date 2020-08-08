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
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Controller\Adminhtml\Profile;

/**
 * Class Save
 * @package Ced\Amazon\Controller\Adminhtml\Profile
 */
class Validate extends \Ced\Amazon\Controller\Adminhtml\Profile\Base
{
    public function execute()
    {
        $this->validation->setData('error', false);
        $this->validation->setData('messages', []);
        $this->validate(true);

        /** @var \Magento\Framework\Controller\Result\Json $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $resultPage->setData($this->validation);
        return $resultPage;
    }
}
