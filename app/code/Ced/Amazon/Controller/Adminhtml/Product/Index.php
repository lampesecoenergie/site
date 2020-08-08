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
namespace Ced\Amazon\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /** @var \Ced\Amazon\Helper\Config  */
    public $config;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        \Ced\Amazon\Helper\Config $config
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->config = $config;
    }

    /**
     * Product list page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $storeId = $this->getRequest()->getParam('store');
        if ($storeId == null) {
            $params = $this->getRequest()->getParams();
            $params['store'] = 1;
            $this->getRequest()->setParams($params);
        }

        if (!$this->config->getInventorySync() || !$this->config->getPriceSync()) {
            $order = empty($this->config->getOrderImport()) ? 'Disabled': 'Enabled';
            $inventory = empty($this->config->getInventorySync()) ? 'Disabled': 'Enabled';
            $price = empty($this->config->getPriceSync()) ? 'Disabled': 'Enabled';
            $this->messageManager->addComplexNoticeMessage(
                'addAmazonSuccess',
                [
                    'message' => 'Crons are disabled. Data syncing will not be functional.',
                    'reasons' => [
                        "Order import cron status: <b>{$order}</b>",
                        "Inventory export cron status: <b>{$inventory}</b>",
                        "Price export cron status: <b>{$price}</b>",
                        "Kindly update the <a href='{$this->getUrl('adminhtml/system_config/edit/section/amazon')}'>configurations</a>."
                    ],
                    'support_url' => $this->getUrl('integrator/support/index')
                ]
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_Amazon::product');
        $resultPage->getConfig()->getTitle()->prepend(__('Amazon Product Listing'));
        return $resultPage;
    }
}
