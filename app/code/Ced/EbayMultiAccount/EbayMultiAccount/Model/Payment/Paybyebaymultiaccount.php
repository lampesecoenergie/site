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
 * @package     Ced_EbayMultiAccount
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\EbayMultiAccount\Model\Payment;

class Paybyebaymultiaccount extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * @var string
     */
    public $_code = 'paybyebaymultiaccount';
    /**
     * @var bool
     */
    public $_canAuthorize = true;
    /**
     * @var bool
     */
    public $_canCancelInvoice = false;
    /**
     * @var bool
     */
    public $_canCapture = false;
    /**
     * @var bool
     */
    public $_canCapturePartial = false;
    /**
     * @var bool
     */
    public $_canCreateBillingAgreement = false;
    /**
     * @var bool
     */
    public $_canFetchTransactionInfo = false;
    /**
     * @var bool
     */
    public $_canManageRecurringProfiles = false;
    /**
     * @var bool
     */
    public $_canOrder = false;
    /**
     * @var bool
     */
    public $_canRefund = false;
    /**
     * @var bool
     */
    public $_canRefundInvoicePartial = false;
    /**
     * @var bool
     */
    public $_canReviewPayment = false;
    /**
     * @var bool
     */
    /* Setting for disable from front-end. */
    /* START */
    public $_canUseCheckout = false;
    /**
     * @var bool
     */
    public $_canUseForMultishipping = false;
    /**
     * @var bool
     */
    public $_canUseInternal = false;
    /**
     * @var bool
     */
    public $_canVoid = false;
    /**
     * @var bool
     */
    public $_isGateway = false;
    /**
     * @var bool
     */
    public $_isInitializeNeeded = false;

    /* END */


    /**
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null) {
        return true;
    }

    /**
     * @return string
     */
    public function getCode() {
        return $this->_code;
    }
}
