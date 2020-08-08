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


class Authenticate extends \Magento\Backend\App\Action
{
    public $apiRequest;
    public $config;

    /**
     * Index constructor.
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Ced\Integrator\Helper\Config $config
    ) {
        $this->config = $config;
        parent::__construct($context);
    }


    public function execute()
    {
        $token = $this->config->getApiToken();
        $redirect = $this->resultRedirectFactory->create();
        if ($this->config->isAlreadyRegistered() && !empty($token)) {
            $redirect->setPath('adminhtml/integration/index');
        } else {
            $this->messageManager->addErrorMessage('Please Signup or Login First');
            $redirect->setPath('integrator/setup/index/id/0');
        }
        return $redirect;
    }
}
