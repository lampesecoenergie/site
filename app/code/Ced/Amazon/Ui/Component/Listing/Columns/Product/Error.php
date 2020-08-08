<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Ui\Component\Listing\Columns\Product;

use Magento\Framework\Json\Helper\Data;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Error
 * @package Ced\Amazon\Ui\Component\Listing\Columns\Product
 */
class Error extends Column
{
    /**
     * @var UrlInterface
     */
    public $urlBuilder;

    /**
     * @var Data
     */
    public $json;

    /**
     * Error constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param Data $json
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Data $json,
        $components = [],
        $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->json = $json;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                $feed = '{}';
                $feedValid = true;
                if (isset($item['amazon_feed_errors']) && !empty($item['amazon_feed_errors'])) {
                    $feed = $item['amazon_feed_errors'];
                    $feedValid = false;
                }

                if (isset($item[$fieldName])) {
                    $item[$fieldName . '_show_heading'] = true;
                    $item[$fieldName . '_product_feed_errors'] = $feed;
                    $item[$fieldName . '_product_feed_label'] =
                        "<tr><td class='cedcommerce errors feed' title='View Feed Errors'>F:</td><td class='cedcommerce errors'><div class='grid-severity-notice'><span>Valid</span></div></td></tr>";
                    if (!$feedValid) {
                        $item[$fieldName . '_product_feed_label'] =
                            "<tr><td class='cedcommerce errors feed' title='View Feed Errors'>F:</td><td class='cedcommerce errors'><div class='grid-severity-critical'><span>Invalid</span></div></td></tr>";
                    }

                    if ($item[$fieldName] == '["valid"]') {
                        $item[$fieldName . '_html'] =
                            "<tr><td class='cedcommerce errors validation' title='View Validation Errors'>V:</td><td class='cedcommerce errors'><div class='grid-severity-notice'><span>valid</span></div></td></tr>";
                        $item[$fieldName . '_title'] = __('Errors');
                        $item[$fieldName . '_productid'] = $item['entity_id'];
                    } else {
                        $item[$fieldName . '_html'] =
                            "<tr><td class='cedcommerce errors validation' title='View Validation Errors'>V:</td><td class='cedcommerce errors'><div class='grid-severity-critical'><span>invalid</span></div></td></tr>";
                        $item[$fieldName . '_title'] = __('Errors');
                        $item[$fieldName . '_productid'] = $item['entity_id'];
                        $item[$fieldName . '_productvalidation'] = $item[$fieldName];
                    }
                } else {
                    $item[$fieldName . '_html'] =
                        "<tr><td class='cedcommerce errors validation' title='View Validation Errors'>V:</td><td class='cedcommerce errors'><div class='grid-severity-notice'><span>NA</span></div></td></tr>";
                    $item[$fieldName . '_title'] = __('Errors');
                    $item[$fieldName . '_productid'] = $item['entity_id'];
                }
            }
        }
        return $dataSource;
    }
}
