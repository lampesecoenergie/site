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

namespace Ced\EbayMultiAccount\Controller\Adminhtml\AccountConfig;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;

/**
 * Class Save
 * @package Ced\EbayMultiAccount\Controller\Adminhtml\AccountConfig
 */
class Save extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_EbayMultiAccount::EbayMultiAccount';

    /**
     * Save constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Ced\EbayMultiAccount\Helper\Cache $cache
     */
    public function __construct(
        Context $context,
        \Ced\EbayMultiAccount\Model\AccountConfigFactory $accountconfig
    )
    {
        parent::__construct($context);
        $this->accountconfig = $accountconfig;
    }

    public function execute()
    {
        $accountDetails = $this->getRequest()->getParams();
        try {
            if (isset($accountDetails['config_name']) || isset($accountDetails['id'])) {
                $check = $this->validateData($accountDetails);
                if (isset($check['error'])) {
                    $this->messageManager->addErrorMessage(__($check['error']));
                    $this->_redirect('*/*/new');
                    return;
                }
                $addData = [];
                if (isset($accountDetails['id'])) {
                    $accountconfig = $this->accountconfig->create()->load($accountDetails['id']);
                } else {
                    $addData['config_name'] = $accountDetails['config_name'];
                    $accountconfig = $this->accountconfig->create();
                }
                $domesticService = $internationalService = [];
                if (isset($accountDetails['domesticService']) && !empty($accountDetails['domesticService'])) {
                    foreach ($accountDetails['domesticService'] as $value) {
                        unset($value['delete']);
                        if ($value['service'] == '') {
                            unset($value['service']);
                            unset($value['charge']);
                            unset($value['add_charge']);
                            continue;
                        }
                        $domesticService[] = $value;
                    }
                }
                if (isset($accountDetails['internationalService']) && !empty($accountDetails['internationalService'])) {
                    foreach ($accountDetails['internationalService'] as $value) {
                        unset($value['delete']);
                        if ($value['service'] == '') {
                            unset($value['service']);
                            unset($value['charge']);
                            unset($value['add_charge']);
                            continue;
                        }
                        $internationalService[] = $value;
                    }
                }
                if (isset($accountDetails['return_accepted']) &&  $accountDetails['return_accepted'] == 'ReturnsNotAccepted') {
                    unset($accountDetails['refund_type']);
                    unset($accountDetails['return_days']);
                    unset($accountDetails['ship_cost_paidby']);
                }
                $addData['account_location'] =  $accountDetails['account_location'];
                $addData['payment_details'] = json_encode([
                    'payment_method' => isset($accountDetails['payment_method']) ? implode(',', $accountDetails['payment_method']) : '', 
                    'paypal_email' => isset($accountDetails['paypal_email']) ? $accountDetails['paypal_email'] : ''
                    ]);
                $addData['shipping_details'] = json_encode([
                    'service_type' => isset($accountDetails['service_type']) ? $accountDetails['service_type'] : '',
                    'free_shipping' => isset($accountDetails['free_shipping']) ? $accountDetails['free_shipping'] : '',
                    'sale_tax_rate' => isset($accountDetails['sale_tax_rate']) ? $accountDetails['sale_tax_rate'] : '',
                    'sale_tax_state' => isset($accountDetails['sale_tax_state']) ? $accountDetails['sale_tax_state'] : '',
                    'shipping_includes' => isset($accountDetails['shipping_includes']) ? $accountDetails['shipping_includes'] : '',
                    'ship_to_location' => isset($accountDetails['ship_to_location']) ? implode(',', $accountDetails['ship_to_location']) : '',
                    'excluded_area' => isset($accountDetails['excluded_area']) ? implode(',', $accountDetails['excluded_area']) : '',
                    'global_shipping' => isset($accountDetails['global_shipping']) ? $accountDetails['global_shipping'] : 0,
                    'domesticService' => $domesticService,
                    'internationalService' => $internationalService,
                    ]);
                $addData['return_policy'] = json_encode([
                    'return_accepted' => isset($accountDetails['return_accepted']) ? $accountDetails['return_accepted'] : '',
                    'refund_type' => isset($accountDetails['refund_type']) ? $accountDetails['refund_type'] : '',
                    'return_days' => isset($accountDetails['return_days']) ? $accountDetails['return_days'] : '',
                    'return_description' => isset($accountDetails['return_description']) ? $accountDetails['return_description'] : '',
                    'ship_cost_paidby' => isset($accountDetails['ship_cost_paidby']) ? $accountDetails['ship_cost_paidby'] : ''
                    ]);
                $accountconfig->addData($addData)->save();
                $this->_redirect('*/*/edit', ['id' => $accountconfig->getId()]);
            } else {
                $this->messageManager->addNoticeMessage(__('Please fill the Configuration Name'));
                $this->_redirect('*/*/new');
            }
            
        } catch (\Exception $e) {
            $this->_objectManager->create('Ced\EbayMultiAccount\Helper\Logger')->addError('In Save Account Configuration: ' . $e->getMessage(), ['path' => __METHOD__]);
            $this->messageManager->addErrorMessage(__('Unable to Save Account Details Please Try Again.' . $e->getMessage()));
            $this->_redirect('*/*/new');
        }
        return;
    }

    public function validateData($accountDetails, $check=[], $error=[])
    {
        if (isset($accountDetails['payment_method']) && in_array('PayPal', $accountDetails['payment_method'])) {
            if (!isset($accountDetails['paypal_email']) || $accountDetails['paypal_email'] == '') {
                $error[] = 'Please fill the paypal email';
            }
        }

        if (!isset($accountDetails['domesticService']) || count($accountDetails['domesticService']) < 1) {
            $error[] = 'Please add atleast one Shipping services';
        }

        if (isset($accountDetails['global_shipping']) && $accountDetails['global_shipping'] == 1) {
            if (!isset($accountDetails['internationalService']) || count($accountDetails['internationalService']) < 1) {
                $error[] = 'Please add atleast one International shipping services';
            }
            if (!isset($accountDetails['ship_to_location']) || empty($accountDetails['ship_to_location'])) {
                $error[] = 'Please fill Ship To location Field';
            }
        }

        if (isset($accountDetails['return_accepted']) && $accountDetails['return_accepted'] == 'ReturnsAccepted') {
            if (isset($accountDetails['refund_type']) && $accountDetails['refund_type'] == '') {
                $error[] = 'Please fill the refund type';
            }
        }

        if (!empty($error)) {
            $check['error'] = implode(', ', $error);
        }
        return $check;
    }
}