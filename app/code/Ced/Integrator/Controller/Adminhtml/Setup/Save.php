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

namespace Ced\Integrator\Controller\Adminhtml\Setup;


class Save extends \Magento\Backend\App\Action
{
    public $apiRequest;
    public $config;

    /**
     * Index constructor.
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Ced\Integrator\Helper\Api\Request $request,
        \Ced\Integrator\Helper\Config $config
    ) {
        $this->apiRequest = $request;
        $this->config = $config;
        parent::__construct($context);
    }


    public function execute()
    {
        $email = $this->getRequest()->getParam('shop_email');
        $userName = $this->getRequest()->getParam('user_name');
        $pass = $this->getRequest()->getParam('password');
        if ($this->config->isAlreadyRegistered()) {
            $response = $this->apiRequest->loginUser($email, $userName, $pass);
            if (isset($response['success']) && $response['success']) {
                $this->messageManager->addSuccessMessage($response['message']);
            } else {
                $this->messageManager->addErrorMessage($response['message']);
            }
        } else {
            $response = $this->apiRequest->createUser($email, $userName, $pass);
            if (isset($response['success']) && $response['success']) {
                $this->messageManager->addSuccessMessage($response['message']);
            } else {
                $this->messageManager->addErrorMessage($response['message']);
            }
        }
        $redirect = $this->resultRedirectFactory->create();
        $redirect->setPath('integrator/setup/index/id/0');
        return $redirect;
    }
}
