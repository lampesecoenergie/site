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
 * Class ProductValidation
 */
class Validation extends Column
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
                if (isset($item[$fieldName])) {
                    if ($item[$fieldName] == '["valid"]') {
                        $item[$fieldName . '_html'] = "<div class='grid-severity-notice'><span>valid</span></div>";
                        $item[$fieldName . '_title'] = __('RueDuCommerce Product Details');
                        $item[$fieldName . '_productid'] = $item['entity_id'];
                    } else {
                        $item[$fieldName . '_html'] =
                            '<button style="width:100%" class="grid-severity-critical">invalid</button>';
                        $item[$fieldName . '_title'] = __('RueDuCommerce Product Details');
                        $item[$fieldName . '_productid'] = $item['entity_id'];
                        $item[$fieldName . '_productvalidation'] = $item[$fieldName];
                    }
                } else {
                    $item[$fieldName . '_html'] = '<div class="grid-severity-notice"><span>
                        not validated</span></div>';
                    $item[$fieldName . '_title'] = __('RueDuCommerce Product Details');
                    $item[$fieldName . '_productid'] = $item['entity_id'];
                }
            }
        }
        return $dataSource;
    }
}
