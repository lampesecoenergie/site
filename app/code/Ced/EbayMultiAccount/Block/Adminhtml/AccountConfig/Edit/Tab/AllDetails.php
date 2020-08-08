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
 * @package   Ced_EbayMultiAccount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\EbayMultiAccount\Block\Adminhtml\AccountConfig\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Registry;

/**
 * Class AllDetails
 * @package Ced\EbayMultiAccount\Block\Adminhtml\Profile\Edit\Tab\Attribute
 */
class AllDetails extends Widget implements RendererInterface
{
    /**
     * @var string
     */
    public $_template = 'accountconfig/alldetails.phtml';

    /**
     * AllDetails constructor.
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_objectManager = $objectManager;
        $this->accountConfig = $this->_objectManager->get('Magento\Framework\Registry')->registry('current_accountconfig');
        $this->multiAccountHelper = $this->_objectManager->get('Ced\EbayMultiAccount\Helper\MultiAccount');
        $this->dataHelper = $this->_objectManager->get('Ced\EbayMultiAccount\Helper\Data');
        $this->paymentmethods = $this->_objectManager->get('Ced\EbayMultiAccount\Model\Config\PaymentMethods');
        $this->returnAccepted = $this->_objectManager->get('Ced\EbayMultiAccount\Model\Config\ReturnAccepted');
        $this->refundType = $this->_objectManager->get('Ced\EbayMultiAccount\Model\Config\RefundType');
        $this->returnWithIn = $this->_objectManager->get('Ced\EbayMultiAccount\Model\Config\ReturnWithIn');
        $this->shipCostPaidBy = $this->_objectManager->get('Ced\EbayMultiAccount\Model\Config\ShipCostPaidBy');
        $this->serviceType = $this->_objectManager->get('Ced\EbayMultiAccount\Model\Config\ServiceType');
        $this->excludedLocation = $this->_objectManager->get('Ced\EbayMultiAccount\Model\Config\ExcludedLocation');
        $this->shipToLocation = $this->_objectManager->get('Ced\EbayMultiAccount\Model\Config\ShipToLocation');
        $this->salesTaxRegion = $this->_objectManager->get('Ced\EbayMultiAccount\Model\Config\SalesTaxRegion');
        $this->shipMethodDom = $this->_objectManager->get('Ced\EbayMultiAccount\Model\Config\DomesticShippingService');
        $this->shipMethodIn = $this->_objectManager->get('Ced\EbayMultiAccount\Model\Config\InternationalShippingService');
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            ['label' => __('Add'), 'onclick' => 'return domesticService.addItem()', 'class' => 'add']
        );
        $button->setName('add_shipping_service');
        $button_new = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            ['label' => __('Add'), 'onclick' => 'return internationalService.addItem()', 'class' => 'add']
        );
        $button_new->setName('add_int_shipping_service');
        $this->setChild('add_button', $button);
        $this->setChild('add_button_new', $button_new);
        return parent::_prepareLayout();
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    public function getDomShippingService($value=[])
    {
        $value = $this->shipMethodDom->toOptionArray();
        return $value;
    }

    public function getInShippingService($value=[])
    {
        $value = $this->shipMethodIn->toOptionArray();
        return $value;
    }

    public function getMappedService($value=[])
    {
        if (isset($this->accountConfig) && $this->accountConfig->getId() > 0) {
            $decodedArray = json_decode($this->accountConfig->getShippingDetails(), true);
            $value = isset($decodedArray['domesticService']) ? $decodedArray['domesticService'] : [];
        }
        return $value;
    }

    public function getInMappedService($value=[])
    {
        if (isset($this->accountConfig) && $this->accountConfig->getId() > 0) {
            $decodedArray = json_decode($this->accountConfig->getShippingDetails(), true);
            $value = isset($decodedArray['internationalService']) ? $decodedArray['internationalService'] : [];
        }   
        return $value;
    }

    public function getLocationId()
    {
        $location = $this->getLocation();
        if (!$location) {
            if (isset($this->accountConfig) && $this->accountConfig->getId() > 0) {
                $locationId = (string)$this->accountConfig->getAccountLocation();
                $id = $this->multiAccountHelper->getAccountFromLocation($locationId);
                $this->multiAccountHelper->unsAccountRegistry();
                $account = $this->multiAccountHelper->getAccountRegistry($id);
                $this->dataHelper->updateAccountVariable();
                $location = (string)($account->getAccountLocation());
            }
        }
        return $location;
    }

    public function getPaymentMethods($value=[])
    {        
        $value = $this->paymentmethods->toOptionArray($this->getLocationId());
        return $value;
    }

    public function getSavedPaymentMethod($value=[])
    {
        if (isset($this->accountConfig) && $this->accountConfig->getId() > 0) {
            $decodedArray = json_decode($this->accountConfig->getPaymentDetails(), true);
            $string = isset($decodedArray['payment_method'])? $decodedArray['payment_method'] : '';
            $value = explode(',', $string);
        }   
        return $value;
    }

    public function getSavedPaymentEmail($value='')
    {
        if (isset($this->accountConfig) && $this->accountConfig->getId() > 0) {
            $decodedArray = json_decode($this->accountConfig->getPaymentDetails(), true);
            $value = isset($decodedArray['paypal_email'])? $decodedArray['paypal_email'] : '';
        }   
        return $value;
    }

    public function getReturnAccepted($value='')
    {
        $value = $this->returnAccepted->toOptionArray();
        return $value;
    }

    public function getSavedReturnAccepted($value='')
    {
        if (isset($this->accountConfig) && $this->accountConfig->getId() > 0) {
            $decodedArray = json_decode($this->accountConfig->getReturnPolicy(), true);
            $value = isset($decodedArray['return_accepted'])? $decodedArray['return_accepted'] : '';
        }   
        return $value;
    }

    public function getRefundType($value='')
    {
        $value = $this->refundType->toOptionArray();
        return $value;
    }

    public function getSavedRefundType($value='')
    {
        if (isset($this->accountConfig) && $this->accountConfig->getId() > 0) {
            $decodedArray = json_decode($this->accountConfig->getReturnPolicy(), true);
            $value = isset($decodedArray['refund_type'])? $decodedArray['refund_type'] : '';
        }   
        return $value;
    }

    public function getReturnWithIn($value='')
    {
        $value = $this->returnWithIn->toOptionArray();
        return $value;
    }

    public function getSavedReturnWithIn($value='')
    {
        if (isset($this->accountConfig) && $this->accountConfig->getId() > 0) {
            $decodedArray = json_decode($this->accountConfig->getReturnPolicy(), true);
            $value = isset($decodedArray['return_days'])? $decodedArray['return_days'] : '';
        }   
        return $value;
    }

    public function getShipCostPaidBy($value='')
    {
        $value = $this->shipCostPaidBy->toOptionArray();
        return $value;
    }

    public function getSavedShipCostPaidBy($value='')
    {
        if (isset($this->accountConfig) && $this->accountConfig->getId() > 0) {
            $decodedArray = json_decode($this->accountConfig->getReturnPolicy(), true);
            $value = isset($decodedArray['ship_cost_paidby'])? $decodedArray['ship_cost_paidby'] : '';
        }   
        return $value;
    }

    public function getSavedReturnDescription($value='')
    {
        if (isset($this->accountConfig) && $this->accountConfig->getId() > 0) {
            $decodedArray = json_decode($this->accountConfig->getReturnPolicy(), true);
            $value = isset($decodedArray['return_description'])? $decodedArray['return_description'] : '';
        }
        return $value;
    }

    public function getServiceType($value=[])
    {
        $value = $this->serviceType->toOptionArray();
        return $value;
    }

    public function getSavedServiceType($value='')
    {
        if (isset($this->accountConfig) && $this->accountConfig->getId() > 0) {
            $decodedArray = json_decode($this->accountConfig->getShippingDetails(), true);
            $value = $decodedArray['service_type'];
            $value = isset($decodedArray['service_type'])? $decodedArray['service_type'] : '';
        }   
        return $value;
    }

    public function getExcludedLocation($value=[])
    {
        $value = $this->excludedLocation->toOptionArray($this->getLocationId());
        return $value;
    }

    public function getSavedExcludedLocation($value=[])
    {
        if (isset($this->accountConfig) && $this->accountConfig->getId() > 0) {
            $decodedArray = json_decode($this->accountConfig->getShippingDetails(), true);
            $string = isset($decodedArray['excluded_area'])? $decodedArray['excluded_area'] : '';
            $value = explode(',', $string);
        }   
        return $value;
    }

    public function getSavedGlobalShipping($value='')
    {
        if (isset($this->accountConfig) && $this->accountConfig->getId() > 0) {
            $decodedArray = json_decode($this->accountConfig->getShippingDetails(), true);
            $value = isset($decodedArray['global_shipping'])? $decodedArray['global_shipping'] : '';
        }   
        return $value;
    }

    public function getSavedFreeShipping($value='')
    {
        if (isset($this->accountConfig) && $this->accountConfig->getId() > 0) {
            $decodedArray = json_decode($this->accountConfig->getShippingDetails(), true);
            $value = isset($decodedArray['free_shipping'])? $decodedArray['free_shipping'] : '';
        }   
        return $value;
    }

    public function getShipToLocation($value=[])
    {
        $value = $this->shipToLocation->toOptionArray();
        return $value;
    }

    public function getSavedShipToLocation($value=[])
    {
        if (isset($this->accountConfig) && $this->accountConfig->getId() > 0) {
            $decodedArray = json_decode($this->accountConfig->getShippingDetails(), true);
            $string = isset($decodedArray['ship_to_location'])? $decodedArray['ship_to_location'] : '';
            $value = explode(',', $string);
        }   
        return $value;
    }

    public function getSalesTaxRegion($value=[])
    {
        $value = $this->salesTaxRegion->toOptionArray();
        return $value;
    }

    public function getSavedSalesTaxRegion($value='')
    {
        if (isset($this->accountConfig) && $this->accountConfig->getId() > 0) {
            $decodedArray = json_decode($this->accountConfig->getShippingDetails(), true);
            $value = isset($decodedArray['sale_tax_state'])? $decodedArray['sale_tax_state'] : '';
        }   
        return $value;
    }

    public function getSavedSaleTaxRate($value='')
    {
        if (isset($this->accountConfig) && $this->accountConfig->getId() > 0) {
            $decodedArray = json_decode($this->accountConfig->getShippingDetails(), true);
            $value = isset($decodedArray['sale_tax_rate'])? $decodedArray['sale_tax_rate'] : '';
        }   
        return $value;
    }

    public function getSavedShippingIncludes($value='')
    {
        if (isset($this->accountConfig) && $this->accountConfig->getId() > 0) {
            $decodedArray = json_decode($this->accountConfig->getShippingDetails(), true);
            $value = isset($decodedArray['shipping_includes'])? $decodedArray['shipping_includes'] : '';
        }   
        return $value;
    }

    public function getYesNo($value=[])
    {
        $value = [['value' => 0, 'label' => 'No'], ['value' => 1, 'label' => 'Yes']];
        return $value;
    }
}
