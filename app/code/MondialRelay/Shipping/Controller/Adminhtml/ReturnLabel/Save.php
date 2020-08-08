<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Controller\Adminhtml\ReturnLabel;

use MondialRelay\Shipping\Model\Label;
use MondialRelay\Shipping\Model\Pickup;
use MondialRelay\Shipping\Helper\Data as ShippingHelper;
use MondialRelay\Shipping\Model\Config\Source\Code;
use MondialRelay\Shipping\Model\Config\Source\ReturnType;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Shipping\Model\Shipment\Request;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Exception;

/**
 * Class Save
 */
class Save extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'MondialRelay_Shipping::label';

    /**
     * @var Label $label
     */
    protected $label;

    /**
     * @var Pickup $pickup
     */
    protected $pickup;

    /**
     * @var Request $request
     */
    protected $request;

    /**
     * @var OrderFactory $orderFactory
     */
    protected $orderFactory;

    /**
     * @var OrderRepositoryInterface $orderRepositoryInterface
     */
    protected $orderRepositoryInterface;

    /**
     * @var ShippingHelper $shippingHelper
     */
    protected $shippingHelper;

    /**
     * @var DataPersistorInterface $dataPersistor
     */
    protected $dataPersistor;

    /**
     * @var ManagerInterface $eventManager
     */
    protected $eventManager;

    /**
     * @param Context $context
     * @param Label $label
     * @param Pickup $pickup
     * @param Request $request
     * @param OrderFactory $orderFactory
     * @param OrderRepositoryInterface $orderRepositoryInterface
     * @param ShippingHelper $shippingHelper
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        Label $label,
        Pickup $pickup,
        Request $request,
        OrderFactory $orderFactory,
        OrderRepositoryInterface $orderRepositoryInterface,
        ShippingHelper $shippingHelper,
        DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context);

        $this->label                    = $label;
        $this->pickup                   = $pickup;
        $this->request                  = $request;
        $this->orderFactory             = $orderFactory;
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->shippingHelper           = $shippingHelper;
        $this->eventManager             = $context->getEventManager();
        $this->dataPersistor            = $dataPersistor;
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $order = $this->orderFactory->create();

            if ($data['parent_id']) {
                $order = $this->orderRepositoryInterface->get($data['parent_id']);

                /** @var \Magento\Sales\Model\Order\Address $address */
                $address = $order->getBillingAddress();
                $address->addData($data);
                $this->dataPersistor->set('address', $address->getData());
            }

            if (!isset($data['recipient_return_type'])) {
                throw new Exception(__('Missing Return type'));
            }

            $recipient = [
                'recipient_company'     => $data['recipient_company'],
                'recipient_street'      => $data['recipient_street'],
                'recipient_postcode'    => $data['recipient_postcode'],
                'recipient_city'        => $data['recipient_city'],
                'recipient_country'     => $data['recipient_country'],
                'recipient_telephone'   => $data['recipient_telephone'],
                'recipient_email'       => $data['recipient_email'],
                'recipient_pickup'      => null,
                'recipient_return_type' => 'LCC',
            ];

            if ($data['recipient_return_type'] == ReturnType::MONDIAL_RELAY_RETURN_TYPE_RELAY) {
                list($pickupId, $code) = explode('-', $data['recipient_pickup']);
                $pickup = $this->pickup->load($pickupId, $data['recipient_country']);

                $recipient['recipient_street']      = trim($pickup->getLgadr3());
                $recipient['recipient_postcode']    = $pickup->getCp();
                $recipient['recipient_city']        = trim($pickup->getVille());
                $recipient['recipient_country']     = $pickup->getPays();
                $recipient['recipient_pickup']      = $pickupId;
                $recipient['recipient_return_type'] = $code;
            }

            $this->request->setData(
                [
                    'is_return'                           => true,
                    'order_shipment'                      => $order->getShipmentsCollection()->getFirstItem(),
                    'mode_liv'                            => $recipient['recipient_return_type'],
                    'liv_rel'                             => $recipient['recipient_pickup'],
                    'shipper_contact_person_first_name'   => $data['firstname'],
                    'shipper_contact_person_last_name'    => $data['lastname'],
                    'shipper_contact_company_name'        => $data['company'] ?: $data['firstname'] . ' ' . $data['lastname'],
                    'shipper_address_street_1'            => $data['street_1'],
                    'shipper_address_street_2'            => $data['street_2'],
                    'shipper_address_city'                => $data['city'],
                    'shipper_address_postal_code'         => $data['postcode'],
                    'shipper_address_country_code'        => $data['country_id'],
                    'shipper_contact_phone_number'        => $data['telephone'],
                    'shipper_email'                       => $data['email'],
                    'recipient_contact_person_first_name' => $recipient['recipient_company'],
                    'recipient_contact_company_name'      => $recipient['recipient_company'],
                    'recipient_address_street_1'          => $recipient['recipient_street'],
                    'recipient_address_city'              => $recipient['recipient_city'],
                    'recipient_address_postal_code'       => $recipient['recipient_postcode'],
                    'recipient_address_country_code'      => $recipient['recipient_country'],
                    'recipient_contact_phone_number'      => $recipient['recipient_telephone'],
                    'recipient_email'                     => $recipient['recipient_email'],
                    'forced_package_weight'               => $data['weight'],
                ]
            );

            $this->eventManager->dispatch(
                'mondialrelay_return_label_before',
                ['request' => $this->request, 'order' => $order]
            );

            $response = $this->label->doShipmentRequest($this->request);

            if ($response->getData('errors')) {
                $this->messageManager->addErrorMessage(
                    __('Return label generation error: %1.', $response->getData('errors'))
                );
            } else {
                $info  = $response->getData('info');
                $label = reset($info);
                $file  = $this->shippingHelper->writeReturnLabel($label['label_content'], $order);

                $this->eventManager->dispatch(
                    'mondialrelay_return_label_after',
                    [
                        'request'  => $this->request,
                        'order'    => $order,
                        'file'     => $file,
                        'tracking' => $label['tracking_number']
                    ]
                );

                $this->dataPersistor->clear('address');

                $this->messageManager->addSuccessMessage(
                    __('The return label has been generated')
                );
            }
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Return label generation error: %1.', $e->getMessage())
            );
        }

        return $resultRedirect->setPath('*/*/', ['address_id' => $data['entity_id']]);
    }
}
