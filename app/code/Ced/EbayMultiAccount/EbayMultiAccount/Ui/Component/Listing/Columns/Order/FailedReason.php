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
 * @package     Ced_EbayMultiAccount
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\EbayMultiAccount\Ui\Component\Listing\Columns\Order;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Json\Helper\Data;

/**
 * Class FailedReason
 * @package Ced\EbayMultiAccount\Ui\Component\Listing\Columns\Product
 */
class FailedReason extends Column
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
     * @var
     */
    public $product;

    /**
     * FailedReason constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param Data $json
     * @param ProductFactory $productFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Data $json,
        ProductFactory $productFactory,
        $components = [],
        $data = []
    )
    {
        $this->product = $productFactory->create();
        $this->urlBuilder = $urlBuilder;
        $this->json = $json;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                if (isset($item['id'])) {
                    $item[$name]['view'] = [
                        'label' => __('View Order'),
                        'class' => 'cedcommerce actions view',
                        'popup' => [
                            'title' => __("EbayMultiAccount Order ID #{$item['ebaymultiaccount_order_id']}"),
                            'file' => $this->urlBuilder->getUrl(
                                self::URL_PATH_VIEW,
                                ['id' => $item['id']]
                            ),
                            'type' => 'json',
                            'render' => 'html',
                        ],
                    ];
                }
            }
        }
        return $dataSource;
    }
}
