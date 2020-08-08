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
use Magento\Framework\Phrase;

/**
 * Class Sync
 * @package Ced\Amazon\Controller\Adminhtml\Order\Shipment
 */
class Sync extends Action
{
    /** @var \Ced\Amazon\Helper\Shipment  */
    public $shipment;

    /** @var \Ced\Amazon\Model\Source\Shipment\Status  */
    public $options;

    public function __construct(
        Action\Context $context,
        \Ced\Amazon\Model\Source\Shipment\Status $options,
        \Ced\Amazon\Helper\Shipment $shipment
    ) {
        parent::__construct($context);
        $this->shipment = $shipment;
        $this->options = $options;
    }

    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $result = [
            'Status' => 'Submitted'
        ];

        if (isset($orderId, $shipmentId)) {
            $shipment = $this->shipment->sync($orderId, $shipmentId);

            if (isset($shipment['Status'])) {
                /** @var Phrase $option */
                $option = $this->options->getOptionText($shipment['Status']);
                if (!empty($option)) {
                    $status = $option->getText();
                } else {
                    $status = $shipment['Status'];
                }

                $result['Status'] = $status;
            }
        }

        /** @var \Magento\Framework\Controller\Result\Json $response */
        $response = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $response->setData($result);
        return $response;
    }
}
