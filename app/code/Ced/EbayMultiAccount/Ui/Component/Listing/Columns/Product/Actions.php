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

namespace Ced\EbayMultiAccount\Ui\Component\Listing\Columns\Product;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class Actions extends Column
{
    /** Url path */
    const URL_PATH_EDIT = 'catalog/product/edit';
    const URL_PATH_SYNC = 'ebaymultiaccount/product/additem';
    const URL_PATH_UPLOAD = 'ebaymultiaccount/product/additem';
    const URL_PATH_END = 'ebaymultiaccount/product/enditem';

    /**
     * @var UrlInterface
     */
    public $urlBuilder;

    /**
     * Actions constructor.
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
    )
    {
        $this->urlBuilder = $urlBuilder;
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
                if (isset($item['entity_id'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(self::URL_PATH_EDIT, ['id' => $item['entity_id']]),
                        'label' => __('Edit on Magento'),
                        'class' => 'cedcommerce actions edit'
                    ];
                    /*if (empty($item['ebaymultiaccount_item_id'])) {
                        $item[$name]['upload'] = [
                            'href' => $this->urlBuilder->getUrl(self::URL_PATH_UPLOAD, ['id' => $item['entity_id']]),
                            'label' => __('Upload on eBay'),
                            'class' => 'cedcommerce actions upload'
                        ];
                    } else {
                        $item[$name]['sync'] = [
                            'href' => $this->urlBuilder->getUrl(self::URL_PATH_SYNC, ['id' => $item['entity_id']]),
                            'label' => __('Sync With eBay'),
                            'class' => 'cedcommerce actions sync'
                        ];
                        $item[$name]['delete'] = [
                            'href' => $this->urlBuilder->getUrl(self::URL_PATH_END, ['id' => $item['entity_id']]),
                            'label' => __('End On eBay'),
                            'class' => 'cedcommerce actions delete'
                        ];
                    }*/
                }
            }
        }
        return $dataSource;
    }
}
