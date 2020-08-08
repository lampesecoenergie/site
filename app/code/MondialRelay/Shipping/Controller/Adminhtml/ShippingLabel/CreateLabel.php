<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Controller\Adminhtml\ShippingLabel;

use MondialRelay\Shipping\Model\Config\Source\Status;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order\Shipment;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Exception;

/**
 * Class CreateLabel
 */
class CreateLabel extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'MondialRelay_Shipping::label';

    /**
     * @var LabelGenerator $labelGenerator
     */
    protected $labelGenerator;

    /**
     * @var ShipmentRepositoryInterface $shipmentRepository
     */
    protected $shipmentRepository;

    /**
     * @param Context $context
     * @param LabelGenerator $labelGenerator
     * @param ShipmentRepositoryInterface $shipmentRepository
     */
    public function __construct(
        Context $context,
        LabelGenerator $labelGenerator,
        ShipmentRepositoryInterface $shipmentRepository
    ) {
        parent::__construct($context);

        $this->labelGenerator     = $labelGenerator;
        $this->shipmentRepository = $shipmentRepository;
    }

    /**
     * Create Shipping Label
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $shipmentId = $this->getRequest()->getParam('shipment_id');

        /** @var Shipment $shipment */
        $shipment = $this->shipmentRepository->get($shipmentId);
        $shipment->setShipmentStatus(Status::SHIPMENT_STATUS_MONDIAL_RELAY_PROCESSING);

        try {
            $this->labelGenerator->create($shipment, $this->getRequest());
            $this->shipmentRepository->save($shipment);

            $this->messageManager->addSuccessMessage(__('You created the shipping label.'));
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}
