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
 * @category  Ced
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Controller\Adminhtml\Account;

/**
 * Class Save
 *
 * @package Ced\Amazon\Controller\Adminhtml\Account
 */
class Save extends \Ced\Amazon\Controller\Adminhtml\Account\Base
{
    public function execute()
    {
        $response = [];
        $messages = [];
        $error = true;
        $accountId = null;

        $back = $this->getRequest()->getParam('back');
        $isAjax = $this->getRequest()->getParam('isAjax', false);

        $this->data = $this->dataFactory->create();
        if ($this->validate() && $this->api()) {
            /** @var \Ced\Amazon\Model\Account $account */
            $account = $this->account->create();
            if (!empty($this->data->getData('id'))) {
                $account->load($this->data->getData('id'));
            }

            $account->addData($this->data->getData());
            try {
                $accountId = $this->repository->save($account);
                $error = false;
                $response = [
                    'id' => $accountId,
                    'name' => $account->getName() . " | id:" . $accountId
                ];
            } catch (\Exception $e) {
                $error = true;
                $messages[] = $e->getMessage();
            }
        }

        if (empty($isAjax)) {
            if ($accountId) {
                $this->messageManager->addSuccessMessage('Amazon account saved successfully.');
            } else {
                $this->messageManager->addWarningMessage('Amazon account saving failed. Invalid credentials.');
            }

            $redirect = $this->resultRedirectFactory->create();
            if (isset($back) && $back == 'edit') {
                if ($accountId) {
                    $redirect->setPath(
                        'amazon/account/edit',
                        ['id' => $accountId, '_current' => true]
                    );
                } else {
                    $redirect->setPath(
                        'amazon/account/edit',
                        ['_current' => true]
                    );
                }
            } else {
                $redirect->setPath('amazon/account/index');
            }

            return $redirect;
        } else {
            /** @var \Magento\Framework\Controller\Result\Json  $result */
            $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
            $result->setData([
                'messages' => $messages,
                'error' => $error,
                'account' => $response,
            ]);

            return $result;
        }
    }
}
