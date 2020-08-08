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
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\EbayMultiAccount\Controller\Adminhtml\AccountConfig;

use Ced\EbayMultiAccount\Helper\Data;

/**
 * Class UpdateDetails
 * @package Ced\EbayMultiAccount\Controller\Adminhtml\AccountConfig
 */
class UpdateDetails extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Ced_EbayMultiAccount::EbayMultiAccount';
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Ced\EbayMultiAccount\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * @var Data
     */
    public $helper;

    /**
     * @var \Ced\EbayMultiAccount\Model\Config\PaymentMethod
     */
    public $paymentmethods;
    /**
     * @var \Ced\EbayMultiAccount\Model\Config\ReturnAccpeted
     */
    public $returnAccpeted;
    /**
     * @var \Ced\EbayMultiAccount\Model\Config\RefundType
     */
    public $refundType;
    /**
     * @var \Ced\EbayMultiAccount\Model\Config\ReturnWithIn
     */
    public $returnWithIn;
    /**
     * @var \Ced\EbayMultiAccount\Model\Config\ShipCostPaidBy
     */
    public $shipCostPaidBy;
    /**
     * @var \Ced\EbayMultiAccount\Model\Config\ServiceType
     */
    public $serviceType;
    /**
     * @var \Ced\EbayMultiAccount\Model\Config\ExcludedLocation
     */
    public $excludedLocation;
    /**
     * @var \Ced\EbayMultiAccount\Model\Config\ShipToLocation
     */
    public $shipToLocation;
    /**
     * @var \Ced\EbayMultiAccount\Model\Config\SalesTaxRegion
     */
    public $salesTaxRegion;

    /**
     * UpdateDetails constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Ced\EbayMultiAccount\Helper\MultiAccount $multiAccountHelper,
        \Ced\EbayMultiAccount\Model\Config\PaymentMethods $paymentmethods,
        \Ced\EbayMultiAccount\Model\Config\ReturnAccepted $returnAccpeted,
        \Ced\EbayMultiAccount\Model\Config\RefundType $refundType,
        \Ced\EbayMultiAccount\Model\Config\ReturnWithIn $returnWithIn,
        \Ced\EbayMultiAccount\Model\Config\ShipCostPaidBy $shipCostPaidBy,
        \Ced\EbayMultiAccount\Model\Config\ServiceType $serviceType,
        \Ced\EbayMultiAccount\Model\Config\ExcludedLocation $excludedLocation,
        \Ced\EbayMultiAccount\Model\Config\ShipToLocation $shipToLocation,
        \Ced\EbayMultiAccount\Model\Config\SalesTaxRegion $salesTaxRegion,
        Data $helper
    )
    {
        parent::__construct($context);
        $this->multiAccountHelper = $multiAccountHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->paymentmethods = $paymentmethods;
        $this->returnAccepted = $returnAccpeted;
        $this->refundType = $refundType;
        $this->returnWithIn = $returnWithIn;
        $this->shipCostPaidBy = $shipCostPaidBy;
        $this->serviceType = $serviceType;
        $this->excludedLocation = $excludedLocation;
        $this->shipToLocation = $shipToLocation;
        $this->salesTaxRegion = $salesTaxRegion;
        $this->helper = $helper;
    }

    public function execute()
    {
        $locationId = $this->getRequest()->getParam('account_location');
        if ($this->_coreRegistry->registry('ebay_account'))
            $this->_coreRegistry->unregister('ebay_account');
        $accountId = $this->multiAccountHelper->getAccountFromLocation($locationId);
        $account = $this->multiAccountHelper->getAccountRegistry($accountId);
        $this->helper->updateAccountVariable();
        
        $location = trim($account->getAccountLocation());
        $payMethods = $this->paymentmethods->toOptionArray($location);
        $returnAccepted = $this->returnAccepted->toOptionArray();
        $refundType = $this->refundType->toOptionArray();
        $returnWithIn = $this->returnWithIn->toOptionArray();
        $shipCostPaidBy = $this->shipCostPaidBy->toOptionArray();
        $serviceType = $this->serviceType->toOptionArray();
        $excludedLocation = $this->excludedLocation->toOptionArray();
        $shipToLocation = $this->shipToLocation->toOptionArray();
        $salesTaxRegion = $this->salesTaxRegion->toOptionArray();
        $yesNo = [['value' => 0, 'label' => 'No'], ['value' => 1, 'label' => 'Yes']];

        $result = $this->resultPageFactory->create(true)->getLayout()->createBlock('Ced\EbayMultiAccount\Block\Adminhtml\AccountConfig\Edit\Tab\AllDetails')->setLocation($location)->setPaymentMethods($payMethods)->setReturnAccepted($returnAccepted)->setYesNo($yesNo)->setRefundType($refundType)->setReturnWithIn($returnWithIn)->setShipCostPaidBy($shipCostPaidBy)->setServiceType($serviceType)->setExcludedLocation($excludedLocation)->setShipToLocation($shipToLocation)->setSalesTaxRegion($salesTaxRegion)->toHtml();
        $this->getResponse()->setBody($result);
        return;
    }
}
