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
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Observer\Adminhtml;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Visitor Observer
 */
class AfterLoadOrder implements ObserverInterface
{
    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * AfterLoadOrder constructor.
     * @param JsonHelper $jsonHelper
     */
    public function __construct(
        JsonHelper $jsonHelper
    ) {
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     */
    public function execute(EventObserver $observer)
    {
        $order = $observer->getOrder();
        if ($order->getCustomerAttribute()) {
            $customerAttribute = $this->jsonHelper->jsonDecode($order->getCustomerAttribute());
            foreach ($customerAttribute as $attr => $value) {
                $orderKey = sprintf('customer_%s', $attr);
                $order->setData($orderKey, $value);
            }
        }
        return $this;
    }
}
