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
 * @category  Ced
 * @package   Ced_Cdiscount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Ui\Component\Listing\Columns\Product;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Feed
 */
class Feed extends Column
{
    /**
     * @var UrlInterface
     */
    public $urlBuilder;

    /**
     * Json Parser
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $json;

    /**
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Magento\Framework\Json\Helper\Data $json,
        $components = [],
        $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->json = $json;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param  array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                $class = 'grid-severity-notice';
                $status = \Ced\Cdiscount\Model\Source\Product\Status::NOT_UPLOADED;
                if (isset($item[$fieldName])) {
                    if (isset($item['cdiscount_product_status']) and !empty($item['cdiscount_product_status'])) {
                        $status = $item['cdiscount_product_status'];
                        if ($status == \Ced\Cdiscount\Model\Source\Product\Status::INVALID) {
                            $class = 'grid-severity-minor';
                        }
                    }

                    $item[$fieldName . '_html'] = "<div class='{$class}'><span>{$status}</span></div>";
                    $item[$fieldName . '_title'] = __('Cdiscount Feed Details');
                    $item[$fieldName . '_productid'] = $item['entity_id'];
                    if (isset($item['cdiscount_feed_errors'])) {
                        $item[$fieldName . '_product_feed_errors'] = $item['cdiscount_feed_errors'];
                    } else {
                        $item[$fieldName . '_product_feed_errors'] = "{'No data available.'}";
                    }
                } else {
                    $item[$fieldName . '_html'] = "<div class='{$class}'><span>{$status}</span></div>";
                    $item[$fieldName . '_title'] = __('Cdiscount Feed Details');
                    $item[$fieldName . '_productid'] = $item['entity_id'];
                    if (isset($item['cdiscount_feed_errors'])) {
                        $item[$fieldName . '_product_feed_errors'] = $item['cdiscount_feed_errors'];
                    } else {
                        $item[$fieldName . '_product_feed_errors'] = "{'No data available.'}";
                    }
                }
            }
        }
        return $dataSource;
    }
}
