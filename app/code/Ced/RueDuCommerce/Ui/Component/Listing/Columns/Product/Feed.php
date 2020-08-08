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
 * @package   Ced_RueDuCommerce
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\RueDuCommerce\Ui\Component\Listing\Columns\Product;

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
     * @var productHelper
     */
    public $productHelper;

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
        \Ced\RueDuCommerce\Helper\Product $productHelper,
        $components = [],
        $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->json = $json;
        $this->productHelper = $productHelper;
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
                $item[$fieldName . '_child_product_status'] = json_encode(array('Child Status' => 'No data available.'));
                $class = 'grid-severity-notice';
                $status = \Ced\RueDuCommerce\Model\Source\Product\Status::NOT_UPLOADED;
                if (isset($item[$fieldName])) {
                    if (isset($item['rueducommerce_product_status']) and !empty($item['rueducommerce_product_status'])) {
                        $status = $item['rueducommerce_product_status'];
                        if ($status == \Ced\RueDuCommerce\Model\Source\Product\Status::INVALID) {
                            $class = 'grid-severity-minor';
                        }
                    }

                    $item[$fieldName . '_html'] = "<button style='width:100%' class='{$class}'>"."<span>{$status}</span></button>";
                    $item[$fieldName . '_title'] = __('RueDuCommerce Feed Details');
                    $item[$fieldName . '_productid'] = $item['entity_id'];
                    if (isset($item['rueducommerce_feed_errors'])) {
                        $item[$fieldName . '_product_feed_errors'] = $item['rueducommerce_feed_errors'];
                    } else {
                        $item[$fieldName . '_product_feed_errors'] = json_encode(array('Data' => 'No data available.'));
                    }
                } else {
                    $item[$fieldName . '_html'] = "<button style='width:100%' class='{$class}'>"."<span>{$status}</span></button>";
                    $item[$fieldName . '_title'] = __('RueDuCommerce Feed Details');
                    $item[$fieldName . '_productid'] = $item['entity_id'];
                    if (isset($item['rueducommerce_feed_errors'])) {
                        $item[$fieldName . '_product_feed_errors'] = $item['rueducommerce_feed_errors'];
                    } else {
                        $item[$fieldName . '_product_feed_errors'] = json_encode(array('Data' => 'No data available.'));
                    }
                }
                $childStatus = $this->productHelper->getChildProductStatus($item['entity_id']);
                $item[$fieldName . '_child_product_status'] = json_encode($childStatus);
            }
        }
        return $dataSource;
    }
}
