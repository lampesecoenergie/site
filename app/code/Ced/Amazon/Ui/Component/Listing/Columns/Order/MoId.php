<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 7/10/19
 * Time: 4:01 PM
 */

namespace Ced\Amazon\Ui\Component\Listing\Columns\Order;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class MoId extends Column
{
    public $urlBuilder;
    const URL_PATH_EDIT = 'sales/order/view';

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = 'magento_increment_id';
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item[$fieldName], $item['magento_order_id'])) {
                    $url = $this->urlBuilder->getUrl(
                        self::URL_PATH_EDIT,
                        [
                            'order_id' => $item['magento_order_id']
                        ]
                    );
                    $html = "<a href='" . $url . "' target='_blank'>";
                    $html .= $item[$fieldName];
                    $html .= "</a>";
                    $item[$fieldName . '_html'] = $html;
                }
            }
        }
        return $dataSource;
    }
}