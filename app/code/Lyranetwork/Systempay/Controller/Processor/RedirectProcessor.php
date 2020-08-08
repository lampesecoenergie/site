<?php
/**
 * Systempay V2-Payment Module version 2.3.2 for Magento 2.x. Support contact : supportvad@lyra-network.com.
 *
 * NOTICE OF LICENSE
 *
 * This source file is licensed under the Open Software License version 3.0
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/osl-3.0.php
 *
 * @category  Payment
 * @package   Systempay
 * @author    Lyra Network (http://www.lyra-network.com/)
 * @copyright 2014-2018 Lyra Network and contributors
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Lyranetwork\Systempay\Controller\Processor;

class RedirectProcessor
{

    /**
     *
     * @var \Lyranetwork\Systempay\Helper\Data
     */
    protected $dataHelper;

    /**
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     *
     * @param \Lyranetwork\Systempay\Helper\Data $dataHelper
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(\Lyranetwork\Systempay\Helper\Data $dataHelper, \Magento\Framework\Registry $coreRegistry)
    {
        $this->dataHelper = $dataHelper;
        $this->coreRegistry = $coreRegistry;
    }

    public function execute(\Lyranetwork\Systempay\Api\RedirectActionInterface $controller)
    {
        try {
            $order = $controller->getAndCheckOrder();

            // add history comment and save it
            $order->addStatusHistoryComment(__('Client sent to Systempay gateway.'), false)
                ->setIsCustomerNotified(false)
                ->save();

            $method = $order->getPayment()->getMethodInstance();
            $this->coreRegistry->register(
                \Lyranetwork\Systempay\Block\Constants::PARAMS_REGISTRY_KEY,
                $method->getFormFields($order)
            );
            $this->coreRegistry->register(
                \Lyranetwork\Systempay\Block\Constants::URL_REGISTRY_KEY,
                $method->getPlatformUrl()
            );

            // redirect to gateway
            $this->dataHelper->log("Client {$order->getCustomerEmail()} sent to payment page for order #{$order->getId()}.");

            return $controller->forward();
        } catch (\Lyranetwork\Systempay\Model\OrderException $e) {
            return $controller->back($e->getMessage());
        }
    }
}
