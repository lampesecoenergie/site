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
 * @category    Ced
 * @package     Ced_Ebay
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Model\Payment;

class Paybyamazon extends \Magento\Payment\Model\Method\AbstractMethod
{
    const METHOD_CODE = 'paybyamazon';

    /**
     * @var string
     */
    protected $_code = self::METHOD_CODE;
    /**
     * @var bool
     */
    protected $_canAuthorize = true;

    /**
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * @var bool
     */
    protected $_canCancelInvoice = false;

    /**
     * @var bool
     */
    protected $_canCapturePartial = false;
    /**
     * @var bool
     */
    protected $_canCreateBillingAgreement = false;
    /**
     * @var bool
     */
    protected $_canFetchTransactionInfo = false;
    /**
     * @var bool
     */
    protected $_canManageRecurringProfiles = false;
    /**
     * @var bool
     */
    protected $_canOrder = false;
    /**
     * @var bool
     */
    protected $_canRefund = false;
    /**
     * @var bool
     */
    protected $_canRefundInvoicePartial = false;
    /**
     * @var bool
     */
    protected $_canReviewPayment = false;

    /* Setting for disable from front-end. */
    /* START */
    /**
     * @var bool
     */
    protected $_canUseCheckout = false;
    /**
     * @var bool
     */
    protected $_canUseForMultishipping = false;
    /**
     * @var bool
     */
    protected $_canUseInternal = false;
    /**
     * @var bool
     */
    protected $_canVoid = false;
    /**
     * @var bool
     */
    protected $_isGateway = false;
    /**
     * @var bool
     */
    /* END */

    /**
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return true;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * Get config payment action, do nothing if status is pending
     *
     * @return string|null
     */
    public function getConfigPaymentAction()
    {
        return $this->getConfigData('order_status') == 'pending' ? null : parent::getConfigPaymentAction();
    }
}
