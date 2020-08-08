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

namespace Ced\Amazon\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action;

/**
 * Class Delete
 * @package Ced\Amazon\Controller\Adminhtml\Order\Shipment
 */
class Delete extends Action
{
    /** @var \Ced\Amazon\Helper\Shipment  */
    public $shipment;

    /**
     * Delete constructor.
     * @param Action\Context $context
     * @param \Ced\Amazon\Helper\Shipment $shipment
     */
    public function __construct(
        Action\Context $context,
        \Ced\Amazon\Helper\Shipment $shipment
    ) {
        parent::__construct($context);
        $this->shipment = $shipment;
    }

    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $shipmentId = $this->getRequest()->getParam('shipment_id');

        $status = false;
        if (isset($orderId, $shipmentId)) {
            $status = $this->shipment->delete($orderId, $shipmentId);
        }

        /** @var \Magento\Framework\Controller\Result\Json $response */
        $response = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $response->setData(
            [
                'status' => $status
            ]
        );

        return $response;
    }
}
