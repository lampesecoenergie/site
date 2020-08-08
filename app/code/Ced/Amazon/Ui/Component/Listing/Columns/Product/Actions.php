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
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Ui\Component\Listing\Columns\Product;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class Actions extends Column
{
    /** Url path */
    const URL_PATH_EDIT = 'catalog/product/edit';
    const URL_PATH_VIEW = 'amazon/product/view';
    const URL_PATH_PREVIEW = 'amazon/product/feed_preview';
    const URL_PATH_SYNC = 'amazon/product/sync';

    /** @var UrlInterface */
    public $urlBuilder;

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
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
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
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                if (isset($item['entity_id'])) {
                    $sku = $item['sku'];
                    $item[$name]['view'] = [
                        'label' => __('View on Amazon'),
                        'class' => 'cedcommerce actions view',
                        'popup' => [
                            'title' => __("MWS Record #{$sku}"),
                            'file' =>  $this->urlBuilder->getUrl(
                                self::URL_PATH_VIEW,
                                ['sku' => $item['sku'], 'id' => $item['entity_id']]
                            ),
                            'type' => 'json',
                            'render' => 'html'
                        ],
                    ];
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(self::URL_PATH_EDIT, ['id' => $item['entity_id']]),
                        'label' => __('Edit'),
                        'class' => 'cedcommerce actions edit'
                    ];
                    $item[$name]['preview'] = [
                        'label' => __('Preview Amazon Feed'),
                        'class' => 'cedcommerce actions preview',
                        'popup' => [
                            'title' => __("Product Feed #{$sku}"),
                            'file' =>  $this->urlBuilder->getUrl(
                                self::URL_PATH_PREVIEW,
                                ['sku' => $item['sku'], 'id' => $item['entity_id']]
                            ),
                            'type' => 'xml',
                        ],
                    ];
                    $item[$name]['sync'] = [
                        'href' => $this->urlBuilder->getUrl(self::URL_PATH_SYNC, ['id' => $item['entity_id']]),
                        'label' => __('Sync Amazon Status'),
                        'class' => 'cedcommerce actions sync'
                    ];
                }
            }
        }
        return $dataSource;
    }
}
