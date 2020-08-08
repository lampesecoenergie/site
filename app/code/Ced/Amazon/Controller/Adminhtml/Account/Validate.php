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
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Controller\Adminhtml\Account;

/**
 * Class Validate
 * @package Ced\Amazon\Controller\Adminhtml\Account
 */
class Validate extends \Ced\Amazon\Controller\Adminhtml\Account\Base
{
    public function execute()
    {
        $this->error = $this->dataFactory->create();
        $this->data = $this->dataFactory->create();

        $this->error->setData('error', false);
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);

        if (!$this->validate() || !$this->api()) {
            $this->error->setData('error', true);
            $fields = implode("|", $this->invalid);
            $error = !empty($fields) ? " Fields are invalid: [{$fields}]" : '';
            $messages[] = 'Invalid credentials. Unable to save the account.' . $error;
            $this->error->setData('messages', $messages);
        }

        $resultJson->setData($this->error->getData());
        return $resultJson;
    }
}
