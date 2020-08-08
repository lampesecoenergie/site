<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Plugin;

use MondialRelay\Shipping\Model\Carrier\MondialRelay;
use Magento\Shipping\Controller\Adminhtml\Order\Shipment\Save;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Message\ManagerInterface;
use Exception;

/**
 * Class Create
 */
class Create
{
    /**
     * @var LabelGenerator $labelGenerator
     */
    protected $labelGenerator;

    /**
     * @var ShipmentRepositoryInterface $shipmentRepository
     */
    protected $shipmentRepository;

    /**
     * @var SearchCriteriaBuilder $searchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder $sortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * @var ManagerInterface $messageManager
     */
    protected $messageManager;

    /**
     * @param LabelGenerator $labelGenerator
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        LabelGenerator $labelGenerator,
        ShipmentRepositoryInterface $shipmentRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        ManagerInterface $messageManager
    ) {
        $this->labelGenerator        = $labelGenerator;
        $this->shipmentRepository    = $shipmentRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder      = $sortOrderBuilder;
        $this->messageManager        = $messageManager;
    }

    /**
     * After afterGetContentTypes
     *
     * @param Save $subject
     * @param array $result
     * @return array
     */
    public function afterExecute(Save $subject, $result)
    {
        $data    = $subject->getRequest()->getParam('shipment');
        $orderId = $subject->getRequest()->getParam('order_id');

        $isNeedCreateLabel = isset($data['create_shipping_label']) && $data['create_shipping_label'];
        if ($isNeedCreateLabel) {
            return $result;
        }

        $isNeedAutoCreateLabel = isset($data['auto_create_shipping_label']) && $data['auto_create_shipping_label'];

        if ($isNeedAutoCreateLabel && $orderId) {
            try {
                $sortOrder = $this->sortOrderBuilder
                    ->setField('created_at')
                    ->setDirection(SortOrder::SORT_DESC)
                    ->create();

                $this->searchCriteriaBuilder->addFilter('order_id', $orderId);
                $this->searchCriteriaBuilder->addSortOrder($sortOrder);
                $this->searchCriteriaBuilder->setPageSize(1);

                $searchCriteria = $this->searchCriteriaBuilder->create();

                $shipments = $this->shipmentRepository->getList($searchCriteria);

                /** @var \Magento\Sales\Model\Order\Shipment $shipment */
                foreach ($shipments as $shipment) {
                    $method = $shipment->getOrder()->getShippingMethod(true);
                    if ($method->getData('carrier_code') !== MondialRelay::SHIPPING_CARRIER_CODE) {
                        continue;
                    }
                    $this->labelGenerator->create($shipment, $subject->getRequest());
                    $this->shipmentRepository->save($shipment);
                    $this->messageManager->addSuccessMessage(
                        __('You created the shipping label.')
                    );
                }
            } catch (Exception $exception) {
                $this->messageManager->addErrorMessage(
                    __('An error occurred while creating shipping label: %1', $exception->getMessage())
                );
            }
        }

        return $result;
    }
}
