<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-present BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomerAttributes\Observer\Integration\B2BRegistration;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class AddNewFormAttribute
 * @package Bss\CustomerAttributes\Observer\Integration\B2bReIntegration
 */
class AddNewFormAttribute implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $usedInForms = $observer->getEvent()->getData('usedInForms');
        $data = $observer->getEvent()->getData('dataPost');
        $num = count($usedInForms->getData()) + 1;
        if (isset($data['b2b_account_create']) && $data['b2b_account_create'] == 1) {
            $usedInForms[$num] = 'b2b_account_create';
        }
        if (isset($data['b2b_account_edit']) && $data['b2b_account_edit'] == 1) {
            $usedInForms[$num + 1] = 'b2b_account_edit';
        }
    }
}
