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
 * @package   Ced_Cdiscount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Controller\Adminhtml\Profile;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Ced\Cdiscount\Model\Profile;
use Magento\Backend\App\Action;

/**
 * Class Edit
 *
 * @package Ced\Cdiscount\Controller\Adminhtml\Profile
 */
class Edit extends Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;
    /**
     * @var
     */
    public $_entityTypeId;
    /**
     * @var \Magento\Framework\Registry
     */
    public $_coreRegistry;

    /**
     * @var Profile
     */
    public $profile;

    public $config;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */

    public function __construct(
        Context $context,
        \Magento\Framework\Registry $coreRegistry,
        PageFactory $resultPageFactory,
        Profile $profile,
        \Ced\Cdiscount\Helper\Config $config
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->profile = $profile;
        $this->config = $config;
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        // case 1 check if api config are valid
        if (!$this->config->isValid()) {
            $this->messageManager->addErrorMessage(
                __('Cdiscount API not enabled or credentials are invalid. Please check Cdiscount Configuration.')
            );
        }

        // Case 2.1 : Ui form
        $id = $this->getRequest()->getParam('id');
        $resultPage = $this->resultPageFactory->create();
        if (isset($id) and !empty($id)) {
            $this->profile->load($id);
        }
        $this->_coreRegistry->register('cdiscount_profile', $this->profile);
        $resultPage->setActiveMenu('Ced_Cdiscount::cdiscount_profile');
        $resultPage->getConfig()->getTitle()->prepend(__('Cdiscount Profile'));
        return $resultPage;
    }
}
