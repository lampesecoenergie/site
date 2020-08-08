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
 * @package   Ced_Cdiscount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Controller\Adminhtml\Order;

use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;

class Ship extends \Magento\Backend\App\Action
{
    public $resultJsonFactory;

    public $json;


    public $order;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Json\Helper\Data $json,
        \Ced\Cdiscount\Helper\Order $order
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->json = $json;
        $this->order = $order;
    }

    public function execute()
    {

        $response = [
            'message' => [],
            'success' => false
        ];

        $data = $this->getRequest()->getParams();

        // cleaning data
        if (isset($data['form_key'])) {
            unset($data['form_key']);
        }

        if (isset($data['key'])) {
            unset($data['key']);
        }

        if (isset($data['isAjax'])) {
            unset($data['isAjax']);
        }

        if (isset($data['shipments']) and is_array($data['shipments'])) {
            foreach ($data['shipments'] as $item) {
                $shipment = $this->order->shipOrder($item);
                if ($shipment['success'] === true) {
                    $response['message'][] = 'Order shipment sent successfully.';
                    $response['success'] = true;
                } else {
                    $response['message'][] = $shipment['message'];
                }
            }
        }

        if (isset($data['cancellations']) and is_array($data['cancellations'])) {
            foreach ($data['cancellations'] as $item) {
                $cancellation = $this->order->cancelOrder($item);
                if ($cancellation['success'] === true) {
                    $response['message'][] = 'Order cancellations sent successfully.';
                    $response['success'] = true;
                } else {
                    $response['success'] = false;
                    $response['message'][] = $cancellation['message'];
                }
            }
        }

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_Cdiscount::cdiscount_orders');
    }
}
