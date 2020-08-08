<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Actions
 */
class Actions extends Column
{
    const LABEL_PRINT_URL_PATH = 'adminhtml/order_shipment/printLabel';

    const LABEL_CREATE_URL_PATH = 'mondialrelay_shipping/shippingLabel/createLabel';

    const SHIPMENT_PACKING_SLIP_URL_PATH = 'sales/shipment/print';

    const SHIPMENT_CREATE_URL_PATH = 'mondialrelay_shipping/shippingLabel/createShipment';

    const SHIPMENT_TRACKING_URL_PATH = 'mondialrelay_shipping/shippingLabel/trackShipment';

    /**
     * @var UrlInterface $urlBuilder
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['order_id'])) {
                    if (!$item['shipment_id']) {
                        $item[$this->getData('name')] = [
                            'action' => [
                                'href' => $this->urlBuilder->getUrl(
                                    static::SHIPMENT_CREATE_URL_PATH,
                                    [
                                        'order_id' => $item['order_id']
                                    ]
                                ),
                                'label' => __('Create Shipment and Label')
                            ]
                        ];
                    } else {
                        $item[$this->getData('name')] = [
                            'action_1' => [
                                'href' => $this->urlBuilder->getUrl(
                                    static::LABEL_CREATE_URL_PATH,
                                    [
                                        'shipment_id' => $item['shipment_id']
                                    ]
                                ),
                                'label' => __('Create Label')
                            ],
                            'action_2' => [
                                'href' => $this->urlBuilder->getUrl(
                                    static::SHIPMENT_PACKING_SLIP_URL_PATH,
                                    [
                                        'shipment_id' => $item['shipment_id']
                                    ]
                                ),
                                'label' => __('Print Packing Slip')
                            ]
                        ];

                        if ($item['has_label']) {
                            $item[$this->getData('name')] = [
                                'action_1' => [
                                    'href' => $this->urlBuilder->getUrl(
                                        static::LABEL_PRINT_URL_PATH,
                                        [
                                            'shipment_id' => $item['shipment_id']
                                        ]
                                    ),
                                    'label' => __('Print Label')
                                ],
                                'action_2' => [
                                    'href' => $this->urlBuilder->getUrl(
                                        static::SHIPMENT_PACKING_SLIP_URL_PATH,
                                        [
                                            'shipment_id' => $item['shipment_id']
                                        ]
                                    ),
                                    'label' => __('Print Packing Slip')
                                ]
                            ];
                        }
                    }
                }
            }
        }

        return $dataSource;
    }
}
