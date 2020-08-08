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
 * @package   Ced_RueDuCommerce
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\RueDuCommerce\Controller\Adminhtml\Profile;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Ced\RueDuCommerce\Model\Profile;
use Magento\Backend\App\Action;

/**
 * Class Edit
 *
 * @package Ced\RueDuCommerce\Controller\Adminhtml\Profile
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
        \Ced\RueDuCommerce\Helper\Config $config
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
                __('RueDuCommerce API not enabled or credentials are invalid. Please check RueDuCommerce Configuration.')
            );
        }

        // Case 2.1 : Ui form
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_RueDuCommerce::rueducommerce_profile');
        $id = $this->getRequest()->getParam('id');
        if (isset($id) and !empty($id)) {
            $this->profile->load($id);
            if($this->profile && $this->profile->getData('profile_name')){
                $this->_coreRegistry->register('rueducommerce_profile', $this->profile);
                $resultPage->getConfig()->getTitle()->prepend(__('Edit Profile '.$this->profile->getData('profile_name')));
            }else {
                $resultPage->getConfig()->getTitle()->prepend(__('Add New Profile'));
            }
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('Add New Profile'));
        }
        return $resultPage;
    }
}
