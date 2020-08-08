<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Model;

use MondialRelay\Shipping\Model\Config\Source\Code;
use MondialRelay\Shipping\Helper\Data as ShippingHelper;
use MondialRelay\Shipping\Api\Data\ShippingDataInterface;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Shipping\Model\Shipment\Request;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Zend_Measure_Weight;
use Zend_Pdf;
use Zend_Pdf_Style;
use Zend_Pdf_Font;

/**
 * Class Label
 */
class Label
{
    /**
     * @var ManagerInterface $eventManager
     */
    protected $eventManager;

    /**
     * @var ShippingHelper $shippingHelper
     */
    protected $shippingHelper;

    /**
     * @var FilterManager $filter
     */
    protected $filter;

    /**
     * @var Soap $soap
     */
    protected $soap;

    /**
     * @var ShipmentRepositoryInterface $shipmentRepository
     */
    protected $shipmentRepository;

    /**
     * @param ManagerInterface $eventManager
     * @param ShippingHelper $shippingHelper
     * @param FilterManager $filter
     * @param Soap $soap
     * @param ShipmentRepositoryInterface $shipmentRepository
     */
    public function __construct(
        ManagerInterface $eventManager,
        ShippingHelper $shippingHelper,
        FilterManager $filter,
        Soap $soap,
        ShipmentRepositoryInterface $shipmentRepository
    ) {
        $this->eventManager       = $eventManager;
        $this->shippingHelper     = $shippingHelper;
        $this->filter             = $filter;
        $this->soap               = $soap;
        $this->shipmentRepository = $shipmentRepository;
    }

    /**
     * Do request to shipment
     *
     * @param Request $request
     * @return DataObject
     */
    public function doShipmentRequest($request)
    {
        $response = new DataObject();

        $this->prepareRequest($request);

        if ($request->getData('error')) {
            return $response->setData(['errors' => $request->getData('error')]);
        }

        $this->eventManager->dispatch(
            'mondialrelay_label_do_shipment_before',
            ['request' => $request]
        );

        $this->generateLabel($request);

        if ($request->getData('error')) {
            return $response->setData(['errors' => $request->getData('error')]);
        }

        $response->setData('info', [
            [
                'tracking_number' => $request->getData('tracking_number'),
                'label_content'   => file_get_contents($request->getData('label_url')),
            ]
        ]);

        $this->eventManager->dispatch(
            'mondialrelay_label_do_shipment_after',
            ['request' => $request, 'response' => $response]
        );

        return $response;
    }

    /**
     * Prepare Request with specific data
     *
     * @param Request $request
     * @return Request
     */
    public function prepareRequest($request)
    {
        $order = $request->getOrderShipment()->getOrder();

        if (!is_array($request->getData('packages'))) {
            $packages = $this->initDefaultPackage($order);
            $request->setData('packages', $packages);
            $request->getOrderShipment()->setPackages($packages);
        }

        $packageCount = count($request->getData('packages'));

        $weight = $this->getPackageWeightParam($request->getData('packages'));
        $units  = $this->getPackageParam($request->getData('packages'), 'weight_units');
        if ($request->getData('forced_package_weight')) {
            $weight = $request->getData('forced_package_weight');
        }
        if (!$weight) {
            $weight = $request->getPackageWeight();
        }
        if (!$weight) {
            $weight = $order->getWeight();
        }
        if (!$units) {
            $units = $this->shippingHelper->getStoreWeightUnit($order->getStoreId(), true);
        }
        $packagingWeight = $order->getData(ShippingDataInterface::MONDIAL_RELAY_PACKAGING_WEIGHT);
        $weight += $packagingWeight * $packageCount;

        $packageWeight = $this->shippingHelper->convertWeight($weight, $units, Zend_Measure_Weight::KILOGRAM);
        $request->setPackageWeight(round($packageWeight * 1000));
        $request->getOrderShipment()->setTotalWeight($weight);

        $modLiv = $request->getData('mode_liv');
        if (!$modLiv) {
            $modLiv = $order->getShippingAddress()->getData(ShippingDataInterface::MONDIAL_RELAY_CODE);
        }

        if ($modLiv === Code::MONDIAL_RELAY_CODE_24R && $packageCount > 1) {
            $request->setData('error', __('Please select only one package.'));
        }

        $livRel = $request->getData('liv_rel');
        if (!$livRel) {
            $livRel = $order->getShippingAddress()->getData(ShippingDataInterface::MONDIAL_RELAY_PICKUP_ID);
        }

        $shipperName = $request->getShipperContactCompanyName();
        if (!empty($this->shippingHelper->getShipperName()) && !$request->getData('is_return')) {
            $shipperName = $this->shippingHelper->getShipperName();
        }

        $request->setData('store_id', $order->getStoreId());
        $request->setData('mode_col', 'CCC');
        $request->setData('mode_liv', $modLiv);
        $request->setData('nb_colis', $packageCount);
        $request->setData('n_dossier', $order->getIncrementId());
        $request->setData('n_client', $order->getCustomerId());
        $request->setData('crt_valeur', '0');
        $request->setData('expe_langage', $this->getLanguage($request->getShipperAddressCountryCode()));
        $request->setData('dest_langage', $this->getLanguage($request->getRecipientAddressCountryCode()));
        $request->setData('col_rel', '0');
        $request->setData('liv_rel', $livRel);
        $request->setData('assurance', $this->getPackageParam($request->getData('packages'), 'container'));
        $request->setData('shipper_contact_person_full_name', $shipperName);
        $request->setData(
            'recipient_contact_person_full_name',
            $request->getRecipientContactPersonFirstName() . ' ' . $request->getRecipientContactPersonLastName()
        );
        $request->setRecipientContactCompanyName(
            trim(preg_replace('/[^a-zA-Z0-9 ]/', '', $request->getRecipientContactCompanyName()))
        );
        $request->getOrderShipment()->setShipmentStatus($this->shippingHelper->getShippingStatus());

        return $request;
    }

    /**
     * Generate Label
     *
     * @param Request $request
     * @return Request
     */
    public function generateLabel($request)
    {
        $data = [];

        foreach ($this->getFields() as $field => $value) {
            if ($value['required']) {
                $data[$field] = $this->formatValue($request->getData($value['field']));
                if (is_null($data[$field])) {
                    $data[$field] = '';
                }
            }
            if (!$value['required']) {
                if ($request->getData($value['field'])) {
                    $data[$field] = $this->formatValue($request->getData($value['field']));
                }
            }
        }

        $response = $this->soap->execute('WSI2_CreationExpedition', $data);

        if ($response['error']) {
            return $request->setData('error', $response['error']);
        }

        $trackingNumber = $response['response']->ExpeditionNum;

        $data = [
            'Expeditions' => $trackingNumber,
            'Langue'      => $request->getData('dest_langage'),
        ];

        $response = $this->soap
            ->setStoreId($request->getData('store_id'))
            ->execute('WSI3_GetEtiquettes', $data);

        if ($response['error']) {
            return $request->setData('error', $response['response']->ExpeditionNum);
        }

        $labelSize = $this->shippingHelper->getLabelSize();

        $request->setData([
            'tracking_number' => $trackingNumber,
            'label_url'       => $this->shippingHelper->getRelayDomain() . $response['response']->$labelSize,
        ]);

        return $request;
    }

    /**
     * Init default package if request package is empty
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    protected function initDefaultPackage($order)
    {
        $items = [];

        $method = $order->getShippingMethod(true);
        $insurance = $this->shippingHelper->getInsurance($method->getData('carrier_code'), $method->getData('method'));

        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($order->getAllVisibleItems() as $item) {
            $qty = (int)$item->getQtyOrdered() - (int)$item->getQtyCanceled();

            if ($qty) {
                $items[] = [
                    'qty'           => $qty,
                    'customs_value' => '',
                    'name'          => $item->getName(),
                    'weight'        => $item->getWeight(),
                    'product_id'    => $item->getProductId(),
                    'order_item_id' => $item->getParentItemId(),
                    'price'         => $item->getPrice(),
                ];
            }
        }

        $packages = [
            1 => [
                'params' => [
                    'container'          => $insurance,
                    'weight'             => $order->getWeight(),
                    'customs_value'      => '',
                    'length'             => '',
                    'width'              => '',
                    'height'             => '',
                    'weight_units'       => $this->shippingHelper->getStoreWeightUnit($order->getStoreId(), true),
                    'dimension_units'    => 'CENTIMETER',
                    'content_type'       => '',
                    'content_type_other' => '',
                ],
                'items' => $items,
            ],
        ];

        return $packages;
    }

    /**
     * Retrieve request params
     *
     * @param array $packages
     * @param string $param
     * @return string|bool
     */
    protected function getPackageParam($packages, $param)
    {
        if (is_array($packages)) {
            foreach ($packages as $package) {
                if (isset($package['params'][$param])) {
                    return $package['params'][$param];
                }
            }
        }

        return false;
    }

    /**
     * Retrieve request params
     *
     * @param array $packages
     * @return string|bool
     */
    protected function getPackageWeightParam($packages)
    {
        $weight = 0;

        if (is_array($packages)) {
            foreach ($packages as $package) {
                if (isset($package['params']['weight'])) {
                    $weight += (float) $package['params']['weight'];
                }
            }
        }

        return $weight;
    }

    /**
     * Format string value
     *
     * @param string $value
     * @return string
     */
    protected function formatValue($value)
    {
        return strtoupper($this->filter->removeAccents($value));
    }

    /**
     * Retrieve country language
     *
     * @param string $country
     * @return string
     */
    protected function getLanguage($country)
    {
        $languages = [
            'FR' => 'FR',
            'BE' => 'NL',
            'ES' => 'ES',
        ];

        return isset($languages[$country]) ? $languages[$country] : 'FR';
    }

    /**
     * Retrieve required fields
     */
    protected function getFields()
    {
        return [
            'ModeCol'      => ['field' => 'mode_col', 'required' => 1],
            'ModeLiv'      => ['field' => 'mode_liv', 'required' => 1],
            'NDossier'     => ['field' => 'n_dossier', 'required' => 0],
            'NClient'      => ['field' => 'n_client', 'required' => 0],
            'Expe_Langage' => ['field' => 'expe_langage', 'required' => 1],
            'Expe_Ad1'     => ['field' => 'shipper_contact_person_full_name', 'required' => 1],
            'Expe_Ad2'     => ['field' => 'shipper_contact_company_name', 'required' => 0],
            'Expe_Ad3'     => ['field' => 'shipper_address_street_1', 'required' => 1],
            'Expe_Ad4'     => ['field' => 'shipper_address_street_2', 'required' => 0],
            'Expe_Ville'   => ['field' => 'shipper_address_city', 'required' => 1],
            'Expe_CP'      => ['field' => 'shipper_address_postal_code', 'required' => 1],
            'Expe_Pays'    => ['field' => 'shipper_address_country_code', 'required' => 1],
            'Expe_Tel1'    => ['field' => 'shipper_contact_phone_number', 'required' => 1],
            'Expe_Tel2'    => ['field' => 'shipping_contact_phone_number', 'required' => 0],
            'Expe_Mail'    => ['field' => 'shipper_email', 'required' => 0],
            'Dest_Langage' => ['field' => 'dest_langage', 'required' => 1],
            'Dest_Ad1'     => ['field' => 'recipient_contact_person_full_name', 'required' => 1],
            'Dest_Ad2'     => ['field' => 'recipient_contact_company_name', 'required' => 0],
            'Dest_Ad3'     => ['field' => 'recipient_address_street_1', 'required' => 1],
            'Dest_Ad4'     => ['field' => 'recipient_address_street_2', 'required' => 0],
            'Dest_Ville'   => ['field' => 'recipient_address_city', 'required' => 1],
            'Dest_CP'      => ['field' => 'recipient_address_postal_code', 'required' => 1],
            'Dest_Pays'    => ['field' => 'recipient_address_country_code', 'required' => 1],
            'Dest_Tel1'    => ['field' => 'recipient_contact_phone_number', 'required' => 1],
            'Dest_Tel2'    => ['field' => 'recipient_contact_phone_number', 'required' => 0],
            'Dest_Mail'    => ['field' => 'recipient_email', 'required' => 0],
            'Poids'        => ['field' => 'package_weight', 'required' => 1],
            'NbColis'      => ['field' => 'nb_colis', 'required' => 1],
            'CRT_Valeur'   => ['field' => 'crt_valeur', 'required' => 1],
            'CRT_Devise'   => ['field' => 'crt_devise', 'required' => 0],
            'Exp_Valeur'   => ['field' => 'exp_valeur', 'required' => 0],
            'Exp_Devise'   => ['field' => 'exp_devise', 'required' => 0],
            'COL_Rel_Pays' => ['field' => 'shipper_address_country_code', 'required' => 0],
            'COL_Rel'      => ['field' => 'col_rel', 'required' => 1],
            'LIV_Rel_Pays' => ['field' => 'recipient_address_country_code', 'required' => 0],
            'LIV_Rel'      => ['field' => 'liv_rel', 'required' => 0],
            'TAvisage'     => ['field' => 't_avisage', 'required' => 0],
            'TReprise'     => ['field' => 't_reprise', 'required' => 0],
            'Montage'      => ['field' => 'montage', 'required' => 0],
            'TRDV'         => ['field' => 'trdv', 'required' => 0],
            'Assurance'    => ['field' => 'assurance', 'required' => 0],
            'Instructions' => ['field' => 'instructions', 'required' => 0],
        ];
    }

    /**
     * Delete Label
     *
     * @param int $shipmentId
     * @return bool
     */
    public function deleteLabel($shipmentId)
    {
        $shipment = $this->shipmentRepository->get($shipmentId);

        if (!$shipment->getEntityId()) {
            return false;
        }

        if ($shipment->getShippingLabel()) {
            $shipment->setShippingLabel(null);
            $this->shipmentRepository->save($shipment);
        }

        return true;
    }

    /**
     * Generate Error Label file
     *
     * @param array $messages
     * @return string
     */
    public function generateErrorLabel($messages)
    {
        $pdf = new Zend_Pdf();
        $pdf->pages[] = $pdf->newPage($this->shippingHelper->getPdfPageSize());
        $page = $pdf->pages[0];
        $style = new Zend_Pdf_Style();
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $style->setFont($font, 8);
        $page->setStyle($style);
        $height = $page->getHeight() - 20;
        foreach ($messages as $message) {
            $page->drawText($message, 4, $height);
            $height = $height - 20;
        }

        return $pdf->render();
    }
}
